<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Enrollment;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaterialController extends Controller
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
            // Authorization: Check if user can view any materials
            $this->authorize('viewAny', Material::class);

            $user = Auth::user();
            
            if ($user->role === 'admin') {
                // Admin bisa lihat semua materials
                $materials = Material::with('courseModule.course')->get();
            } elseif ($user->role === 'instructor') {
                // Instructor hanya bisa lihat materials dari course yang dia ajar
                $materials = Material::with('courseModule.course')
                    ->whereHas('courseModule.course', function($query) use ($user) {
                        $query->where('instructor_id', $user->id);
                    })->get();
            } elseif ($user->role === 'student') {
                // Student hanya bisa lihat materials dari course yang dia ikuti
                $materials = Material::with('courseModule.course')
                    ->whereHas('courseModule.course.enrollments', function($query) use ($user) {
                        $query->where('student_id', $user->id);
                    })->get();
            } elseif ($user->role === 'parent') {
                // Parent bisa lihat materials dari course yang anaknya ikuti
                $childrenIds = \App\Models\User::where('parent_id', $user->id)->pluck('id');
                $materials = Material::with('courseModule.course')
                    ->whereHas('courseModule.course.enrollments', function($query) use ($childrenIds) {
                        $query->whereIn('student_id', $childrenIds);
                    })->get();
            } else {
                $materials = collect();
            }

            return response()->json($materials);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving materials', 'error' => $e->getMessage()], 500);
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
        // Authorization: Check if user can create materials
        $this->authorize('create', Material::class);

        $validated = $request->validate([
            'module_id' => 'required|exists:course_modules,id',
            'title' => 'required|string|max:255',
            'material_type' => 'required|in:file,link,video',
            'content_path' => 'required|string',
        ]);

        try {
            $material = Material::create($validated);
            return response()->json($material, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating material', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Material  $material
     * @return \Illuminate\Http\Response
     */
    public function show(Material $material)
    {
        // Authorization: Check if user can view this material
        $this->authorize('view', $material);

        $material->load('courseModule');
        return response()->json($material);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Material  $material
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Material $material)
    {
        // Authorization: Check if user can update this material
        $this->authorize('update', $material);

        $validated = $request->validate([
            'module_id' => 'sometimes|required|exists:course_modules,id',
            'title' => 'sometimes|required|string|max:255',
            'material_type' => 'sometimes|required|in:file,link,video',
            'content_path' => 'sometimes|required|string',
        ]);

        try {
            $material->update($validated);
            return response()->json($material);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating material', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Material  $material
     * @return \Illuminate\Http\Response
     */
    public function destroy(Material $material)
    {
        // Authorization: Check if user can delete this material
        $this->authorize('delete', $material);

        try {
            $material->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting material', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Browse all materials with preview mode for discovery
     */
    public function browse()
    {
        try {
            $this->authorize('viewAny', Material::class);
            
            $user = Auth::user();
            
            if ($user->role === 'admin') {
                // Admin full access to all materials
                $materials = Material::with('courseModule.course')->get();
                return $materials->map(function($material) {
                    $material->can_access = true;
                    $material->access_level = 'full';
                    return $material;
                });
            } elseif ($user->role === 'instructor') {
                // Instructor can see all, but full access only to own courses
                $materials = Material::with('courseModule.course')->get();
                return $materials->map(function($material) use ($user) {
                    $canAccess = $material->courseModule->course->instructor_id === $user->id;
                    $material->can_access = $canAccess;
                    $material->access_level = $canAccess ? 'full' : 'preview';
                    
                    if (!$canAccess) {
                        // Hide actual content path in preview mode
                        $material->content_path = 'Preview available after course enrollment';
                    }
                    
                    return $material;
                });
            } elseif (in_array($user->role, ['student', 'parent'])) {
                // Student/Parent can browse all materials
                $materials = Material::with('courseModule.course')->get();
                
                // Get enrolled course IDs
                $enrolledCourseIds = collect();
                if ($user->role === 'student') {
                    $enrolledCourseIds = Enrollment::where('student_id', $user->id)->pluck('course_id');
                } else { // parent
                    $childrenIds = \App\Models\User::where('parent_id', $user->id)->pluck('id');
                    $enrolledCourseIds = Enrollment::whereIn('student_id', $childrenIds)
                        ->pluck('course_id')->unique();
                }
                
                return $materials->map(function($material) use ($enrolledCourseIds) {
                    $canAccess = $enrolledCourseIds->contains($material->courseModule->course_id);
                    $material->can_access = $canAccess;
                    $material->access_level = $canAccess ? 'full' : 'preview';
                    
                    if (!$canAccess) {
                        // Preview mode: hide actual content
                        $material->content_path = 'Enroll in course to access this material';
                        $material->description = 'Preview: ' . $material->title . ' - Full content available after enrollment';
                    }
                    
                    return $material;
                });
            }
            
            return response()->json([]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error browsing materials', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get only materials from enrolled courses (full access)
     */
    public function myMaterials()
    {
        try {
            $this->authorize('viewAny', Material::class);
            
            $user = Auth::user();
            
            if ($user->role === 'admin') {
                // Admin can access all materials
                $materials = Material::with('courseModule.course')->get();
            } elseif ($user->role === 'instructor') {
                // Instructor materials from own courses
                $materials = Material::with('courseModule.course')
                    ->whereHas('courseModule.course', function($query) use ($user) {
                        $query->where('instructor_id', $user->id);
                    })->get();
            } elseif ($user->role === 'student') {
                // Student materials from enrolled courses
                $materials = Material::with('courseModule.course')
                    ->whereHas('courseModule.course.enrollments', function($query) use ($user) {
                        $query->where('student_id', $user->id);
                    })->get();
            } elseif ($user->role === 'parent') {
                // Parent materials from children's enrolled courses
                $childrenIds = \App\Models\User::where('parent_id', $user->id)->pluck('id');
                $materials = Material::with('courseModule.course')
                    ->whereHas('courseModule.course.enrollments', function($query) use ($childrenIds) {
                        $query->whereIn('student_id', $childrenIds);
                    })->get();
            } else {
                $materials = collect();
            }
            
            // Add full access indicator
            $materials = $materials->map(function($material) {
                $material->can_access = true;
                $material->access_level = 'full';
                return $material;
            });
            
            return response()->json($materials);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving my materials', 'error' => $e->getMessage()], 500);
        }
    }
}
