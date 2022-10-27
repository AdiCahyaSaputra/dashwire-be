<?php

namespace App\Helpers;

class SendResponseHelper
{
  public static function success($status = 200, $message, $data = [])
  {
    return response()->json([
      'message' => $message,
      'data' => $data
    ], $status);
  }

  public static function error($status = 400, $message, $errors = [])
  {
    return response()->json([
      'message' => $message,
      'errors' => $errors
    ], $status);
  }
}
