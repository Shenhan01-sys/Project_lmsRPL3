<?php

use App\Http\Controllers\API\AssignmentController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CourseController;
use App\Http\Controllers\API\CourseModuleController;
use App\Http\Controllers\API\EnrollmentController;
use App\Http\Controllers\API\MaterialController;
use App\Http\Controllers\API\SubmissionController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\ParentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\PasswordController;
use App\Http\Controllers\API\FileUploadController;

Route::get('/test', function () {
    return response()->json(['message' => 'File api.php berhasil diakses!']);
});
/*
|--------------------------------------------------------------------------
| Rute Publik (Tidak Perlu Login)
|--------------------------------------------------------------------------
|
| Rute-rute ini dapat diakses oleh siapa saja.
|
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [PasswordController::class, 'forgotPassword']);
Route::post('/reset-password', [PasswordController::class, 'resetPassword']);

// Registration routes for calon siswa
Route::post('/register-calon-siswa', [App\Http\Controllers\API\RegistrationController::class, 'registerCalonSiswa']);

// Routes that require authentication but not full user verification
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/upload-documents', [App\Http\Controllers\API\RegistrationController::class, 'uploadDocuments']);
    Route::get('/registration-status', [App\Http\Controllers\API\RegistrationController::class, 'getRegistrationStatus']);
});


/*
|--------------------------------------------------------------------------
| Rute Terlindungi (Wajib Login / Punya Token)
|--------------------------------------------------------------------------
|
| Semua rute di dalam grup ini memerlukan token otentikasi yang valid.
|
*/
Route::middleware('auth:sanctum')->group(function () {
    // Rute untuk mendapatkan data user yang sedang login
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Rute untuk logout
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Rute untuk password management (user harus login)
    Route::post('/change-password', [PasswordController::class, 'changePassword']);
    
    // Rute untuk file upload
    Route::prefix('upload')->group(function () {
        Route::post('/profile-photo', [FileUploadController::class, 'uploadProfilePhoto']);
        Route::post('/material/{materialId}', [FileUploadController::class, 'uploadMaterialFile']);
        Route::post('/assignment/{assignmentId}', [FileUploadController::class, 'uploadAssignmentFile']);
        Route::post('/submission/{submissionId}', [FileUploadController::class, 'uploadSubmissionFile']);
        Route::delete('/file', [FileUploadController::class, 'deleteFile']);
        Route::get('/file-info', [FileUploadController::class, 'getFileInfo']);
    });

    // Grup untuk semua rute CRUD resource dengan prefix v1
    Route::prefix('v1')->group(function () {
        Route::get('instructors', [UserController::class, 'indexInstructor'])->name('users.instructors');
        Route::apiResource('users', UserController::class);
        Route::apiResource('courses', CourseController::class);
        Route::apiResource('enrollments', EnrollmentController::class);
        Route::apiResource('course-modules', CourseModuleController::class);
        Route::apiResource('materials', MaterialController::class);
        Route::apiResource('assignments', AssignmentController::class);
        Route::apiResource('submissions', SubmissionController::class);
        
        // Hybrid approach routes for discovery
        Route::get('course-modules/browse', [CourseModuleController::class, 'browse'])->name('course-modules.browse');
        Route::get('course-modules/my-modules', [CourseModuleController::class, 'myModules'])->name('course-modules.my');
        Route::get('materials/browse', [MaterialController::class, 'browse'])->name('materials.browse');
        Route::get('materials/my-materials', [MaterialController::class, 'myMaterials'])->name('materials.my');
        
        // Registration management routes (Admin only)
        Route::get('registrations/pending', [App\Http\Controllers\API\RegistrationController::class, 'getPendingRegistrations'])->name('registrations.pending');
        Route::get('registrations', [App\Http\Controllers\API\RegistrationController::class, 'getAllRegistrations'])->name('registrations.all');
        Route::post('registrations/{user}/approve', [App\Http\Controllers\API\RegistrationController::class, 'approveRegistration'])->name('registrations.approve');
        Route::post('registrations/{user}/reject', [App\Http\Controllers\API\RegistrationController::class, 'rejectRegistration'])->name('registrations.reject');
        
        // Routes untuk Grading System
        Route::apiResource('grade-components', App\Http\Controllers\API\GradeComponentController::class);
        Route::apiResource('grades', App\Http\Controllers\API\GradeController::class);
        
        // Routes khusus untuk grading
        Route::post('/grades/bulk', [App\Http\Controllers\API\GradeController::class, 'bulkStore'])->name('grades.bulk');
        Route::get('/grades/student', [App\Http\Controllers\API\GradeController::class, 'getStudentGrades'])->name('grades.student');
        Route::get('/grades/course', [App\Http\Controllers\API\GradeController::class, 'getCourseGrades'])->name('grades.course');
        Route::apiResource('parents', ParentController::class);
        Route::get('parents/{parent}/children', [ParentController::class, 'children'])->name('parents.children');
    });
});
