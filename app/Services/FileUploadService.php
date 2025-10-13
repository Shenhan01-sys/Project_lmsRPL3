<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    /**
     * Upload file umum dengan validasi
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param array $allowedExtensions
     * @param int $maxSizeKB
     * @return array
     * @throws \Exception
     */
    public function uploadFile(UploadedFile $file, string $directory, array $allowedExtensions = [], int $maxSizeKB = 10240)
    {
        // Validasi file
        $this->validateFile($file, $allowedExtensions, $maxSizeKB);

        // Generate nama file unik
        $filename = $this->generateUniqueFilename($file);
        
        // Upload file
        $path = $file->storeAs($directory, $filename, 'public');

        return [
            'success' => true,
            'filename' => $filename,
            'path' => $path,
            'url' => Storage::url($path),
            'size' => $file->getSize(),
            'extension' => $file->getClientOriginalExtension(),
            'original_name' => $file->getClientOriginalName(),
        ];
    }

    /**
     * Upload foto profil user
     *
     * @param UploadedFile $file
     * @param int $userId
     * @return array
     */
    public function uploadProfilePhoto(UploadedFile $file, int $userId)
    {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $maxSizeKB = 2048; // 2MB

        $directory = "profile-photos/{$userId}";
        
        return $this->uploadFile($file, $directory, $allowedExtensions, $maxSizeKB);
    }

    /**
     * Upload file materi pembelajaran
     *
     * @param UploadedFile $file
     * @param int $materialId
     * @return array
     */
    public function uploadMaterialFile(UploadedFile $file, int $materialId)
    {
        $allowedExtensions = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'mp4', 'mp3'];
        $maxSizeKB = 51200; // 50MB

        $directory = "materials/{$materialId}";
        
        return $this->uploadFile($file, $directory, $allowedExtensions, $maxSizeKB);
    }

    /**
     * Upload file tugas (soal dari guru)
     *
     * @param UploadedFile $file
     * @param int $assignmentId
     * @return array
     */
    public function uploadAssignmentFile(UploadedFile $file, int $assignmentId)
    {
        $allowedExtensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
        $maxSizeKB = 10240; // 10MB

        $directory = "assignments/{$assignmentId}";
        
        return $this->uploadFile($file, $directory, $allowedExtensions, $maxSizeKB);
    }

    /**
     * Upload file jawaban tugas (dari siswa)
     *
     * @param UploadedFile $file
     * @param int $submissionId
     * @return array
     */
    public function uploadSubmissionFile(UploadedFile $file, int $submissionId)
    {
        $allowedExtensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'zip', 'rar'];
        $maxSizeKB = 20480; // 20MB

        $directory = "submissions/{$submissionId}";
        
        return $this->uploadFile($file, $directory, $allowedExtensions, $maxSizeKB);
    }

    /**
     * Upload dokumen pendaftaran
     *
     * @param UploadedFile $file
     * @param int $registrationId
     * @param string $documentType
     * @return array
     */
    public function uploadRegistrationDocument(UploadedFile $file, int $registrationId, string $documentType)
    {
        $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
        $maxSizeKB = 5120; // 5MB

        $directory = "registrations/{$registrationId}/{$documentType}";
        
        return $this->uploadFile($file, $directory, $allowedExtensions, $maxSizeKB);
    }

    /**
     * Hapus file
     *
     * @param string $filePath
     * @return bool
     */
    public function deleteFile(string $filePath)
    {
        try {
            if (Storage::disk('public')->exists($filePath)) {
                return Storage::disk('public')->delete($filePath);
            }
            return true; // File sudah tidak ada
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Validasi file
     *
     * @param UploadedFile $file
     * @param array $allowedExtensions
     * @param int $maxSizeKB
     * @throws \Exception
     */
    private function validateFile(UploadedFile $file, array $allowedExtensions, int $maxSizeKB)
    {
        // Cek apakah file valid
        if (!$file->isValid()) {
            throw new \Exception('File tidak valid atau corrupted.');
        }

        // Cek ekstensi file
        if (!empty($allowedExtensions)) {
            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, array_map('strtolower', $allowedExtensions))) {
                throw new \Exception('Format file tidak diizinkan. Format yang diizinkan: ' . implode(', ', $allowedExtensions));
            }
        }

        // Cek ukuran file
        $fileSizeKB = $file->getSize() / 1024;
        if ($fileSizeKB > $maxSizeKB) {
            $maxSizeMB = round($maxSizeKB / 1024, 1);
            $fileSizeMB = round($fileSizeKB / 1024, 1);
            throw new \Exception("Ukuran file terlalu besar ({$fileSizeMB}MB). Maksimal {$maxSizeMB}MB.");
        }
    }

    /**
     * Generate nama file unik
     *
     * @param UploadedFile $file
     * @return string
     */
    private function generateUniqueFilename(UploadedFile $file)
    {
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid() . '_' . time() . '.' . $extension;
        
        return $filename;
    }

    /**
     * Get URL file
     *
     * @param string $filePath
     * @return string|null
     */
    public function getFileUrl(string $filePath)
    {
        if (Storage::disk('public')->exists($filePath)) {
            return Storage::url($filePath);
        }
        
        return null;
    }

    /**
     * Get info file
     *
     * @param string $filePath
     * @return array|null
     */
    public function getFileInfo(string $filePath)
    {
        if (!Storage::disk('public')->exists($filePath)) {
            return null;
        }

        return [
            'path' => $filePath,
            'url' => Storage::url($filePath),
            'size' => Storage::disk('public')->size($filePath),
            'last_modified' => Storage::disk('public')->lastModified($filePath),
        ];
    }
}