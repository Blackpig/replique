<?php

use BlackpigCreatif\Replique\Enums\CommentStatus;
use BlackpigCreatif\Replique\Filament\Resources\CommentResource\Pages\ListComments;
use BlackpigCreatif\Replique\Models\Comment;
use BlackpigCreatif\Replique\Tests\Support\TestUser;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\Testing\TestAction;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->actingAs(
        TestUser::factory()->create()
    );
});

it('can list comments in the table', function (): void {
    $comments = Comment::factory()->count(3)->create();

    livewire(ListComments::class)
        ->assertCanSeeTableRecords($comments);
});

it('can filter comments by status', function (): void {
    $pending = Comment::factory()->create(['status' => CommentStatus::Pending]);
    $approved = Comment::factory()->approved()->create();

    livewire(ListComments::class)
        ->filterTable('status', [CommentStatus::Approved->value])
        ->assertCanSeeTableRecords(collect([$approved]))
        ->assertCanNotSeeTableRecords(collect([$pending]));
});

it('can soft delete a comment via row action', function (): void {
    $comment = Comment::factory()->create();

    livewire(ListComments::class)
        ->callAction(TestAction::make(DeleteAction::class)->table($comment));

    expect($comment->fresh()->trashed())->toBeTrue();
});

it('can restore a soft deleted comment', function (): void {
    $comment = Comment::factory()->create();
    $comment->delete();

    livewire(ListComments::class)
        ->callAction(TestAction::make(RestoreAction::class)->table($comment));

    expect($comment->fresh()->trashed())->toBeFalse();
});

it('can approve a comment via row action', function (): void {
    $comment = Comment::factory()->create(['status' => CommentStatus::Pending]);

    livewire(ListComments::class)
        ->callAction(TestAction::make('approve')->table($comment))
        ->assertNotified();

    expect($comment->fresh()->status)->toBe(CommentStatus::Approved);
});

it('can reject a comment via row action', function (): void {
    $comment = Comment::factory()->create(['status' => CommentStatus::Pending]);

    livewire(ListComments::class)
        ->callAction(TestAction::make('reject')->table($comment))
        ->assertNotified();

    expect($comment->fresh()->status)->toBe(CommentStatus::Rejected);
});

it('can mark a comment as spam via row action', function (): void {
    $comment = Comment::factory()->create(['status' => CommentStatus::Pending]);

    livewire(ListComments::class)
        ->callAction(TestAction::make('markAsSpam')->table($comment))
        ->assertNotified();

    expect($comment->fresh()->status)->toBe(CommentStatus::Spam);
});

it('can post a reply via the reply action', function (): void {
    $parent = Comment::factory()->create();

    livewire(ListComments::class)
        ->callAction(
            TestAction::make('reply')->table($parent),
            [
                'parent_id' => $parent->id,
                'commentable_type' => $parent->commentable_type,
                'commentable_id' => $parent->commentable_id,
                'original_text' => 'Test reply text',
                'text_mode' => 'escaped_html',
            ]
        )
        ->assertNotified('Reply posted.');

    $this->assertDatabaseHas('replique_comments', [
        'parent_id' => $parent->id,
        'original_text' => 'Test reply text',
    ]);
});
