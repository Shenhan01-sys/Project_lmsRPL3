<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\GradeComponent;
use App\Services\GradingService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class GradeComponentController extends Controller
{
    use AuthorizesRequests;

    protected $gradingService;

    public function __construct(GradingService $gradingService)
    {
        $this->gradingService = $gradingService;
    }

    /**
     * Display a listing of grade components for a course
     */
    public function index(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
        ]);

        try {
            // Authorization: Check if user can view grade components
            $this->authorize('viewAny', GradeComponent::class);
            
            $components = GradeComponent::where('course_id', $request->course_id)
                ->with('course:id,course_name')
                ->orderBy('created_at')
                ->get();

            // Ambil validasi total bobot
            $weightValidation = $this->gradingService->validateTotalWeight($request->course_id);

            return response()->json([
                'message' => 'Komponen nilai berhasil diambil.',
                'data' => $components,
                'weight_validation' => $weightValidation,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error mengambil komponen nilai.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created grade component
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'weight' => 'required|numeric|min:0|max:100',
            'max_score' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        try {
            // Authorization: Check if user can create grade components
            $this->authorize('create', GradeComponent::class);

            $component = $this->gradingService->createGradeComponent(
                $validated['course_id'], 
                $validated
            );

            return response()->json([
                'message' => 'Komponen nilai berhasil dibuat.',
                'data' => $component
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal membuat komponen nilai.',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Display the specified grade component
     */
    public function show(GradeComponent $gradeComponent)
    {
        try {
            // Authorization: Check if user can view this grade component
            $this->authorize('view', $gradeComponent);
            
            $gradeComponent->load(['course:id,course_name', 'grades.student:id,name']);
            
            return response()->json([
                'message' => 'Detail komponen nilai berhasil diambil.',
                'data' => $gradeComponent
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error mengambil detail komponen nilai.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified grade component
     */
    public function update(Request $request, GradeComponent $gradeComponent)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'weight' => 'sometimes|required|numeric|min:0|max:100',
            'max_score' => 'sometimes|required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        try {
            // Authorization: Check if user can update this grade component
            $this->authorize('update', $gradeComponent);

            // Validasi bobot jika ada perubahan
            if (isset($validated['weight'])) {
                $existingWeight = GradeComponent::where('course_id', $gradeComponent->course_id)
                    ->where('is_active', true)
                    ->where('id', '!=', $gradeComponent->id)
                    ->sum('weight');

                if (($existingWeight + $validated['weight']) > 100) {
                    return response()->json([
                        'message' => 'Total bobot melebihi 100%.',
                        'error' => "Sisa bobot yang tersedia: " . (100 - $existingWeight) . "%"
                    ], 400);
                }
            }

            $gradeComponent->update($validated);

            return response()->json([
                'message' => 'Komponen nilai berhasil diupdate.',
                'data' => $gradeComponent
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error update komponen nilai.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified grade component
     */
    public function destroy(GradeComponent $gradeComponent)
    {
        try {
            // Authorization: Check if user can delete this grade component
            $this->authorize('delete', $gradeComponent);

            // Check apakah ada grades yang sudah di-input
            $hasGrades = $gradeComponent->grades()->exists();
            
            if ($hasGrades) {
                return response()->json([
                    'message' => 'Tidak dapat menghapus komponen nilai yang sudah memiliki data nilai.',
                ], 400);
            }

            $gradeComponent->delete();

            return response()->json([
                'message' => 'Komponen nilai berhasil dihapus.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error menghapus komponen nilai.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}