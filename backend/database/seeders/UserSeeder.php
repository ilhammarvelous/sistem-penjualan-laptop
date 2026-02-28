<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'ilham',
            'email' => 'ilham@gmail.com',
            'no_wa' => '0895414627260',
            'password' => Hash::make('ilham123'),
            'role' => 'super admin',
        ]);

        User::create([
            'name' => 'haru',
            'email' => 'haru@gmail.com',
            'no_wa' => '0892737232737',
            'password' => Hash::make('haru123'),
            'role' => 'admin',
        ]);
    }
}
