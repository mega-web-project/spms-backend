<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Check if super admin already exists
        $email = 'admin@example.com';
        if (User::where('email', $email)->exists()) {
            $this->command->info('Admin already exists!');
            return;
        }

        // Create the super admin user
        $user = User::create([
            'full_name'  => 'Admin',
            'email'      => $email,
            'password'   => Hash::make('Password123!'),
            'status' => 'active',
            'created_by' => null, // first account
        
        ]);

        //admin role
        $role = Role::where('name', 'admin')->first();
        if ($role) {
            
            $user->roles()->attach($role->id);
        }

        $this->command->info("Admin created: {$email} / Password123!");
    }
}
