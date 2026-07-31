<?php

use App\Http\Controllers\Api\{AssignmentController,AuthController,ConversationController,DeviceController,EnrollmentController,LibraryController,MaterialController,MediaController,NewsController,ScheduleController,StatsController,StudentController,SubjectController,SupportController,UserController};
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->middleware('throttle:10,1')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('password/forgot', [AuthController::class, 'forgot']);
        Route::post('password/reset', [AuthController::class, 'reset']);
    });

    // Public catalog reads (courses / library / news pages work without login)
    Route::get('subjects', [SubjectController::class, 'index']);
    Route::get('subjects/{subject}', [SubjectController::class, 'show']);
    Route::get('subjects/{subject}/materials', [MaterialController::class, 'index']);
    Route::get('library', [LibraryController::class, 'index']);
    Route::get('news', [NewsController::class, 'index']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']); Route::get('auth/me', [AuthController::class, 'me']);
        Route::delete('users/{user}', [UserController::class, 'destroy']);
        Route::apiResource('subjects', SubjectController::class)->only(['store', 'update']);
        Route::post('subjects/{subject}/enrollments', [EnrollmentController::class, 'store']);
        Route::post('subjects/{subject}/materials', [MaterialController::class, 'store']);
        Route::patch('materials/{material}', [MaterialController::class, 'update']); Route::delete('materials/{material}', [MaterialController::class, 'destroy']);
        Route::post('students/bulk', [StudentController::class, 'bulk']); Route::post('students/export', [StudentController::class, 'export']);
        Route::get('students', [StudentController::class, 'index']); Route::post('students', [StudentController::class, 'store']); Route::patch('students/{student}', [StudentController::class, 'update']);
        Route::apiResource('schedules', ScheduleController::class)->only(['index','store','update','destroy']);
        Route::get('assignments', [AssignmentController::class, 'index']); Route::post('assignments', [AssignmentController::class, 'store']);
        Route::get('assignments/{assignment}/submissions', [AssignmentController::class, 'submissions']); Route::post('assignments/{assignment}/submissions', [AssignmentController::class, 'submit']);
        Route::patch('submissions/{submission}', [AssignmentController::class, 'updateSubmission']);
        Route::apiResource('library', LibraryController::class)->parameters(['library'=>'library'])->only(['store','update','destroy']);
        Route::apiResource('news', NewsController::class)->only(['store','update','destroy']);
        Route::get('conversations', [ConversationController::class, 'index']); Route::post('conversations', [ConversationController::class, 'store']);
        Route::get('conversations/{conversation}/messages', [ConversationController::class, 'messages']); Route::post('conversations/{conversation}/messages', [ConversationController::class, 'send']);
        Route::post('media/sign-upload', [MediaController::class, 'sign']); Route::post('devices', [DeviceController::class, 'store']);
        Route::get('support/users', [SupportController::class, 'users']); Route::patch('support/users/{user}/theme', [SupportController::class, 'theme']);
        Route::get('stats/summary', [StatsController::class, 'summary']); Route::post('stats/export', [StatsController::class, 'export']);
    });
});
