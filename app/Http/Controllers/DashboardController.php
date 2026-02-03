<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Stats
        $totalAccessible = Document::query()->accessibleBy($user)->count();
        $myUploads = Document::where('uploaded_by', $user->id)->count();
        $deptDocs = 0;
        if ($user->department_id) {
            $deptDocs = Document::where('department_id', $user->department_id)->count();
        }

        // Recent Activity (Latest 5 documents accessible to user)
        $recentDocs = Document::query()
            ->accessibleBy($user)
            ->with(['uploader', 'category'])
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'stats' => [
                'total_accessible' => $totalAccessible,
                'my_uploads' => $myUploads,
                'department_docs' => $deptDocs
            ],
            'recent_activity' => $recentDocs
        ]);
    }
}
