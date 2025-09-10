<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EnrollmentController extends Controller
{
    public function index()
    {
        try {
            $enrollments = Enrollment::with(['student', 'course'])->get();
            return response()->json($enrollments);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving enrollments', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'course_id' => [
                'required',
                'exists:courses,id',
                Rule::unique('enrollments')->where(function ($query) use ($request) {
                    return $query->where('student_id', $request->student_id);
                }),
            ],
        ]);

        try {
            $enrollment = Enrollment::create($validated);
            return response()->json($enrollment, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating enrollment', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(Enrollment $enrollment)
    {
        $enrollment->load(['student', 'course']);
        return response()->json($enrollment);
    }

    public function update(Request $request, Enrollment $enrollment)
    {
        // Generally, enrollments are not updated. They are created or deleted.
        // If an update is needed, validation rules would be similar to store.
        return response()->json(['message' => 'Enrollment updates are not typically supported.'], 405);
    }

    public function destroy(Enrollment $enrollment)
    {
        try {
            $enrollment->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting enrollment', 'error' => $e->getMessage()], 500);
        }
    }
}
