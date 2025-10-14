<?php

namespace App\Policies;

use App\Models\Grade;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class GradePolicy
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
        // Admin dan instructor bisa lihat semua grades
        return in_array($user->role, ['admin', 'instructor']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Grade $grade): bool
    {
        // Admin bisa lihat semua, instructor bisa lihat grades course-nya, 
        // student bisa lihat nilai sendiri, parent bisa lihat nilai anak
        return match($user->role) {
            'admin' => true,
            'instructor' => $grade->gradeComponent->course->instructor_id === $user->id,
            'student' => $grade->student_id === $user->id,
            'parent' => $grade->student->parent_id === $user->id,
            default => false,
        };
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Hanya admin dan instructor yang bisa input grades
        return in_array($user->role, ['admin', 'instructor']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Grade $grade): bool
    {
        // Admin bisa update semua, instructor hanya bisa update grades course-nya
        return match($user->role) {
            'admin' => true,
            'instructor' => $grade->gradeComponent->course->instructor_id === $user->id,
            default => false,
        };
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Grade $grade): bool
    {
        // Admin bisa delete semua, instructor hanya bisa delete grades course-nya
        return match($user->role) {
            'admin' => true,
            'instructor' => $grade->gradeComponent->course->instructor_id === $user->id,
            default => false,
        };
    }

    /**
     * Custom policy for bulk input grades
     */
    public function bulkInput(User $user): bool
    {
        // Hanya admin dan instructor yang bisa bulk input
        return in_array($user->role, ['admin', 'instructor']);
    }

    /**
     * Custom policy for course statistics
     */
    public function viewStatistics(User $user): bool
    {
        // Admin, instructor, dan parent bisa lihat statistik
        return in_array($user->role, ['admin', 'instructor', 'parent']);
    }
}
