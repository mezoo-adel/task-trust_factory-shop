<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserService
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    /**
     * Create admin user with random password and send credentials email
     */
    public function createAdmin(array $validated): User
    {
        // Generate random password
        $password = Str::random(10);

        // Create admin user
        $admin = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($password),
            'is_admin' => true,
            'email_verified_at' => now(), // Auto-verify admin emails
        ]);

        // Send login credentials email immediately
        $loginUrl = route('admin.login');
        $this->notificationService->sendEmail(
            $admin,
            'Admin Account Created - ' . config('app.name'),
            "Your admin account has been created successfully!\n\n" .
            "Login URL: {$loginUrl}\n" .
            "Email: {$admin->email}\n" .
            "Temporary Password: {$password}\n\n" .
            "Please login and change your password immediately for security.",
            'Login to Admin Panel',
            $loginUrl
        );

        return $admin;
    }
}

