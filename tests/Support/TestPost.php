<?php

namespace BlackpigCreatif\Replique\Tests\Support;

use BlackpigCreatif\Replique\Concerns\HasComments;
use Illuminate\Database\Eloquent\Model;

class TestPost extends Model
{
    use HasComments;

    protected $table = 'test_posts';

    protected $guarded = [];
}
