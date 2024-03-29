<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AutomaticController;
use App\Http\Controllers\Api\CategoryCourseController;
use App\Http\Controllers\Api\ClassRoomController;
use App\Http\Controllers\Api\ClassUserController;
use App\Http\Controllers\Api\CombineController;
use App\Http\Controllers\Api\ProgramController;
use App\Http\Controllers\Api\SubjectController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\TeacherController;
use App\Http\Controllers\Api\TimeFrameController;
use App\Http\Controllers\Api\WeekDayController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\ImageController;
use App\Http\Controllers\Api\PaypalPaymentController;
use App\Http\Controllers\Api\ResetPasswordController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\TopicCourseController;
use App\Http\Controllers\Api\UnitController;
use App\Http\Controllers\Api\VNPayController;
use App\Models\CategoryCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public router

// Auth
Route::group(['prefix' => '/auth'], function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/admin/login', [AuthController::class, 'adminLogin']);

    Route::post('reset-password', [ResetPasswordController::class, 'sendMail']);
    Route::put('reset-password/{token}', [ResetPasswordController::class, 'reset']);
});

// Search programs - courses - teachers
Route::get('/search', [SearchController::class, 'search']);
Route::get('/search-keyword', [SearchController::class, 'searchKeyword']);

// Programs
Route::group(['prefix' => '/programs'], function () {
    Route::get('/', [ProgramController::class, 'show']);
    Route::get('/{slug}', [ProgramController::class, 'showSlug']);
});

// Category Course
Route::group(['prefix' => '/category-course'], function () {
    Route::get('/{slug}', [CategoryCourseController::class, 'showSlug']);
});

// Topic Course
Route::group(['prefix' => '/topic-course'], function () {
    Route::get('/{slug}', [TopicCourseController::class, 'showSlug']);
});

// Subjects
Route::group(['prefix' => '/subjects'], function () {
    Route::get('/', [SubjectController::class, 'show']);
});

// Rooms
Route::group(['prefix' => '/rooms'], function () {
    Route::get('/', [RoomController::class, 'show']);
});

// Teachers
Route::group(['prefix' => '/teachers'], function () {
    Route::get('/', [TeacherController::class, 'show']);
});

// Time frames
Route::group(['prefix' => '/time-frames'], function () {
    Route::get('/', [TimeFrameController::class, 'show']);
});

// Week days
Route::group(['prefix' => '/week-days'], function () {
    Route::get('/', [WeekDayController::class, 'show']);
});

// Class
Route::group(['prefix' => '/classes'], function () {
    Route::get('/', [ClassRoomController::class, 'show']);
});

// Courses
Route::group(['prefix' => '/courses'], function () {
    Route::get('/', [CourseController::class, 'show']);
    Route::get('/{slug}', [CourseController::class, 'show_by_slug']);
});

// Courses
Route::group(['prefix' => '/course'], function () {
    Route::get('/{slug}', [CourseController::class, 'show_by_slug']);
    Route::get('/class/{slug}', [CourseController::class, 'show_class_by_course']);
});

// Checkout
Route::group(['prefix' => '/checkout'], function () {
    Route::get('/course/{slug}', [CourseController::class, 'show_by_slug_checkout']);
});

// Units
Route::group(['prefix' => '/units'], function () {
    Route::get('/units-by-course', [UnitController::class, 'units_by_course']);
});

// Combine
Route::group(['prefix' => '/combine'], function () {
    Route::get('/', [CombineController::class, 'show']);
});

// Route::get('images', [ImageController::class, 'index'])->name('images');
// Route::post('images', [ImageController::class, 'upload'])->name('images');


// ================================================================


Route::get('/vnpay/order/capture',[VNPayController::class,'capture']);

