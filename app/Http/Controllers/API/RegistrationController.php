<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\StudentRegistration;

class RegistrationController extends Controller
{
    use AuthorizesRequests;

    /**
     * Register calon siswa - Step 1: Basic Information
     */
    public function registerCalonSiswa(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string|max:20',
            'tanggal_lahir' => 'required|string|max:255',
            'tempat_lahir' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'nama_orang_tua' => 'required|string|max:255',
            'phone_orang_tua' => 'required|string|max:20',
            'alamat_orang_tua' => 'required|string|max:500',
        ]);

        try {
            // NEW: Create user (auth only) + separate registration record
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'phone' => $validated['phone'],
                'role' => 'calon_siswa',
            ]);

            // Create separate registration record
            $registration = StudentRegistration::create([
                'user_id' => $user->id,
                'tanggal_lahir' => $validated['tanggal_lahir'],
                'tempat_lahir' => $validated['tempat_lahir'],
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'nama_orang_tua' => $validated['nama_orang_tua'],
                'phone_orang_tua' => $validated['phone_orang_tua'],
                'alamat_orang_tua' => $validated['alamat_orang_tua'],
                'registration_status' => 'pending_documents',
            ]);

            // Create token for immediate access
            $token = $user->createToken('registration-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Registration successful',
                'user' => $user->load('studentRegistration'),
                'next_step' => 'upload_documents'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload registration documents - Step 2
     */
    public function uploadDocuments(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Only calon_siswa can upload documents
        if ($user->role !== 'calon_siswa') {
            return response()->json(['message' => 'Unauthorized. Only calon siswa can upload documents.'], 403);
        }

        $validated = $request->validate([
            'ktp_orang_tua' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'ijazah' => 'required|image|mimes:jpeg,png,jpg,pdf|max:2048',
            'foto_siswa' => 'required|image|mimes:jpeg,png,jpg|max:1024',
            'bukti_pembayaran' => 'required|image|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);

        try {
            $registration = $user->studentRegistration;
            if (!$registration) {
                return response()->json(['message' => 'Registration record not found. Please complete basic registration first.'], 404);
            }

            // Check if already submitted
            if ($registration->registration_status === 'pending_approval' || $registration->registration_status === 'approved') {
                return response()->json(['message' => 'Documents already submitted for this registration.'], 400);
            }

            $documentPaths = [];

            // Upload each document
            foreach ($validated as $key => $file) {
                $fileName = $user->id . '_' . $key . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('registration_documents', $fileName, 'public');
                $documentPaths[$key . '_path'] = $path;
            }

            // Update registration with document paths
            $registration->update([
                ...$documentPaths,
                'registration_status' => 'pending_approval',
                'submitted_at' => now(),
            ]);
            // Refresh registration model
            $registration->refresh();

            return response()->json([
                'message' => 'Documents uploaded successfully. Your registration is now pending admin approval.',
                'user' => $user->load('studentRegistration'),
                'registration' => $registration,
                'documents' => [
                    'ktp_orang_tua_url' => $registration->ktp_orang_tua_url,
                    'ijazah_url' => $registration->ijazah_url,
                    'foto_siswa_url' => $registration->foto_siswa_url,
                    'bukti_pembayaran_url' => $registration->bukti_pembayaran_url,
                ],
                'next_step' => 'wait_for_approval'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Document upload failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get registration status
     */
    public function getRegistrationStatus()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        if ($user->role !== 'calon_siswa') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Get registration record
        $registration = $user->studentRegistration;
        if (!$registration) {
            return response()->json(['message' => 'Registration record not found'], 404);
        }

        return response()->json([
            'user' => $user,
            'registration' => $registration,
            'registration_status' => $registration->registration_status,
            'submitted_at' => $registration->submitted_at,
            'approved_at' => $registration->approved_at,
            'approval_notes' => $registration->approval_notes,
            'documents' => [
                'ktp_orang_tua_url' => $registration->ktp_orang_tua_url,
                'ijazah_url' => $registration->ijazah_url,
                'foto_siswa_url' => $registration->foto_siswa_url,
                'bukti_pembayaran_url' => $registration->bukti_pembayaran_url,
            ],
            'is_complete' => $registration->is_complete,
            'next_step' => $this->getNextStep($registration->registration_status, $registration->is_complete),
        ]);
    }

    /**
     * Determine next step based on status and completeness
     */
    private function getNextStep($status, $isComplete)
    {
        switch ($status) {
            case 'pending_documents':
                return $isComplete ? 'submit_for_approval' : 'upload_documents';
            case 'pending_approval':
                return 'wait_for_approval';
            case 'approved':
                return 'registration_complete';
            case 'rejected':
                return 'fix_documents';
            default:
                return 'contact_support';
        }
    }

    /**
     * Admin: Get all pending registrations
     */
    public function getPendingRegistrations()
    {
        $this->authorize('viewAny', User::class);

        // Query through StudentRegistration model instead of User
        $pendingRegistrations = StudentRegistration::with('user:id,name,email,phone,role')
            ->where('registration_status', 'pending_approval')
            ->orderBy('submitted_at', 'asc')
            ->get()
            ->map(function ($registration) {
                return [
                    'id' => $registration->id,
                    'user' => $registration->user,
                    'registration_status' => $registration->registration_status,
                    'submitted_at' => $registration->submitted_at,
                    'tanggal_lahir' => $registration->tanggal_lahir,
                    'tempat_lahir' => $registration->tempat_lahir,
                    'nama_orang_tua' => $registration->nama_orang_tua,
                    'phone_orang_tua' => $registration->phone_orang_tua,
                    'documents' => [
                        'ktp_orang_tua_url' => $registration->ktp_orang_tua_url,
                        'ijazah_url' => $registration->ijazah_url,
                        'foto_siswa_url' => $registration->foto_siswa_url,
                        'bukti_pembayaran_url' => $registration->bukti_pembayaran_url,
                    ],
                    'is_complete' => $registration->is_complete,
                ];
            });

        return response()->json([
            'data' => $pendingRegistrations,
            'count' => $pendingRegistrations->count(),
        ]);
    }

    /**
     * Admin: Approve registration
     */
/**
 * Admin: Approve registration
 */
public function approveRegistration(Request $request, $userId)
{
    $this->authorize('update', User::class);

    // Find user and their registration
    $calonSiswa = User::findOrFail($userId);
    $registration = $calonSiswa->studentRegistration;
    
    if (!$registration) {
        return response()->json(['message' => 'Registration record not found'], 404);
    }
    
    if ($calonSiswa->role !== 'calon_siswa' || $registration->registration_status !== 'pending_approval') {
        return response()->json(['message' => 'Invalid registration status'], 400);
    }

    try {
        // Update user role (authentication level)
        $calonSiswa->update([
            'role' => 'student', // Change from calon_siswa to student
        ]);

        // Update registration status (registration level)
        $registration->update([
            'registration_status' => 'approved',
            'approved_at' => now(),
            'approved_by' => Auth::id(),
            'approval_notes' => $request->input('approval_notes', 'Registration approved by admin'),
        ]);

        return response()->json([
            'message' => 'Registration approved successfully',
            'data' => [
                'user' => $calonSiswa->fresh(),
                'registration' => $registration->fresh(),
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Approval failed',
            'error' => $e->getMessage()
        ], 500);
    }
}

    /**
     * Admin: Reject registration
     */
    public function rejectRegistration(Request $request, $userId)
    {
        $this->authorize('update', User::class);

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        // Find user and their registration
        $calonSiswa = User::findOrFail($userId);
        $registration = $calonSiswa->studentRegistration;

        if (!$registration) {
            return response()->json(['message' => 'Registration record not found'], 404);
        }

        // Ensure current status allows rejection
        if ($calonSiswa->role !== 'calon_siswa' || $registration->registration_status !== 'pending_approval') {
            return response()->json(['message' => 'Invalid registration status'], 400);
        }

        try {
            // Update registration (not the user role)
            $registration->update([
                'registration_status' => 'rejected',
                'approval_notes' => $validated['rejection_reason'],
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            return response()->json([
                'message' => 'Registration rejected successfully',
                'data' => [
                    'user' => $calonSiswa->fresh(),
                    'registration' => $registration->fresh(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Rejection failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: Get all registrations with filters
     */
    public function getAllRegistrations(Request $request)
    {
        $this->authorize('viewAny', User::class);

        // Query StudentRegistration with User relationship
        $query = StudentRegistration::with(['user:id,name,email,phone,role', 'approver:id,name']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('registration_status', $request->status);
        }

        // Filter by level (via user relationship)
        if ($request->has('level')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('level', $request->level);
            });
        }

        $registrations = $query->orderBy('submitted_at', 'desc')
            ->get()
            ->map(function ($registration) {
                return [
                    'id' => $registration->id,
                    'user' => $registration->user,
                    'registration_status' => $registration->registration_status,
                    'submitted_at' => $registration->submitted_at,
                    'approved_at' => $registration->approved_at,
                    'approval_notes' => $registration->approval_notes,
                    'approver' => $registration->approver,
                    'tanggal_lahir' => $registration->tanggal_lahir,
                    'tempat_lahir' => $registration->tempat_lahir,
                    'nama_orang_tua' => $registration->nama_orang_tua,
                    'phone_orang_tua' => $registration->phone_orang_tua,
                    'documents' => [
                        'ktp_orang_tua_url' => $registration->ktp_orang_tua_url,
                        'ijazah_url' => $registration->ijazah_url,
                        'foto_siswa_url' => $registration->foto_siswa_url,
                        'bukti_pembayaran_url' => $registration->bukti_pembayaran_url,
                    ],
                    'is_complete' => $registration->is_complete,
                ];
            });

        return response()->json([
            'data' => $registrations,
            'count' => $registrations->count(),
        ]);
    }
}
