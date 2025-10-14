<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SubmissionController extends Controller
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
            $submissions = Submission::with(['assignment', 'student'])->get();
            return response()->json($submissions);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving submissions', 'error' => $e->getMessage()], 500);
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
        $StudentId = $request->user()->id;
        $validated = $request->validate([
            'assignment_id' => [
                'required',
                'exists:assignments,id',
                Rule::unique('submissions')->where(function ($query) use ($request) {
                    return $query->where('student_id', $request->student_id);
                }),
            ],
            'student_id' => 'required|exists:users,id',
            'file_path' => 'nullable|string',
            'grade' => 'nullable|numeric',
            'feedback' => 'nullable|string',
        ]);

        if($StudentId !== $validated['student_id']){
            return response()->json(['message' => 'You are not authorized or a student to submit for this assignment.'], 403);
        }
        try {
            $submission = Submission::create($validated);
            return response()->json($submission, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating submission', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Submission  $submission
     * @return \Illuminate\Http\Response
     */
    public function show(Submission $submission)
    {
        $submission->load(['assignment', 'student']);
        return response()->json($submission);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Submission  $submission
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Submission $submission)
    {
        $validated = $request->validate([
            'assignment_id' => [
                'sometimes',
                'required',
                'exists:assignments,id',
                Rule::unique('submissions')->ignore($submission->id)->where(function ($query) use ($request, $submission) {
                    return $query->where('student_id', $request->student_id ?? $submission->student_id);
                }),
            ],
            'student_id' => 'sometimes|required|exists:users,id',
            'file_path' => 'nullable|string',
            'grade' => 'nullable|numeric',
            'feedback' => 'nullable|string',
        ]);

        try {
            $submission->update($validated);
            return response()->json($submission);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating submission', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Submission  $submission
     * @return \Illuminate\Http\Response
     */
    public function destroy(Submission $submission)
    {
        try {
            $submission->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting submission', 'error' => $e->getMessage()], 500);
        }
    }
}
