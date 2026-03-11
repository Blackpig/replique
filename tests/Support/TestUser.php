<?php

namespace BlackpigCreatif\Replique\Tests\Support;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User;
use Orchestra\Testbench\Factories\UserFactory;

class TestUser extends User
{
    use HasFactory;

    protected $table = 'users';

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
