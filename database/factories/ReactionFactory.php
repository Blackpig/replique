<?php

namespace BlackpigCreatif\Replique\Database\Factories;

use BlackpigCreatif\Replique\Models\Comment;
use BlackpigCreatif\Replique\Models\Reaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reaction>
 */
class ReactionFactory extends Factory
{
    protected $model = Reaction::class;

    public function definition(): array
    {
        return [
            'comment_id' => Comment::factory(),
            'reactor_type' => null,
            'reactor_id' => null,
            'anonymous_email' => fake()->safeEmail(),
            'type' => 'like',
        ];
    }
}
