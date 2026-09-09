<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\Client;
use App\Models\Inquiry;
use App\Models\Lead;
use App\Models\Quote;
use App\Models\Solution;
use App\Models\Task;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json(['data' => [
            'leads' => Lead::count(),
            'new_inquiries' => Inquiry::where('status', 'new')->count(),
            'qualified_leads' => Lead::where('status', 'qualified')->count(),
            'clients' => Client::where('status', 'active')->count(),
            'quotes' => Quote::count(),
            'accepted_quotes_total' => Quote::where('status', 'accepted')->sum('total'),
            'open_tasks' => Task::whereNotIn('status', ['done', 'cancelled'])->count(),
            'published_posts' => BlogPost::where('status', 'published')->count(),
            'published_solutions' => Solution::where('status', 'published')->count(),
        ]]);
    }
}
