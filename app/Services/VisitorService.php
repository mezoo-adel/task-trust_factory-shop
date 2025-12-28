<?php

namespace App\Services;

class VisitorService
{
    /**
     * Get or create visitor fingerprint
     * 
     * Fingerprint is stored in session (not cookies) and persists across:
     * - Page visits within the same session
     * - Browser refresh
     * - Navigation between pages
     * 
     * Fingerprint is generated from: session ID + IP address + User Agent
     * This ensures consistent identification of the same visitor.
     * 
     * @return string Visitor fingerprint
     */
    public function getOrCreateFingerprint(): string
    {
        if (!session()->has('visitor_fingerprint')) {
            $fingerprint = md5(session()->getId() . request()->ip() . request()->userAgent());
            session()->put('visitor_fingerprint', $fingerprint);
        }

        return session()->get('visitor_fingerprint');
    }

    /**
     * Get existing fingerprint from session (without creating)
     * 
     * @return string|null
     */
    public function getFingerprint(): ?string
    {
        return session()->get('visitor_fingerprint');
    }
}

