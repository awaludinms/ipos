<?php

namespace Database\Seeders;

use DB;
use Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table("users")->insert([
            'name' => 'Admin',
            'email' => 'admin@ipos.app',
            'password' => Hash::make('#indonesiaNegaraku#')
        ]);
    }
}
