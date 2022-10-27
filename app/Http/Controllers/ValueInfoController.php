<?php

namespace App\Http\Controllers;

// Lib
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

// Helper
use App\Helpers\SendResponseHelper;

class ValueInfoController extends Controller
{
  public function store(Request $request)
  {
    Validator::make($request->only('table_id', 'data'), [
      'table_id' => 'required|uuid',
      'data.*.column_id' => 'required|numeric',
      'data.*.values' => 'required',
      'data.*.type' => 'required'
    ])->validate();

    $columns = DB::table('table_infos')
      ->join('column_infos', 'column_infos.table_id', 'table_infos.id')
      ->select('column_infos.column_name', 'column_infos.validation_rules', 'column_infos.id')
      ->where('table_infos.id', $request->table_id)
      ->get();

    if (!count($columns)) return SendResponseHelper::error(status: 404, message: 'Data Not Found');

    $rules = [];
    $values = [];

    foreach ($columns as $column) {
      $rules[$column->column_name] = $column->validation_rules;

      foreach ($request->data as $data) {

        if ($column->id === $data['column_id']) {
          $values[$column->column_name] = $data['values'];
          break;
        }
      }
    }

    Validator::make($values, $rules)->validate();

    $insertedValues = DB::table('value_infos')->insert($request->data);

    if(!$insertedValues) return SendResponseHelper::error(400, 'Cannot insert Value!');

    return SendResponseHelper::success(200, 'Inserted values Successfully!', [
      'data' => $insertedValues
    ]);
  }
}
