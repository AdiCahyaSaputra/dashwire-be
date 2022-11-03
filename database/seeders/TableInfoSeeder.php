<?php

namespace Database\Seeders;

use App\Models\TableInfo;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class TableInfoSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    $insertedTable = TableInfo::create([
      'name' => 'Siswa SMK'
    ]);

    $admins = [
      [
        'table_id' => $insertedTable->id,
        'user_id' => 1,
        'is_author' => 1
      ],
      [
        'table_id' => $insertedTable->id,
        'user_id' => 2,
        'is_author' => 0
      ],
      [
        'table_id' => $insertedTable->id,
        'user_id' => 3,
        'is_author' => 0
      ],
    ];

    DB::table('admins')->insert($admins);

    $column_infos = [
      [
        'table_id' => $insertedTable->id,
        'column_name' => 'nama',
        'validation_rules' => 'required|string'
      ],
      [
        'table_id' => $insertedTable->id,
        'column_name' => 'nis',
        'validation_rules' => 'required|string|max:8'
      ],
      [
        'table_id' => $insertedTable->id,
        'column_name' => 'jurusan',
        'validation_rules' => 'required|string'
      ],
    ];

    DB::table('column_infos')->insert($column_infos);
  }
}
