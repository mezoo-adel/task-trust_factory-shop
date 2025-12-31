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
        $settings['mail_from_name'] = $this->settingService->get('mail_from_name');
        $settings['mail_from_address'] = $this->settingService->get('mail_from_address');

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
            'mail_from_name' => 'required|string|max:255',
            'mail_from_address' => 'required|email|max:255',
        ]);

        // Update app name
        $this->settingService->set('app_name', $validated['app_name']);

        // Update app description
        if (isset($validated['app_description'])) {
            $this->settingService->set('app_description', $validated['app_description']);
        }

        // Update mail from name
        $this->settingService->set('mail_from_name', $validated['mail_from_name']);

        // Update mail from address
        $this->settingService->set('mail_from_address', $validated['mail_from_address']);

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
