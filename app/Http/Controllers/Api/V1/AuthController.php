<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

        if (! $request->expectsJson()) {
            return redirect('/admin');
        }

        return response()->json(['user' => $user->fresh('role')]);
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
