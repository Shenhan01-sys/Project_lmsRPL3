<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CoursePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Semua role bisa lihat course list (untuk enrollment)
        return in_array($user->role, ['admin', 'instructor', 'student', 'parent']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Course $course): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'instructor') {
            // Instructor bisa lihat course yang dia ajar
            return $course->instructor_id === $user->id;
        }

        if ($user->role === 'student') {
            // Student bisa lihat course yang dia ikuti atau course yang available untuk enrollment
            return $user->enrollments()->where('course_id', $course->id)->exists() || true;
        }

        if ($user->role === 'parent') {
            // Parent bisa lihat course yang anaknya ikuti
            return $user->children()->whereHas('enrollments', function($query) use ($course) {
                $query->where('course_id', $course->id);
            })->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === 'admin' || $user->role === 'instructor';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Course $course): bool
    {
        return $user->role === 'admin' || $user->id === $course->instructor_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Course $course): bool
    {
        return $user->role === 'admin' || $user->id === $course->instructor_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Course $course): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Course $course): bool
    {
        return $user->role === 'admin';
    }
}
