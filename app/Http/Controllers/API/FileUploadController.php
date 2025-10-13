<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\FileUploadService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class FileUploadController extends Controller
{
    use AuthorizesRequests;

    protected $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Upload foto profil user
     */
    public function uploadProfilePhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|file|image|max:2048', // max 2MB
        ]);

        try {
            $user = $request->user();
            
            // Hapus foto profil lama jika ada
            if ($user->profile_photo_path) {
                $this->fileUploadService->deleteFile($user->profile_photo_path);
            }

            $result = $this->fileUploadService->uploadProfilePhoto(
                $request->file('photo'),
                $user->id
            );

            // Update user dengan path foto baru
            $user->update([
                'profile_photo_path' => $result['path']
            ]);

            return response()->json([
                'message' => 'Foto profil berhasil diupload.',
                'data' => [
                    'photo_url' => $result['url'],
                    'photo_path' => $result['path'],
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal upload foto profil.',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Upload file materi
     */
    public function uploadMaterialFile(Request $request, $materialId)
    {
        $request->validate([
            'file' => 'required|file|max:51200', // max 50MB
        ]);

        try {
            // TODO: Add authorization check untuk material
            // $this->authorize('update', Material::find($materialId));

            $result = $this->fileUploadService->uploadMaterialFile(
                $request->file('file'),
                $materialId
            );

            return response()->json([
                'message' => 'File materi berhasil diupload.',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal upload file materi.',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Upload file tugas (soal)
     */
    public function uploadAssignmentFile(Request $request, $assignmentId)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // max 10MB
        ]);

        try {
            // TODO: Add authorization check untuk assignment
            // $this->authorize('update', Assignment::find($assignmentId));

            $result = $this->fileUploadService->uploadAssignmentFile(
                $request->file('file'),
                $assignmentId
            );

            return response()->json([
                'message' => 'File tugas berhasil diupload.',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal upload file tugas.',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Upload file jawaban tugas (submission)
     */
    public function uploadSubmissionFile(Request $request, $submissionId)
    {
        $request->validate([
            'file' => 'required|file|max:20480', // max 20MB
        ]);

        try {
            // TODO: Add authorization check untuk submission
            // $this->authorize('update', Submission::find($submissionId));

            $result = $this->fileUploadService->uploadSubmissionFile(
                $request->file('file'),
                $submissionId
            );

            return response()->json([
                'message' => 'File jawaban berhasil diupload.',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal upload file jawaban.',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Hapus file
     */
    public function deleteFile(Request $request)
    {
        $request->validate([
            'file_path' => 'required|string',
        ]);

        try {
            $result = $this->fileUploadService->deleteFile($request->file_path);

            if ($result) {
                return response()->json([
                    'message' => 'File berhasil dihapus.'
                ]);
            } else {
                return response()->json([
                    'message' => 'Gagal menghapus file.'
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error saat menghapus file.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get info file
     */
    public function getFileInfo(Request $request)
    {
        $request->validate([
            'file_path' => 'required|string',
        ]);

        try {
            $fileInfo = $this->fileUploadService->getFileInfo($request->file_path);

            if ($fileInfo) {
                return response()->json([
                    'message' => 'Info file berhasil diambil.',
                    'data' => $fileInfo
                ]);
            } else {
                return response()->json([
                    'message' => 'File tidak ditemukan.'
                ], 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error saat mengambil info file.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}