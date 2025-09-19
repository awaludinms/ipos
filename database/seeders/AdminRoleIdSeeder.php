<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminRoleIdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table("users")->where('email', 'admin@ipos.app')
            ->update([
                'role_id' => 4, # (4) admin
            ]);
    }
}
