<?php

namespace App\Policies;

use App\Models\CourseModule;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CourseModulePolicy
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
        // Admin dan instructor bisa lihat semua course modules
        // Student dan parent bisa lihat tapi akan difilter di view level
        return in_array($user->role, ['admin', 'instructor', 'student', 'parent']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CourseModule $courseModule): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'instructor') {
            // Instructor hanya bisa lihat module dari course yang dia ajar
            return $courseModule->course->instructor_id === $user->id;
        }

        if (in_array($user->role, ['student', 'parent'])) {
            // Student bisa lihat module dari course yang dia ikuti
            // Parent bisa lihat module dari course yang anaknya ikuti
            $enrolledCourses = $user->role === 'student' 
                ? $user->enrollments()->pluck('course_id')
                : $user->children()->with('enrollments')->get()->pluck('enrollments.*.course_id')->flatten();
            
            return $enrolledCourses->contains($courseModule->course_id);
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Hanya admin dan instructor yang bisa create course modules
        return in_array($user->role, ['admin', 'instructor']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CourseModule $courseModule): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'instructor') {
            // Instructor hanya bisa update module dari course yang dia ajar
            return $courseModule->course->instructor_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CourseModule $courseModule): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'instructor') {
            // Instructor hanya bisa delete module dari course yang dia ajar
            return $courseModule->course->instructor_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CourseModule $courseModule): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CourseModule $courseModule): bool
    {
        return false;
    }
}
