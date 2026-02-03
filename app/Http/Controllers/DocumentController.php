<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Document;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $query = Document::query();

        // Access Control Scope
        $query->accessibleBy($request->user());

        // Search
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($request->has('category_id')) {
            $query->where('document_category_id', $request->input('category_id'));
        }

        if ($request->has('department_id')) {
            $query->where('department_id', $request->input('department_id'));
        }

        // Date Range Filter
        if ($request->has('start_date') && $request->input('start_date')) {
            $query->whereDate('created_at', '>=', $request->input('start_date'));
        }
        if ($request->has('end_date') && $request->input('end_date')) {
            $query->whereDate('created_at', '<=', $request->input('end_date'));
        }

        // Sorting
        $sortField = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');

        $allowedSorts = ['title', 'created_at', 'download_count', 'file_size'];
        if (!in_array($sortField, $allowedSorts)) {
            $sortField = 'created_at';
        }
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }

        $documents = $query->with(['uploader', 'department', 'category'])
            ->orderBy($sortField, $sortDirection)
            ->paginate(20);

        return response()->json($documents);
    }

    public function show(Document $document)
    {
        $this->authorize('view', $document);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'document_id' => $document->id,
            'action' => 'view',
            'details' => 'Viewed document details',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return response()->json($document->load(['uploader', 'department', 'category']));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Document::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'required|file|max:10240|mimes:pdf,docx,xlsx,jpg,png',
            'document_category_id' => 'required|exists:document_categories,id',
            'department_id' => 'required|exists:departments,id',
            'access_level' => 'required|in:public,department,private',
        ]);

        if ($request->user()->isManager() && $validated['department_id'] != $request->user()->department_id) {
            return response()->json(['message' => 'Managers can only upload to their own department.'], 403);
        }

        $file = $request->file('file');
        $path = $file->store('documents');

        $document = Document::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'document_category_id' => $validated['document_category_id'],
            'department_id' => $validated['department_id'],
            'uploaded_by' => $request->user()->id,
            'access_level' => $validated['access_level'],
        ]);

        return response()->json($document, 201);
    }

    public function update(Request $request, Document $document)
    {
        $this->authorize('update', $document);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'document_category_id' => 'sometimes|exists:document_categories,id',
            'access_level' => 'sometimes|in:public,department,private',
        ]);

        $document->update($validated);

        return response()->json($document);
    }

    public function destroy(Document $document)
    {
        $this->authorize('delete', $document);

        Storage::delete($document->file_path);
        $document->delete();

        return response()->json(['message' => 'Document deleted successfully']);
    }

    public function download(Document $document)
    {
        $this->authorize('view', $document);

        $document->increment('download_count');

        ActivityLog::create([
            'user_id' => auth()->id(),
            'document_id' => $document->id,
            'action' => 'download',
            'details' => 'Downloaded file: ' . $document->file_name,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return Storage::download($document->file_path, $document->file_name);
    }
}
