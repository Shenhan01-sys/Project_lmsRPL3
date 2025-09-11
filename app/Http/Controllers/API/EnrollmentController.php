<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EnrollmentController extends Controller
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
            $enrollments = Enrollment::with(['student', 'course'])->get();
            return response()->json($enrollments);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving enrollments', 'error' => $e->getMessage()], 500);
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

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Enrollment  $enrollment
     * @return \Illuminate\Http\Response
     */
    public function show(Enrollment $enrollment)
    {
        $enrollment->load(['student', 'course']);
        return response()->json($enrollment);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Enrollment  $enrollment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Enrollment $enrollment)
    {
        // Generally, enrollments are not updated. They are created or deleted.
        // If an update is needed, validation rules would be similar to store.
        return response()->json(['message' => 'Enrollment updates are not typically supported.'], 405);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Enrollment  $enrollment
     * @return \Illuminate\Http\Response
     */
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
