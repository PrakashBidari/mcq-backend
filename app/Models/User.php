<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\TeacherPermission;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_image',
        'otp',
        'otp_expires_at',
        'is_verified',
        'reset_otp',
        'reset_otp_expires_at',
        'role', // ← ADD THIS
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'reset_otp_expires_at' => 'datetime',
            'password' => 'hashed',
            'is_verified' => 'boolean',
        ];
    }

    // Role check methods
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isTeacher()
    {
        return $this->role === 'teacher';
    }

    public function isUser()
    {
        return $this->role === 'user';
    }

    public function canAccessDashboard()
    {
        return in_array($this->role, ['admin', 'teacher']);
    }



    // ... existing code ...

    public function teacherPermissions()
    {
        return $this->hasMany(TeacherPermission::class);
    }

    // Check if user can perform action on model
    public function hasPermission($action, $model)
    {
        // Admin can do everything
        if ($this->isAdmin()) {
            return true;
        }

        // Regular users can't access dashboard
        if ($this->isUser()) {
            return false;
        }

        // Teachers check permissions
        if ($this->isTeacher()) {
            $permission = $this->teacherPermissions()
                ->where('model', $model)
                ->first();

            if (!$permission) {
                return false;
            }

            switch ($action) {
                case 'create':
                    return $permission->can_create;
                case 'read':
                case 'view':
                    return $permission->can_read;
                case 'update':
                case 'edit':
                    return $permission->can_update;
                case 'delete':
                    return $permission->can_delete;
                default:
                    return false;
            }
        }

        return false;
    }

    // Get all permissions for a teacher
    public function getPermissions()
    {
        if ($this->isAdmin()) {
            // Admin has all permissions
            return [
                'Category' => ['create' => true, 'read' => true, 'update' => true, 'delete' => true],
                'QuestionSet' => ['create' => true, 'read' => true, 'update' => true, 'delete' => true],
                'Question' => ['create' => true, 'read' => true, 'update' => true, 'delete' => true],
                'Book' => ['create' => true, 'read' => true, 'update' => true, 'delete' => true],
            ];
        }

        if ($this->isTeacher()) {
            $permissions = [];
            foreach ($this->teacherPermissions as $perm) {
                $permissions[$perm->model] = [
                    'create' => $perm->can_create,
                    'read' => $perm->can_read,
                    'update' => $perm->can_update,
                    'delete' => $perm->can_delete,
                ];
            }
            return $permissions;
        }

        return [];
    }
}
