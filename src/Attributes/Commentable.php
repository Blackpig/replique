<?php

namespace BlackpigCreatif\Replique\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Commentable
{
    public function __construct(public readonly ?string $label = null) {}
}
