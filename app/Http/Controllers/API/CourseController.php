<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $courses = Course::with('instructor')->get();
            return response()->json($courses);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving courses', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
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

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function show(Course $course)
    {
        $course->load('instructor', 'enrollments.student', 'courseModules.materials', 'assignments');
        return response()->json($course);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\Response
     */
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\Response
     */
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
