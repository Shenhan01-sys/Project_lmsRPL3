<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CourseModule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class CourseModuleController extends Controller
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
            $modules = CourseModule::with('course')->get();
            return response()->json($modules);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving course modules', 'error' => $e->getMessage()], 500);
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
            'module_order' => 'nullable|integer',
        ]);

        try {
            $module = CourseModule::create($validated);
            return response()->json($module, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating course module', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CourseModule  $courseModule
     * @return \Illuminate\Http\Response
     */
    public function show(CourseModule $courseModule)
    {
        $courseModule->load('course', 'materials');
        return response()->json($courseModule);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CourseModule  $courseModule
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CourseModule $courseModule)
    {
        $validated = $request->validate([
            'course_id' => 'sometimes|required|exists:courses,id',
            'title' => 'sometimes|required|string|max:255',
            'module_order' => 'nullable|integer',
        ]);

        try {
            $courseModule->update($validated);
            return response()->json($courseModule);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating course module', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CourseModule  $courseModule
     * @return \Illuminate\Http\Response
     */
    public function destroy(CourseModule $courseModule)
    {
        try {
            $courseModule->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting course module', 'error' => $e->getMessage()], 500);
        }
    }
}
