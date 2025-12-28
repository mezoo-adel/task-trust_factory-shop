<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAddressRequest;
use App\Http\Requests\UpdateAddressRequest;
use App\Http\Requests\UpdateNotificationPreferencesRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Models\Address;
use App\Services\AddressService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function __construct(
        private NotificationService $notificationService,
        private AddressService $addressService
    ) {}

    public function index()
    {
        $user = auth()->user();
        $addresses = Address::where('user_id', $user->id)->get();
        $notificationPreferences = $this->notificationService->getUserChannelPreferences($user);

        return Inertia::render('Profile/Index', [
            'user' => $user,
            'addresses' => $addresses,
            'notification_preferences' => $notificationPreferences,
        ]);
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        $user = auth()->user();
        $user->update([
            'password' => Hash::make($request->validated()['password']),
        ]);

        return back()->with('success', 'Password updated successfully.');
    }

    public function updateNotifications(UpdateNotificationPreferencesRequest $request)
    {
        $user = auth()->user();
        $this->notificationService->updateUserPreferences($user, $request->validated()['preferences']);

        return back()->with('success', 'Notification preferences updated successfully.');
    }

    public function storeAddress(StoreAddressRequest $request)
    {
        $user = auth()->user();
        $this->addressService->create($request->validated(), $user->id);

        return back()->with('success', 'Address added successfully.');
    }

    public function updateAddress(UpdateAddressRequest $request, Address $address)
    {
        $user = auth()->user();
        $this->addressService->update($address, $request->validated(), $user->id);

        return back()->with('success', 'Address updated successfully.');
    }

    public function destroyAddress(Address $address)
    {
        $user = auth()->user();

        // Ensure user owns this address
        if ($address->user_id !== $user->id) {
            abort(403);
        }

        // Check if address can be deleted
        if (!$this->addressService->canDelete($address)) {
            return back()->withErrors([
                'error' => 'Cannot delete address that is linked to existing orders.'
            ]);
        }

        $address->delete();

        return back()->with('success', 'Address deleted successfully.');
    }
}
