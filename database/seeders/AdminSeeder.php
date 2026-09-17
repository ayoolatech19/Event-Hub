<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@eventhub.test'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
            ]
        );

        if ($admin->role !== 'admin') {
            $admin->role = 'admin';
            $admin->save();
        }

        $organizer = User::firstOrCreate(
            ['email' => 'organizer@eventhub.test'],
            [
                'name' => 'Organizer',
                'password' => Hash::make('password'),
            ]
        );

        if ($organizer->role !== 'organizer') {
            $organizer->role = 'organizer';
            $organizer->save();
        }
    }
}