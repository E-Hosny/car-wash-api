<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class CreateTestUser extends Command
{
    protected $signature = 'user:create-test';
    protected $description = 'Create a test user and return token';

    public function handle()
    {
        $user = User::where('email', 'test@example.com')->first();
        
        if (!$user) {
            $user = User::create([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => bcrypt('password123'),
                'phone' => '1234567890'
            ]);
            $this->info('User created: ' . $user->email);
        } else {
            $this->info('User found: ' . $user->email);
        }
        
        $token = $user->createToken('test-token')->plainTextToken;
        $this->info('Token: ' . $token);
        
        return 0;
    }
}