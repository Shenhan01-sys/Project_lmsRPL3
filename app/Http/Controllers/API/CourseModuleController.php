<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CourseModule;
use Illuminate\Http\Request;

class CourseModuleController extends Controller
{
    public function index()
    {
        try {
            $modules = CourseModule::with('course')->get();
            return response()->json($modules);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving course modules', 'error' => $e->getMessage()], 500);
        }
    }

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

    public function show(CourseModule $courseModule)
    {
        $courseModule->load('course', 'materials');
        return response()->json($courseModule);
    }

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
