<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    public function index()
    {
        try {
            $courses = Course::with('instructor')->get();
            return response()->json($courses);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving courses', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_code' => 'required|string|unique:courses,course_code',
            'course_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructor_id' => 'required|exists:users,id',
        ]);

        try {
            $course = Course::create($validated);
            return response()->json($course, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating course', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(Course $course)
    {
        $course->load('instructor', 'enrollments.student', 'courseModules.materials', 'assignments');
        return response()->json($course);
    }

    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'course_code' => ['sometimes', 'required', 'string', Rule::unique('courses')->ignore($course->id)],
            'course_name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'instructor_id' => 'sometimes|required|exists:users,id',
        ]);

        try {
            $course->update($validated);
            return response()->json($course);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating course', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Course $course)
    {
        try {
            $course->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting course', 'error' => $e->getMessage()], 500);
        }
    }
}
