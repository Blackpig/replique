<?php

namespace BlackpigCreatif\Replique\Database\Factories;

use BlackpigCreatif\Replique\Enums\CommentStatus;
use BlackpigCreatif\Replique\Enums\TextMode;
use BlackpigCreatif\Replique\Models\Comment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition(): array
    {
        $text = fake()->paragraph();

        return [
            'commentable_type' => 'App\\Models\\Post',
            'commentable_id' => fake()->numberBetween(1, 100),
            'commentator_type' => null,
            'commentator_id' => null,
            'anonymous_email' => fake()->safeEmail(),
            'anonymous_name' => fake()->name(),
            'original_text' => $text,
            'text' => htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'text_mode' => TextMode::EscapedHtml,
            'parent_id' => null,
            'depth' => 0,
            'status' => CommentStatus::Pending,
            'ip_address' => fake()->ipv4(),
            'is_pinned' => false,
            'approved_at' => null,
            'approved_by' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => CommentStatus::Approved,
            'approved_at' => now(),
            'approved_by' => 1,
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => CommentStatus::Rejected,
        ]);
    }

    public function spam(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => CommentStatus::Spam,
        ]);
    }

    public function pinned(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_pinned' => true,
        ]);
    }

    public function replyTo(Comment $parent): static
    {
        return $this->state(fn (array $attributes): array => [
            'commentable_type' => $parent->commentable_type,
            'commentable_id' => $parent->commentable_id,
            'parent_id' => $parent->id,
            'depth' => $parent->depth + 1,
        ]);
    }

    public function anonymous(): static
    {
        return $this->state(fn (array $attributes): array => [
            'commentator_type' => null,
            'commentator_id' => null,
        ]);
    }
}
