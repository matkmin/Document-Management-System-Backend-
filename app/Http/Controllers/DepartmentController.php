<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isEmployee()) {
            return response()->json([$user->department]); // hanya department sendiri
        }

        return Department::all(); // Admin & Manager lihat semua
    }

    public function store(Request $request)
    {
        $this->authorizeRole($request->user(), ['admin', 'manager']);
        $validated = $request->validate(['name' => 'required|string', 'description' => 'nullable|string']);

        return Department::create($validated);
    }

    public function show($id)
    {
        return Department::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $this->authorizeRole($request->user(), ['admin', 'manager']);
        $department = Department::findOrFail($id);
        $department->update($request->only('name', 'description'));

        return $department;
    }

    public function destroy(Request $request, $id)
    {
        $this->authorizeRole($request->user(), ['admin']);

        $department = Department::findOrFail($id);

        $department->delete();

        return response()->json(['message' => 'Deleted']);
    }

    private function authorizeRole($user, $roles)
    {
        if (! in_array($user->role, $roles)) {
            abort(403, 'Unauthorized');
        }
    }
}
