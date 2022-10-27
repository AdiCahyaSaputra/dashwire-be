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
      ->join('users', 'admins.user_id', 'users.id')
      ->select(
        'table_infos.name as table_name',
        'table_infos.id',
        'users.name',
        'admins.is_author'
      )->get();

    if (!count($tables)) return SendResponseHelper::error(status: 404, message: 'Data is Empty');

    $data = [
      [
        'id' => $tables[0]->id,
        'table' => $tables[0]->table_name,
        'users' => [
          [
            'name' => $tables[0]->name,
            'is_author' => $tables[0]->is_author
          ]
        ]
      ]
    ];

    $pointerTables = 1;
    $pointerData = 0;

    while ($pointerTables < count($tables)) {

      if ($data[$pointerData]['id'] === $tables[$pointerTables]->id) {

        $data[$pointerData]['users'][] = [
          'name' => $tables[$pointerTables]->name,
          'is_author' => $tables[$pointerTables]->is_author
        ];

        $pointerTables++;
      } else {
        $data[] = [
          'id' => $tables[$pointerTables]->id,
          'table' => $tables[$pointerTables]->table_name,
          'users' => [
            [
              'name' => $tables[$pointerTables]->name,
              'is_author' => $tables[$pointerTables]->is_author
            ]
          ]
        ];

        $pointerData++;
        $pointerTables++;
      }
    }

    return SendResponseHelper::success(status: 200, message: 'All Tables data with users info', data: [
      'tables' => $data
    ]);
  }

  public function withColumnAndValue(Request $request)
  {
    Validator::make(
      $request->only('table_id'),
      [
        'table_id' => 'required|uuid',
      ]
    )->validate();

    $tables = DB::table('table_infos')
      ->join('column_infos', 'column_infos.table_id', 'table_infos.id')
      ->join('value_infos', 'value_infos.column_id', 'column_infos.id')
      ->select(
        'column_infos.table_id',
        'table_infos.name',
        'column_infos.id',
        'column_infos.column_name',
        'column_infos.validation_rules',
        'value_infos.values'
      )
      ->where('table_infos.id', $request->table_id)
      ->get();

    if (!count($tables)) return SendResponseHelper::error(status: 404, message: 'Table not found or maybe there is no data in that table!');

    $data = [
      [
        'id' => $tables[0]->table_id,
        'table' => $tables[0]->name,
        'columns_info' => [
          [
            'id' => $tables[0]->id,
            'name' => $tables[0]->column_name,
            'validation_rules' => $tables[0]->validation_rules,
            'values' => $tables[0]->values
          ]
        ]
      ]
    ];

    $pointerTables = 1;
    $pointerData = 0;

    while ($pointerTables < count($tables)) {

      if ($data[$pointerData]['id'] === $tables[$pointerTables]->table_id) {

        $data[$pointerData]['columns_info'][] = [
          'id' => $tables[$pointerTables]->id,
          'name' => $tables[$pointerTables]->column_name,
          'validation_rules' => $tables[$pointerTables]->validation_rules,
          'values' => $tables[$pointerTables]->values
        ];

        $pointerTables++;
      } else {
        $data[] = [
          'id' => $tables[$pointerTables]->table_id,
          'table' => $tables[$pointerTables]->name,
          'columns_info' => [
            [
              'id' => $tables[$pointerTables]->id,
              'name' => $tables[$pointerTables]->column_name,
              'validation_rules' => $tables[$pointerTables]->validation_rules,
              'values' => $tables[$pointerTables]->values
            ]
          ]
        ];

        $pointerData++;
        $pointerTables++;
      }
    }

    return SendResponseHelper::success(200, 'Table with columns information', [
      'table' => $data
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
