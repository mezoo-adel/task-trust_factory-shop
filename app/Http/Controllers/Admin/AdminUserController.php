<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminUserRequest;
use App\Models\User;
use App\Services\AdminUserService;
use Inertia\Inertia;

class AdminUserController extends Controller
{
    public function __construct(
        private AdminUserService $adminUserService
    ) {}

    public function index()
    {
        $admins = User::where('is_admin', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Admin/Admins/Index', [
            'admins' => $admins,
        ]);
    }

    public function store(StoreAdminUserRequest $request)
    {
        $this->adminUserService->createAdmin($request->validated());

        return back()->with('success', 'Admin user created and credentials sent via email.');
    }
}
