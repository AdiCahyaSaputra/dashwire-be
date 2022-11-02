<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth:api', [
      'except' => ['login', 'register']
    ]);
  }

  public function responseWithToken($token)
  {
    return response()->json([
      'token' => [
        'access_token' => $token['access_token'],
        'refresh_token' => $token['refresh_token'],
      ],
      'data' => [
        'user' => auth()->user(),
        'expires_in' => auth()->factory()->getTTL() * 60
      ],
      'message' => "You're Signed"
    ]);
  }

  public function register(Request $request)
  {
    Validator::make($request->only(['name', 'email', 'password']), [
      'name' => 'required|min:8|max:20',
      'email' => 'required|email|unique:users',
      'password' => 'required|min:8'
    ])->validate();

    $user = User::create([
      'name' => $request->name,
      'email' => $request->email,
      'password' => bcrypt($request->password),
    ]);

    if (!$user) {
      return response()->json([
        'message' => 'Failed To Creating a User!'
      ], 422);
    }

    return response()->json([
      'message' => 'New User Has Been Created!'
    ], 201);
  }

  public function login(Request $request)
  {
    $credentials = Validator::make($request->only(['email', 'password']), [
      'email' => 'required|email',
      'password' => 'required|min:8'
    ])->validate();

    if (!$access_token = auth()->guard('api')->attempt($credentials)) {
      return response()->json([
        'message' => 'Credentials or Request Is Not Valid'
      ], 401);
    }

    $refresh = auth()->guard('api')->setTTL(60 * 24 * 30)->attempt($credentials);

    $token = [
      'access_token' => $access_token,
      'refresh_token' => $refresh
    ];

    return $this->responseWithToken($token);
  }

  public function logout()
  {
    auth()->logout(true);
    return response()->json([
      'message' => "You're Logged Out!"
    ]);
  }

  public function refresh()
  {
    return response()->json([
      'access_token' => auth()->refresh()
    ]);
  }
}
