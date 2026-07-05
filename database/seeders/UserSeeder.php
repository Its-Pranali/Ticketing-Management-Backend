<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         User::create([
            'name' => 'System Administrator',
            'email' => 'admin@pondichery.com',
            'password' => Hash::make('Admin@123'), 
            'role' => 1, 
            'status' => 'active',
            'dist_id' => 1,
            'society_id' => 101,
            'mobile' => '9876543210',
            'designation' => 'Super Admin',
            'organization' => 'Government of Pondichery',
        ]);

        User::create([
            'name' => 'Secretary User',
            'email' => 'secretary@pondichery.com',
            'password' => Hash::make('Secretary@123'), 
            'role' => 2, 
            'status' => 'active',
            'dist_id' => 2,
            'society_id' => 102,
            'mobile' => '9876543211',
            'designation' => 'Secretary',
            'organization' => 'Pondichery Dept',
        ]);
        
    }
}
