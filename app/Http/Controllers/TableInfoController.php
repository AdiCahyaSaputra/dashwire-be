<?php

namespace App\Http\Controllers;

// Models
use App\Models\TableInfo;

// Lib
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

// Helper
use App\Helpers\SendResponseHelper;

class TableInfoController extends Controller
{
  public function index()
  {
    $tables = DB::table('table_infos')
      ->join('admins', 'admins.table_id', 'table_infos.id')
      ->select(
        'table_infos.name as table_name',
        'table_infos.id',
      )->where('admins.user_id', Auth::id())->get();

    if (!count($tables)) return SendResponseHelper::error(status: 404, message: 'Data is Empty');

    $data = [];

    foreach ($tables as $table) {
      $data[] = [
        'id' => $table->id,
        'table' => $table->table_name
      ];
    }

    return SendResponseHelper::success(
      status: 200,
      message: 'All Tables Based On Current User',
      data: [
        'tables' => $data
      ]
    );
  }

  public function withValues(Request $request)
  {
    Validator::make(
      $request->only('table_id'),
      [
        'table_id' => 'required|uuid',
      ]
    )->validate();

    $values = DB::table('table_infos')
      ->join('column_infos', 'column_infos.table_id', 'table_infos.id')
      ->join('value_infos', 'value_infos.column_id', 'column_infos.id')
      ->select(
        'column_infos.column_name',
        'value_infos.column_id',
        'value_infos.values',
        'table_infos.name',
        'table_infos.id'
      )->get();

    if (!count($values)) return SendResponseHelper::error(404, 'There\'s No data in this table!');

    $column_values = [];

    foreach ($values as $value) {
      $column_values[$value->column_name][] = $value->values;
    }

    $data = [
      'table' => [
        'id' => $values[0]->id,
        'name' => $values[0]->name
      ],
      'column_values' => $column_values
    ];

    return SendResponseHelper::success(200, 'Values Data from given table_id', $data);
  }

  public function withAuthors(Request $request)
  {
    Validator::make(
      $request->only('table_id'),
      [
        'table_id' => 'required|uuid',
      ]
    )->validate();

    $authors = DB::table('table_infos')
      ->join('admins', 'admins.table_id', 'table_infos.id')
      ->join('users', 'users.id', 'admins.user_id')
      ->select('users.id', 'users.name', 'admins.is_author')
      ->get();

    if (!count($authors)) return SendResponseHelper::error(404, "There's No Author In That Table!");

    return SendResponseHelper::success(200, 'Author Data from given table_id', [
      'authors' => $authors
    ]);
  }

  public function store(Request $request)
  {
    Validator::make(
      $request->only('table_name', 'columns_info', 'users'),
      [
        'table_name' => 'required|min:8',
        'columns_info.*.name' => 'required|string',
        'columns_info.*.validation_rules' => 'required|string',
        'users.*.id' => 'required|numeric'
      ]
    )->validate();

    DB::beginTransaction();

    $insertedTable = TableInfo::create([
      'name' => $request->table_name,
    ]);


    if (!$insertedTable) {
      DB::rollBack();
      return SendResponseHelper::error(message: 'Table created Errors!');
    }

    $admins = [
      [
        'table_id' => $insertedTable->id,
        'user_id' => Auth::id(),
        'is_author' => 1
      ]
    ];

    foreach ($request->users as $user) {
      $admins[] = [
        'table_id' => $insertedTable->id,
        'user_id' => $user['id'],
        'is_author' => 0
      ];
    }

    $insertedAdmins = DB::table('admins')->insert($admins);

    if (!$insertedAdmins) {
      DB::rollBack();
      return SendResponseHelper::error(message: 'Admin created Errors!');
    }

    $column_infos = [];

    foreach ($request->columns_info as $column_info) {

      $column_infos[] = [
        'table_id' => $insertedTable->id,
        'column_name' => $column_info['name'],
        'validation_rules' => $column_info['validation_rules']
      ];
    }

    $insertedColumns = DB::table('column_infos')->insert($column_infos);

    if (!$insertedColumns) {
      DB::rollBack();
      return SendResponseHelper::error(message: 'Column created Errors!');
    }


    DB::commit();

    return SendResponseHelper::success(
      status: 201,
      message: 'Table has been created!',
      data: [
        'table' => $insertedTable,
        'column' => $insertedColumns
      ]
    );
  }
}
