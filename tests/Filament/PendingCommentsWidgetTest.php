<?php

use BlackpigCreatif\Replique\Enums\CommentStatus;
use BlackpigCreatif\Replique\Filament\Widgets\PendingCommentsWidget;
use BlackpigCreatif\Replique\Models\Comment;
use BlackpigCreatif\Replique\Tests\Support\TestUser;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->actingAs(TestUser::factory()->create());
});

it('shows the correct pending comment count', function (): void {
    Comment::factory()->count(3)->create(['status' => CommentStatus::Pending]);
    Comment::factory()->count(2)->approved()->create();

    livewire(PendingCommentsWidget::class)
        ->assertSee('3');
});

it('shows the correct approved today count', function (): void {
    Comment::factory()->count(2)->create([
        'status' => CommentStatus::Approved,
        'approved_at' => now(),
    ]);

    Comment::factory()->create([
        'status' => CommentStatus::Approved,
        'approved_at' => now()->subDays(2),
    ]);

    livewire(PendingCommentsWidget::class)
        ->assertSee('2');
});

it('shows the correct spam count', function (): void {
    Comment::factory()->count(4)->spam()->create();

    livewire(PendingCommentsWidget::class)
        ->assertSee('4');
});
