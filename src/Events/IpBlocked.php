<?php

namespace BlackpigCreatif\Replique\Events;

use Illuminate\Foundation\Events\Dispatchable;

class IpBlocked
{
    use Dispatchable;

    public function __construct(
        public readonly string $ip,
        public readonly ?string $reason,
    ) {}
}
