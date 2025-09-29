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
        Route::apiResource('parents', ParentController::class);
        Route::get('parents/{parent}/children', [ParentController::class, 'children'])->name('parents.children');
    });
});
