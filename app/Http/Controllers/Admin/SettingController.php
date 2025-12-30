<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SettingService;
use App\Services\UploadService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function __construct(
        protected SettingService $settingService,
        protected UploadService $uploadService
    ) {}

    public function index()
    {
        $settings = $this->settingService->getSystemInfo();

        return Inertia::render('Admin/Settings/Index', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'app_name' => 'required|string|max:255',
            'app_description' => 'nullable|string|max:1000',
            'app_icon' => 'nullable|file|image|max:2048',
        ]);

        // Update app name
        $this->settingService->set('app_name', $validated['app_name']);

        // Update app description
        if (isset($validated['app_description'])) {
            $this->settingService->set('app_description', $validated['app_description']);
        }

        // Handle icon upload
        if ($request->hasFile('app_icon')) {
            $file = $request->file('app_icon');
            $path = $file->store('settings', 'public');
            $url = asset('storage/' . $path);
            $this->settingService->set('app_icon_url', $url);
        }

        return redirect()->back()->with('success', 'Settings updated successfully');
    }
}
