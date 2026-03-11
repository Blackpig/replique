<?php

namespace BlackpigCreatif\Replique\Models;

use Illuminate\Database\Eloquent\Model;

class BlockedIp extends Model
{
    protected $table = 'replique_blocked_ips';

    protected $guarded = [];
}
