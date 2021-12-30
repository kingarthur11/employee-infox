<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\PerformanceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::group(['middleware' => 'cors'], function (){

    Route::prefix('user')->group(function() {

        Route::post('/login', [StaffController::class, 'login']);
        Route::post('/register', [StaffController::class, 'register']);

        Route::group( ['middleware' => 'auth:user-api' ],function(){

            Route::put('/update/{id}', [StaffController::class, 'update']);
            Route::get('/getall', [StaffController::class, 'index']);
         	Route::get('/getone/{id}', [StaffController::class, 'show']);
            Route::delete('/delete/{id}',[StaffController::class, 'destroy']);
        });
    });
    Route::prefix('department')->group(function() {

        Route::group( ['middleware' => 'auth:user-api' ],function(){

            Route::put('/update', [DepartmentController::class, 'update']);
            Route::get('/getall', [DepartmentController::class, 'index']);
            Route::get('/getone/{id}', [DepartmentController::class, 'show']);
         	Route::post('/create', [DepartmentController::class, 'store']);
            Route::delete('/destroy/{id}',[DepartmentController::class, 'destroy']);

            Route::post('/add-staff', [DepartmentController::class, 'addStaff']);
            Route::get('/getall-staffs', [DepartmentController::class, 'getStaffsInDepart']);
            Route::get('/getone-staff/{staffId}/{departId}', [DepartmentController::class, 'getOneStaffInDepart']);
            Route::delete('/remove-staff/{staffId}/{departId}', [DepartmentController::class, 'removeStaff']);
        });
    });

    Route::prefix('performance')->group(function() {

        Route::group( ['middleware' => 'auth:user-api' ],function(){

            Route::post('/', [PerformanceController::class, 'store']);
            Route::get('/getall-byYear', [PerformanceController::class, 'searchPerfomByYear']);
            Route::get('/getall-byMonth', [PerformanceController::class, 'searchPerfomByMonth']);
         	Route::get('/getStaff-byYear', [PerformanceController::class, 'searchStaffPerfomByYear']);
            Route::get('/getStaff-byMonth',[PerformanceController::class, 'searchStaffPerfomByMonth']);

        });
    });
});
