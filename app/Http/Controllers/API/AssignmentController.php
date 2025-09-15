<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use App\Models\Course;

class AssignmentController extends Controller
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
            $assignments = Assignment::with('course')->get();
            return response()->json($assignments);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving assignments', 'error' => $e->getMessage()], 500);
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
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'due_date' => 'nullable|date',
        ]);

        $course = Course::find($validated['course_id']);
        if ($request->user()->id !== $course->instructor_id) {
            return response()->json(['message' => 'You are not authorized to create assignments for this course.'], 403);
        }

        try {
            $assignment = Assignment::create($validated);
            return response()->json($assignment, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating assignment', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Assignment  $assignment
     * @return \Illuminate\Http\Response
     */
    public function show(Assignment $assignment)
    {
        $assignment->load('course', 'submissions.student');
        return response()->json($assignment);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Assignment  $assignment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Assignment $assignment)
    {
        $validated = $request->validate([
            'course_id' => 'sometimes|required|exists:courses,id',
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'due_date' => 'nullable|date',
        ]);

        try {
            $assignment->update($validated);
            return response()->json($assignment);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating assignment', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Assignment  $assignment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Assignment $assignment)
    {
        try {
            $assignment->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting assignment', 'error' => $e->getMessage()], 500);
        }
    }
}
