<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\GradeComponent;
use App\Services\GradingService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GradeController extends Controller
{
    use AuthorizesRequests;

    protected $gradingService;

    public function __construct(GradingService $gradingService)
    {
        $this->gradingService = $gradingService;
    }

    /**
     * Input nilai siswa
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'grade_component_id' => 'required|exists:grade_components,id',
            'score' => 'required|numeric|min:0',
            'max_score' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        try {
            // TODO: Add authorization check
            // $this->authorize('input-grades', GradeComponent::find($validated['grade_component_id']));

            $options = [
                'max_score' => $validated['max_score'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'graded_by' => Auth::id(),
            ];

            $grade = $this->gradingService->inputGrade(
                $validated['student_id'],
                $validated['grade_component_id'],
                $validated['score'],
                $options
            );

            $grade->load(['student:id,name', 'gradeComponent:id,name,weight', 'grader:id,name']);

            return response()->json([
                'message' => 'Nilai berhasil di-input.',
                'data' => $grade
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal input nilai.',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Input nilai massal (bulk)
     */
    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'grades' => 'required|array',
            'grades.*.student_id' => 'required|exists:users,id',
            'grades.*.grade_component_id' => 'required|exists:grade_components,id',
            'grades.*.score' => 'required|numeric|min:0',
            'grades.*.max_score' => 'nullable|numeric|min:0',
            'grades.*.notes' => 'nullable|string',
        ]);

        try {
            // TODO: Add authorization check untuk setiap grade component

            // Prepare data dengan options
            $gradesData = collect($validated['grades'])->map(function($grade) {
                return [
                    'student_id' => $grade['student_id'],
                    'grade_component_id' => $grade['grade_component_id'],
                    'score' => $grade['score'],
                    'options' => [
                        'max_score' => $grade['max_score'] ?? null,
                        'notes' => $grade['notes'] ?? null,
                        'graded_by' => Auth::id(),
                    ]
                ];
            })->toArray();

            $grades = $this->gradingService->bulkInputGrades($gradesData);

            return response()->json([
                'message' => 'Nilai massal berhasil di-input.',
                'data' => $grades,
                'count' => $grades->count()
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal input nilai massal.',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get nilai siswa untuk course
     */
    public function getStudentGrades(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
        ]);

        try {
            // TODO: Add authorization check
            // $this->authorize('view-grades', ...);

            $grades = $this->gradingService->getStudentGrades(
                $validated['student_id'], 
                $validated['course_id']
            );

            $finalGrade = $this->gradingService->calculateFinalGrade(
                $validated['student_id'], 
                $validated['course_id']
            );

            return response()->json([
                'message' => 'Nilai siswa berhasil diambil.',
                'data' => [
                    'grades' => $grades,
                    'final_grade' => $finalGrade
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error mengambil nilai siswa.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get rekap nilai untuk course
     */
    public function getCourseGrades(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
        ]);

        try {
            // TODO: Add authorization check
            // $this->authorize('view-course-grades', Course::find($validated['course_id']));

            $summary = $this->gradingService->getCourseGradesSummary($validated['course_id']);
            $statistics = $this->gradingService->getCourseStatistics($validated['course_id']);

            return response()->json([
                'message' => 'Rekap nilai course berhasil diambil.',
                'data' => [
                    'summary' => $summary,
                    'statistics' => $statistics
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error mengambil rekap nilai.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update nilai siswa
     */
    public function update(Request $request, Grade $grade)
    {
        $validated = $request->validate([
            'score' => 'sometimes|required|numeric|min:0',
            'max_score' => 'sometimes|required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        try {
            // TODO: Add authorization check
            // $this->authorize('update', $grade);

            // Validasi score tidak melebihi max_score
            if (isset($validated['score']) && isset($validated['max_score'])) {
                if ($validated['score'] > $validated['max_score']) {
                    return response()->json([
                        'message' => 'Nilai tidak boleh melebihi nilai maksimal.'
                    ], 400);
                }
            } elseif (isset($validated['score'])) {
                if ($validated['score'] > $grade->max_score) {
                    return response()->json([
                        'message' => 'Nilai tidak boleh melebihi nilai maksimal.'
                    ], 400);
                }
            }

            $grade->update($validated);
            $grade->load(['student:id,name', 'gradeComponent:id,name,weight', 'grader:id,name']);

            return response()->json([
                'message' => 'Nilai berhasil diupdate.',
                'data' => $grade
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error update nilai.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete nilai siswa
     */
    public function destroy(Grade $grade)
    {
        try {
            // TODO: Add authorization check
            // $this->authorize('delete', $grade);

            $grade->delete();

            return response()->json([
                'message' => 'Nilai berhasil dihapus.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error menghapus nilai.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}