// Private router
Route::group(['middleware' => ['auth:sanctum']], function () {
    // Paypal
    Route::group(['prefix'=>'paypal'], function(){
        Route::post('/order/create',[PaypalPaymentController::class,'create']);
        Route::post('/order/capture',[PaypalPaymentController::class,'capture']);
    });

    Route::group(['prefix'=>'vnpay'], function(){
        Route::post('/order/create',[VNPayController::class,'createVN']);

    });

    // Auth
    Route::group(['prefix' => '/auth'], function () {
        Route::get('/', [UserController::class, 'getUser']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    // Programs
    Route::group(['prefix' => '/admin/programs'], function () {
        Route::post('/', [ProgramController::class, 'store']);
        Route::get('/', [ProgramController::class, 'adminShow']);
        Route::put('/{slug}', [ProgramController::class, 'update']);
        Route::delete('/{slug}', [ProgramController::class, 'destroy']);
    });

    // Categories Courses
    Route::group(['prefix' => '/category-course'], function () {
        Route::post('/', [CategoryCourseController::class, 'store']);
    });

    // Topics Courses
    Route::group(['prefix' => '/topic-course'], function () {
        Route::post('/', [TopicCourseController::class, 'store']);
    });

    // Subjects
    Route::group(['prefix' => '/subjects'], function () {
        Route::post('/', [SubjectController::class, 'store']);
        Route::put('/{slug}', [SubjectController::class, 'update']);
        Route::delete('/{slug}', [SubjectController::class, 'destroy']);
    });

    // Rooms
    Route::group(['prefix' => '/rooms'], function () {
        Route::post('/', [RoomController::class, 'store']);
        Route::put('/{idRoom}', [RoomController::class, 'update']);
        Route::delete('/{idRoom}', [RoomController::class, 'destroy']);
    });

    // Teachers
    Route::group(['prefix' => '/admin/teachers'], function () {
        Route::post('/', [TeacherController::class, 'store']);
        Route::get('/', [TeacherController::class, 'show']);
        Route::put('/{idTeacher}', [TeacherController::class, 'update']);
        Route::delete('/{idTeacher}', [TeacherController::class, 'destroy']);
    });

    // Time frames
    Route::group(['prefix' => '/time-frames'], function () {
        Route::post('/', [TimeFrameController::class, 'store']);
        Route::put('/{idTimeFrame}', [TimeFrameController::class, 'update']);
        Route::delete('/{idTimeFrame}', [TimeFrameController::class, 'destroy']);
    });

    // Week days
    Route::group(['prefix' => '/week-days'], function () {
        Route::post('/', [WeekDayController::class, 'store']);
        Route::put('/{idWeedDay}', [WeekDayController::class, 'update']);
        Route::delete('/{idWeedDay}', [WeekDayController::class, 'destroy']);
    });

    // Classes
    Route::group(['prefix' => '/classes'], function () {
        Route::post('/', [ClassRoomController::class, 'store']);
        Route::put('/{slug}', [ClassRoomController::class, 'update']);
        Route::delete('/{slug}', [ClassRoomController::class, 'destroy']);
    });

    // Courses
    Route::group(['prefix' => '/courses'], function () {
        Route::post('/', [CourseController::class, 'store']);
        Route::get('/', [CourseController::class, 'show']);
        Route::post('/store_auto', [CourseController::class, 'store_auto']);
        Route::put('/{slug}', [CourseController::class, 'update']);
        Route::delete('/{slug}', [CourseController::class, 'destroy']);
    });

    // order
    Route::group(['prefix' => '/order'], function () {
        Route::get('/', [ClassUserController::class, 'show']);
    });

    // ===============================================

    // Auto
    Route::group(['prefix' => '/auto'], function () {
        Route::post('/courses_store', [AutomaticController::class, 'courses_store']);
    });

    // ===============================================

    // Schedule
    Route::group(['prefix' => '/schedule'], function () {
        Route::post('/', [ScheduleController::class, 'schedule']);
    });

    // ===============================================
    Route::group(['prefix' => '/me/learning'], function () {
        Route::get('/', [UserController::class, 'get_learning_user']);
        Route::get('/class/{class}', [UserController::class, 'get_class_user']);
        Route::get('/schedule/{class}', [UserController::class, 'get_schedule_byclass_user']);
        Route::get('/schedule', [UserController::class, 'get_schedule_user']);
    });
});


Route::get('/send_email', [ResetPasswordController::class, 'sendEmailReminder']);
Route::post('/check', [TeacherController::class, 'check']);
