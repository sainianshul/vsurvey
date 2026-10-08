<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin User
        if (!User::where('phone', '9999999999')->exists()) {
            User::create([
                'name' => 'Admin User',
                'phone' => '9999999999',
                'email' => 'admin@vsurvey.com',
                'password' => Hash::make('12345678'),
                'role' => User::ROLE_ADMIN,
                'status' => User::STATUS_ACTIVE,
                'phone_verified_at' => now(),
            ]);
        }
        $indianNames = [
            'Rahul Sharma', 'Priya Patel', 'Amit Kumar', 'Sneha Gupta', 'Vikram Singh',
            'Anjali Desai', 'Rajesh Verma', 'Neha Joshi', 'Sanjay Mishra', 'Pooja Reddy',
            'Karan Malhotra', 'Divya Iyer', 'Ravi Tiwari', 'Kavita Rao', 'Arjun Kapoor',
            'Swati Menon', 'Manoj Nair', 'Riya Chawla', 'Sunil Yadav', 'Ananya Banerjee',
            'Siddharth Bhatia', 'Shruti Ahuja', 'Gaurav Jain', 'Nisha Agarwal', 'Vikas Pandey',
            'Deepika Mehra', 'Saurabh Dubey', 'Tanvi Rathi', 'Akash Kadam', 'Megha Sinha'
        ];

        $cities = [
            'Mumbai', 'Delhi', 'Bangalore', 'Hyderabad', 'Ahmedabad', 'Chennai', 
            'Kolkata', 'Surat', 'Pune', 'Jaipur', 'Lucknow', 'Kanpur'
        ];

        $password = Hash::make('password123');
        $basePhone = 9800000000;

        foreach ($indianNames as $index => $name) {
            $email = Str::slug($name) . '@example.com';
            $phone = (string)($basePhone + $index);
            
            // Avoid inserting if user with same phone already exists
            if (!User::where('phone', $phone)->exists()) {
                User::create([
                    'name' => $name,
                    'phone' => $phone,
                    'email' => $email,
                    'password' => $password,
                    'role' => User::ROLE_USER,
                    'status' => User::STATUS_ACTIVE,
                    'phone_verified_at' => now(),
                    'city' => $cities[array_rand($cities)],
                    'created_by' => User::CREATED_BY_ADMIN,
                ]);
            }
        }
    }
}