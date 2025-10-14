<?php

namespace App\Policies;

use App\Models\GradeComponent;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class GradeComponentPolicy
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
        // Semua role bisa lihat grade components (untuk keperluan melihat struktur penilaian)
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, GradeComponent $gradeComponent): bool
    {
        // Semua yang terkait course bisa lihat komponen nilai
        return match($user->role) {
            'admin' => true,
            'instructor' => $gradeComponent->course->instructor_id === $user->id,
            'student' => $gradeComponent->course->enrollments()->where('student_id', $user->id)->exists(),
            'parent' => $gradeComponent->course->enrollments()
                ->whereHas('student', fn($q) => $q->where('parent_id', $user->id))
                ->exists(),
            default => false,
        };
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Hanya admin dan instructor yang bisa buat grade components
        return in_array($user->role, ['admin', 'instructor']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, GradeComponent $gradeComponent): bool
    {
        // Admin bisa update semua, instructor hanya bisa update komponen course-nya
        return match($user->role) {
            'admin' => true,
            'instructor' => $gradeComponent->course->instructor_id === $user->id,
            default => false,
        };
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, GradeComponent $gradeComponent): bool
    {
        // Admin bisa delete semua, instructor hanya bisa delete komponen course-nya
        // Tapi hanya jika belum ada grades yang di-input
        $canManage = match($user->role) {
            'admin' => true,
            'instructor' => $gradeComponent->course->instructor_id === $user->id,
            default => false,
        };

        return $canManage && !$gradeComponent->grades()->exists();
    }

    /**
     * Custom policy for managing course grade components
     */
    public function manage(User $user, $courseId): bool
    {
        // Admin bisa manage semua, instructor hanya course-nya
        return match($user->role) {
            'admin' => true,
            'instructor' => \App\Models\Course::where('id', $courseId)
                ->where('instructor_id', $user->id)
                ->exists(),
            default => false,
        };
    }
}
