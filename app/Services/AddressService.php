<?php

namespace App\Services;

use App\Models\Address;

class AddressService
{
    /**
     * Set an address as default and unset others
     */
    public function setAsDefault(Address $address, int $userId): void
    {
        Address::where('user_id', $userId)
            ->where('id', '!=', $address->id)
            ->update(['is_default' => false]);

        $address->update(['is_default' => true]);
    }

    /**
     * Check if an address can be deleted
     */
    public function canDelete(Address $address): bool
    {
        return !$address->orders()->exists();
    }

    /**
     * Create address with default handling
     */
    public function create(array $data, int $userId): Address
    {
        // If setting as default, unset other defaults
        if ($data['is_default'] ?? false) {
            Address::where('user_id', $userId)->update(['is_default' => false]);
        }

        return Address::create([
            ...$data,
            'user_id' => $userId,
        ]);
    }

    /**
     * Update address with default handling
     */
    public function update(Address $address, array $data, int $userId): void
    {
        // If setting as default, unset other defaults
        if ($data['is_default'] ?? false) {
            Address::where('user_id', $userId)
                ->where('id', '!=', $address->id)
                ->update(['is_default' => false]);
        }

        $address->update($data);
    }
}

