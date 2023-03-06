<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\MouController;
use App\Http\Controllers\FixController;
use App\Http\Controllers\FixStatusController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\ProjectTypeController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ScoreController;
use App\Http\Controllers\UniversityController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SettingController;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);

    Route::group(['middleware' => 'auth:api'], function() {
      Route::get('logout', [AuthController::class, 'logout']);
      Route::get('user', [AuthController::class, 'user']);
    });
});


Route::group(['prefix' => 'user'], function () {
    Route::get('/{id}', [UserController::class, 'get']);
    Route::get('/', [UserController::class, 'getAll']);
    Route::post('/', [UserController::class, 'add']);
    Route::put('/{id}', [UserController::class, 'edit']);
    Route::delete('/{id}', [UserController::class, 'delete']);
});

Route::group(['prefix' => 'fix'], function () {
    Route::get('/{id}', [FixController::class, 'get']);
    Route::get('/', [FixController::class, 'getAll']);
    Route::post('/', [FixController::class, 'add']);
    Route::put('/{id}', [FixController::class, 'edit']);
    Route::delete('/{id}', [FixController::class, 'delete']);
});


Route::group(['prefix' => 'activity'], function () {
    Route::get('/{id}', [ActivityController::class, 'get']);
    Route::get('/', [ActivityController::class, 'getAll']);
    Route::post('/', [ActivityController::class, 'add']);
    Route::put('/{id}', [ActivityController::class, 'edit']);
    Route::delete('/{id}', [ActivityController::class, 'delete']);
});

Route::group(['prefix' => 'mou'], function () {
    Route::get('/{id}', [MouController::class, 'get']);
    Route::get('/', [MouController::class, 'getAll']);
    Route::post('/', [MouController::class, 'add']);
    Route::put('/{id}', [MouController::class, 'edit']);
    Route::delete('/{id}', [MouController::class, 'delete']);
});

Route::group(['prefix' => 'country'], function () {
    Route::get('/{id}', [CountryController::class, 'get']);
    Route::get('/', [CountryController::class, 'getAll']);
    Route::post('/', [CountryController::class, 'add']);
    Route::put('/{id}', [CountryController::class, 'edit']);
    Route::delete('/{id}', [CountryController::class, 'delete']);
});

Route::group(['prefix' => 'building'], function () {
    Route::get('/{id}', [BuildingController::class, 'get']);
    Route::get('/', [BuildingController::class, 'getAll']);
    Route::post('/', [BuildingController::class, 'add']);
    Route::put('/{id}', [BuildingController::class, 'edit']);
    Route::delete('/{id}', [BuildingController::class, 'delete']);
});

Route::group(['prefix' => 'fix-status'], function () {
    Route::get('/{id}', [FixStatusController::class, 'get']);
    Route::get('/', [FixStatusController::class, 'getAll']);
    Route::post('/', [FixStatusController::class, 'add']);
    Route::put('/', [FixStatusController::class, 'edit']);
    Route::delete('/{id}', [FixStatusController::class, 'delete']);
});

Route::group(['prefix' => 'project-type'], function () {
    Route::get('/{id}', [ProjectTypeController::class, 'get']);
    Route::get('/', [ProjectTypeController::class, 'getAll']);
    Route::post('/', [ProjectTypeController::class, 'add']);
    Route::put('/{id}', [ProjectTypeController::class, 'edit']);
    Route::delete('/{id}', [ProjectTypeController::class, 'delete']);
});

Route::group(['prefix' => 'question'], function () {
    Route::get('/{id}', [QuestionController::class, 'get']);
    Route::get('/', [QuestionController::class, 'getAll']);
    Route::post('/', [QuestionController::class, 'add']);
    Route::put('/{id}', [QuestionController::class, 'edit']);
    Route::delete('/{id}', [QuestionController::class, 'delete']);
});

Route::group(['prefix' => 'score'], function () {
    Route::get('/{id}', [ScoreController::class, 'get']);
    Route::get('/', [ScoreController::class, 'getAll']);
    Route::post('/', [ScoreController::class, 'add']);
    Route::put('/{id}', [ScoreController::class, 'edit']);
    Route::delete('/{id}', [ScoreController::class, 'delete']);
});

Route::group(['prefix' => 'university'], function () {
    Route::get('/{id}', [UniversityController::class, 'get']);
    Route::get('/', [UniversityController::class, 'getAll']);
    Route::post('/', [UniversityController::class, 'add']);
    Route::put('/{id}', [UniversityController::class, 'edit']);
    Route::delete('/{id}', [UniversityController::class, 'delete']);
});

Route::group(['prefix' => 'project'], function () {
    Route::get('/{id}', [ProjectController::class, 'get']);
    Route::get('/', [ProjectController::class, 'getAll']);
    Route::post('/', [ProjectController::class, 'add']);
    Route::put('/{id}', [ProjectController::class, 'edit']);
    Route::delete('/{id}', [ProjectController::class, 'delete']);
});

Route::group(['prefix' => 'setting'], function () {
    Route::get('/{id}', [SettingController::class, 'get']);
});





// use App\Http\Controllers\ProjectTypeController;
// use App\Http\Controllers\QuestionController;
// use App\Http\Controllers\ScoreController;
// use App\Http\Controllers\UniversityController;


// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });