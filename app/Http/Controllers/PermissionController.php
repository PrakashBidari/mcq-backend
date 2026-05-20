<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\TeacherPermission;
use App\Helpers\PermissionHelper;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index()
    {
        // Only admin can access
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('dashboard')->with('error', 'Access denied.');
        }

        $teachers = User::where('role', 'teacher')
            ->withCount('teacherPermissions')
            ->orderBy('name')
            ->get();

        return view('permissions.index', compact('teachers'));
    }

    public function edit($userId)
    {
        // Only admin can access
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('dashboard')->with('error', 'Access denied.');
        }

        $user = User::where('role', 'teacher')->findOrFail($userId);

        // Get available models
        $availableModels = PermissionHelper::getAvailableModels();

        // Get current permissions
        $currentPermissions = $user->teacherPermissions->keyBy('model');

        return view('permissions.edit', compact('user', 'availableModels', 'currentPermissions'));
    }

    public function update(Request $request, $userId)
    {
        // Only admin can access
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('dashboard')->with('error', 'Access denied.');
        }

        $user = User::where('role', 'teacher')->findOrFail($userId);

        // Delete all existing permissions for this user
        $user->teacherPermissions()->delete();

        // Get all models from request
        $models = $request->input('models', []);

        // Create new permissions
        foreach ($models as $model => $permissions) {
            if (isset($permissions['enabled'])) {
                TeacherPermission::create([
                    'user_id' => $user->id,
                    'model' => $model,
                    'can_create' => isset($permissions['create']),
                    'can_read' => isset($permissions['read']),
                    'can_update' => isset($permissions['update']),
                    'can_delete' => isset($permissions['delete']),
                ]);
            }
        }

        return redirect()->route('permissions.index')->with('success', 'Permissions updated successfully!');
    }
}
