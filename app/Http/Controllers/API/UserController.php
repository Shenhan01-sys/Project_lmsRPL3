<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);
        try {
            // Validasi filter opsional
            $validated = $request->validate([
                'role' => 'sometimes|in:student,instructor,admin',
                'parent_id' => 'sometimes|nullable|integer|exists:parents,id',
                'level' => 'sometimes|in:SMP,SMA',
            ]);

            $query = User::query();

            // Tentukan role efektif (default student)
            $effectiveRole = $validated['role'] ?? 'student';
            $query->where('role', $effectiveRole);

            // Filter anak berdasarkan parent jika diberikan
            if (array_key_exists('parent_id', $validated) && !is_null($validated['parent_id'])) {
                $query->where('parent_id', $validated['parent_id']);
            }

            // Jika role student dan filter level diberikan, terapkan
            if ($effectiveRole === 'student' && array_key_exists('level', $validated)) {
                $query->where('level', $validated['level']);
            }

            $users = $query->get();
            return response()->json($users);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving users', 'error' => $e->getMessage()], 500);
        }
    }

    public function indexInstructor()
    {
        $this->authorize('viewAny', User::class);
        try {
            $users = User::where('role', 'instructor')->get();
            return response()->json($users);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving users', 'error' => $e->getMessage()], 500);
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
        $this->authorize('create', User::class);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:student,instructor,admin',
            'level' => 'required_if:role,student|in:SMP,SMA',
            'parent_id' => 'nullable|exists:parents,id',
        ]);

        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
                'level' => $validated['role'] === 'student' ? $validated['level'] : null,
                'parent_id' => $validated['parent_id'] ?? null,
            ]);
            return response()->json($user, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating user', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);
        return response()->json($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'sometimes|required|string|min:8',
            'role' => 'sometimes|required|in:student,instructor,admin',
            'level' => 'sometimes|in:SMP,SMA',
            'parent_id' => 'sometimes|nullable|exists:parents,id',
        ]);

        try {
            if (isset($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            }
            $effectiveRole = $validated['role'] ?? $user->role;
            if ($effectiveRole !== 'student') {
                $validated['level'] = null;
            }
            $user->update($validated);
            return response()->json($user);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating user', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);
        try {
            $user->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting user', 'error' => $e->getMessage()], 500);
        }
    }
}
