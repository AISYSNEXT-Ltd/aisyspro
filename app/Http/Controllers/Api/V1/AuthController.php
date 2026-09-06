<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function login(LoginRequest $request): Response
    {
        $data = $request->validated();
        $user = User::query()
            ->with('role')
            ->where('email', $data['credential'])
            ->orWhere('login', $data['credential'])
            ->first();

        if (! $user || ! $user->is_active || ! Hash::check($data['password'], $user->password)) {
            if (! $request->expectsJson()) {
                return redirect('/connexion-admin?error=credentials');
            }

            throw ValidationException::withMessages([
                'credential' => ['Identifiants incorrects ou compte désactivé.'],
            ]);
        }

        if (! $request->expectsJson()) {
            $token = Str::random(64);
            Cache::put('admin-login-completion:'.hash('sha256', $token), [
                'user_id' => $user->getKey(),
                'session_hash' => hash('sha256', $request->session()->getId()),
                'remember' => (bool) ($data['remember'] ?? false),
            ], now()->addMinute());

            return redirect('/connexion-admin/complete?token='.$token)
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, private');
        }

        // Always use the session-backed web guard explicitly. This avoids the
        // active API guard influencing authentication when Sanctum handles the
        // request behind Cloudflare / Varnish.
        Auth::guard('web')->login($user, (bool) ($data['remember'] ?? false));
        $request->session()->regenerate();
        // Persist the regenerated session before returning the JSON response.
        // The StartSession middleware will still attach the new secure cookie,
        // while the database record is guaranteed to exist for the next page.
        $request->session()->save();
        $user->forceFill(['last_login_at' => now()])->save();

        return response()->json(['user' => $user->fresh('role')]);
    }

    public function complete(Request $request): Response
    {
        $token = (string) $request->query('token', '');
        $payload = null;
        if (preg_match('/^[a-zA-Z0-9]{64}$/D', $token)) {
            $key = 'admin-login-completion:'.hash('sha256', $token);
            $lock = Cache::lock($key.':lock', 10);
            if ($lock->get()) {
                try {
                    $candidate = Cache::get($key);
                    if (is_array($candidate) && hash_equals(
                        (string) ($candidate['session_hash'] ?? ''),
                        hash('sha256', $request->session()->getId()),
                    )) {
                        $payload = $candidate;
                        Cache::forget($key);
                    }
                } finally {
                    $lock->release();
                }
            }
        }

        if (! is_array($payload)) {
            return redirect('/connexion-admin?error=expired');
        }

        $user = User::query()->with('role')->find($payload['user_id'] ?? null);
        if (! $user || ! $user->is_active) {
            return redirect('/connexion-admin?error=credentials');
        }

        Auth::guard('web')->login($user, (bool) ($payload['remember'] ?? false));
        $request->session()->save();
        $user->forceFill(['last_login_at' => now()])->save();

        return redirect('/admin')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, private');
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => $request->user()->load('role')]);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Déconnexion effectuée.']);
    }
}
