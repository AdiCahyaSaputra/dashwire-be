<?php

// Controller
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TableInfoController;
use App\Http\Controllers\ValueInfoController;
// Lib
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
  return $request->user();
});

Route::group(['prefix' => 'auth'], function () {
  Route::post('/login', [AuthController::class, 'login']);
  Route::post('/register', [AuthController::class, 'register']);
  Route::post('/logout', [AuthController::class, 'logout']);

  Route::get('/refresh', [AuthController::class, 'refresh']);
});

Route::group(['middleware' => 'auth:api', 'prefix' => 'table'], function () {
  Route::controller(TableInfoController::class)->group(function () {
    Route::get('/', 'index');
    Route::get('/column/value', 'withColumnAndValue');

    Route::post('/', 'store');
  });
});

Route::group(['middleware' => 'auth:api', 'prefix' => 'value'], function() {
  Route::controller(ValueInfoController::class)->group(function () {
    Route::post('/', 'store');
  });
});
