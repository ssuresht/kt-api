<?php

use App\Http\Controllers\Api\v1\Student\FavoritesController;
use App\Http\Controllers\Api\v1\Student\InternshipPostsController;
use App\Http\Controllers\Api\v1\Student\PasswordResetController;
use App\Http\Controllers\Api\v1\Student\StudentController;
use App\Http\Controllers\Api\v1\MediaPostsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\Student\AuthController;


Route::post('/log-in', [StudentController::class, 'login']);
Route::post('/logout', [StudentController::class, 'logout']);
Route::post('/password-reset', [PasswordResetController::class, 'resetStudentPassword'])->name('studentPasswordReset');
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('resetPassword');
Route::post('/singup-request-email', [StudentController::class, 'store']);
Route::get('show-sign-up-student/{id}', [StudentController::class, 'show']);
 Route::post('update-sign-up-student/{id}', [StudentController::class, 'update']);
Route::apiResource('internship-post', InternshipPostsController::class);
Route::apiResource('media-posts', MediaPostsController::class);

Route::group(['middleware' => 'auth:students'], function() {
    Route::post('/logout', [StudentController::class, 'logout']);
    Route::apiResource('students', StudentController::class)->except('index','store','update');
    Route::post('internship-favourite/{id}', FavoritesController::class);    
    Route::post('student/change_password', [AuthController::class, 'changePassword'])->name('studentChangePassword');
    Route::post('basicInformation', [StudentController::class, 'update'])->name('updateInformation');
});

