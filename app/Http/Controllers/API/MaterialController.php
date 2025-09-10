<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Material;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    public function index()
    {
        try {
            $materials = Material::with('courseModule')->get();
            return response()->json($materials);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving materials', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
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

    public function show(Material $material)
    {
        $material->load('courseModule');
        return response()->json($material);
    }

    public function update(Request $request, Material $material)
    {
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

    public function destroy(Material $material)
    {
        try {
            $material->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting material', 'error' => $e->getMessage()], 500);
        }
    }
}
