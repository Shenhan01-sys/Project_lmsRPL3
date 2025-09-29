<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ParentModel;
use App\Models\Parents;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class ParentController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        // $this->authorize('viewAny', Parents::class); // bisa ditambahkan Policy jika diperlukan
        return response()->json(Parents::query()->latest()->get());
    }

    public function store(Request $request)
    {
        // $this->authorize('create', ParentModel::class);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:parents,email',
            'phone' => 'nullable|string|max:50',
        ]);

        $parent = Parents::create($validated);
        return response()->json($parent, 201);
    }

    public function show(Parents $parent)
    {
        // $this->authorize('view', $parent);
        return response()->json($parent->load('children'));
    }

    public function update(Request $request, Parents $parent)
    {
        // $this->authorize('update', $parent);
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|nullable|email|unique:parents,email,' . $parent->id,
            'phone' => 'sometimes|nullable|string|max:50',
        ]);

        $parent->update($validated);
        return response()->json($parent);
    }

    public function destroy(Parents $parent)
    {
        // $this->authorize('delete', $parent);
        $parent->delete();
        return response()->json(null, 204);
    }

    public function children(Parents $parent)
    {
        // Dapatkan semua anak (users) milik parent
        $children = $parent->children()->where('role', 'student')->get();
        return response()->json($children);
    }
}
