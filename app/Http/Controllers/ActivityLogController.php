<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        if (! $request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $logs = ActivityLog::with(['user', 'document'])
            ->latest()
            ->paginate(50);

        return response()->json($logs);
    }
}
