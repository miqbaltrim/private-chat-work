<?php
// database/seeders/DefaultRoomSeeder.php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultRoomSeeder extends Seeder
{
    public function run(): void
    {
        // Create a system/admin user
        $admin = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'display_name' => 'Admin',
                'password' => Hash::make('admin123'),
            ]
        );

        // Create default General room
        $general = Room::firstOrCreate(
            ['slug' => 'general'],
            [
                'name' => 'General',
                'slug' => 'general',
                'description' => 'Room umum untuk semua orang',
                'is_private' => false,
                'created_by' => $admin->id,
            ]
        );

        // Add admin to general room
        $general->members()->syncWithoutDetaching([$admin->id => ['role' => 'admin']]);

        $this->command->info('Default room "General" created.');
        $this->command->info('Admin account: admin / admin123');
    }
}
