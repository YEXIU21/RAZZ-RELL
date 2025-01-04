<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@razzrellevents.com',
            'phone_number' => '09123456789',
            'password' => Hash::make('admin123456'),
            'role' => 'admin',
            'status' => 'active',
            'avatar' => 'images/DefaultProfile/defaultAvatar.png'
        ]);
    }
} 