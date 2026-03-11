<?php

namespace BlackpigCreatif\Replique\Facades;

use BlackpigCreatif\Replique\Models\BlockedIp;
use Illuminate\Support\Facades\Facade;

/**
 * @method static BlockedIp blockIp(string $ip, ?string $reason = null)
 * @method static bool isBlocked(string $ip)
 */
class Replique extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \BlackpigCreatif\Replique\Replique::class;
    }
}
