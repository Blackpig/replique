<?php

namespace BlackpigCreatif\Replique;

use BlackpigCreatif\Replique\Events\IpBlocked;
use BlackpigCreatif\Replique\Models\BlockedIp;
use Illuminate\Support\Facades\Auth;

class Replique
{
    public function blockIp(string $ip, ?string $reason = null): BlockedIp
    {
        $blocked = BlockedIp::updateOrCreate(
            ['ip_address' => $ip],
            [
                'reason' => $reason,
                'blocked_by' => Auth::id(),
            ],
        );

        IpBlocked::dispatch($ip, $reason);

        return $blocked;
    }

    public function isBlocked(string $ip): bool
    {
        return BlockedIp::where('ip_address', $ip)->exists();
    }
}
