<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubmissionController extends Controller
{
    public function index()
    {
        try {
            $submissions = Submission::with(['assignment', 'student'])->get();
            return response()->json($submissions);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving submissions', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
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

        try {
            $submission = Submission::create($validated);
            return response()->json($submission, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating submission', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(Submission $submission)
    {
        $submission->load(['assignment', 'student']);
        return response()->json($submission);
    }

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
