<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@crm.com'],
            [
                'name'     => 'Admin',
                'password' => Hash::make('password123'),
                'role'     => 'admin',
                'status'   => 'activo',
            ]
        );
    }
}