<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateTestUser extends Command
{
    protected $signature = 'user:create {email} {name} {password=password}';
    protected $description = 'Create a test user';

    public function handle()
    {
        $email = $this->argument('email');
        $name = $this->argument('name');
        $password = $this->argument('password');
        
        $existing = User::where('email', $email)->first();
        if ($existing) {
            $this->info("User {$email} already exists");
            return;
        }
        
        User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'email_verified_at' => now(),
        ]);
        
        $this->info("User created: {$email} with password: {$password}");
    }
}
