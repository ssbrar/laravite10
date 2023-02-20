<?php

namespace Database\Seeders;


use DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'staff_id' => 'SID'.rand(1,100).strtoupper(substr(uniqid(), -4 )),
            'email' => 'admin@demo.com',
            'first_name' => 'admin',
            'last_name'=>'admin',
            'phone'=>'8888888888',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'password' => Hash::make('password'),
            'role_id' => 0,
            'status' => 1,
            'remember_token' => Str::random(10),
        ]);
    }
}
