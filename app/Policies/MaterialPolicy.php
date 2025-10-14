<?php

namespace App\Policies;

use App\Models\Material;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MaterialPolicy
{
    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user, string $ability): bool|null
    {
        if ($user->role === 'admin') {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Admin dan instructor bisa lihat semua materials
        // Student dan parent bisa lihat tapi akan difilter di view level
        return in_array($user->role, ['admin', 'instructor', 'student', 'parent']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Material $material): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'instructor') {
            // Instructor hanya bisa lihat material dari course yang dia ajar
            return $material->courseModule->course->instructor_id === $user->id;
        }

        if (in_array($user->role, ['student', 'parent'])) {
            // Student bisa lihat material dari course yang dia ikuti
            // Parent bisa lihat material dari course yang anaknya ikuti
            $enrolledCourses = $user->role === 'student' 
                ? $user->enrollments()->pluck('course_id')
                : $user->children()->with('enrollments')->get()->pluck('enrollments.*.course_id')->flatten();
            
            return $enrolledCourses->contains($material->courseModule->course_id);
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Hanya admin dan instructor yang bisa create materials
        return in_array($user->role, ['admin', 'instructor']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Material $material): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'instructor') {
            // Instructor hanya bisa update material dari course yang dia ajar
            return $material->courseModule->course->instructor_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Material $material): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'instructor') {
            // Instructor hanya bisa delete material dari course yang dia ajar
            return $material->courseModule->course->instructor_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Material $material): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Material $material): bool
    {
        return false;
    }
}
