<?php

// Controller
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TableInfoController;
use App\Http\Controllers\ValueInfoController;
// Lib
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

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

Route::prefix('v1')->group(function () {
  Route::group(['prefix' => 'auth'], function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');

    Route::get('/refresh', [AuthController::class, 'refresh'])->middleware();
  });

  Route::group(['middleware' => 'auth:api', 'prefix' => 'table'], function () {
    Route::controller(TableInfoController::class)->group(function () {
      Route::get('/', 'index');
      Route::post('/values', 'withValues');
      Route::post('/authors', 'withAuthors');

      Route::post('/', 'store');
    });
  });

  Route::group(['middleware' => 'auth:api', 'prefix' => 'value'], function () {
    Route::controller(ValueInfoController::class)->group(function () {
      Route::post('/', 'store');
    });
  });
});

Route::post('/create/value', function () {
  $data = [
    [
      'column_id' => 1,
      'values' => 'Adi Cahya Saputra',
      'type' => 'string',
    ],
    [
      'column_id' => 2,
      'values' => '00598291',
      'type' => 'string',
    ],
    [
      'column_id' => 3,
      'values' => 'RPL',
      'type' => 'string',
    ],
    [
      'column_id' => 1,
      'values' => 'Cahya Saputro',
      'type' => 'string',
    ],
    [
      'column_id' => 2,
      'values' => '00598296',
      'type' => 'string',
    ],
    [
      'column_id' => 3,
      'values' => 'IT',
      'type' => 'string',
    ],
  ];

  $inserted = DB::table('value_infos')->insert($data);
  return response()->json($inserted);
});
