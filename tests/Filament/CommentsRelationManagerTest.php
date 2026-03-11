<?php

use BlackpigCreatif\Replique\Enums\CommentStatus;
use BlackpigCreatif\Replique\Filament\RelationManagers\CommentsRelationManager;
use BlackpigCreatif\Replique\Models\Comment;
use BlackpigCreatif\Replique\Tests\Support\TestPost;
use BlackpigCreatif\Replique\Tests\Support\TestPostResource\Pages\EditTestPost;
use BlackpigCreatif\Replique\Tests\Support\TestUser;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\Testing\TestAction;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->actingAs(TestUser::factory()->create());
    $this->post = TestPost::create(['title' => 'Test Post']);
});

it('can list comments for a commentable model', function (): void {
    $comments = Comment::factory()->count(3)->create([
        'commentable_type' => TestPost::class,
        'commentable_id' => $this->post->id,
    ]);

    livewire(CommentsRelationManager::class, [
        'ownerRecord' => $this->post,
        'pageClass' => EditTestPost::class,
    ])
        ->assertCanSeeTableRecords($comments);
});

it('does not show comments belonging to a different model', function (): void {
    $otherPost = TestPost::create(['title' => 'Other Post']);

    $ours = Comment::factory()->create([
        'commentable_type' => TestPost::class,
        'commentable_id' => $this->post->id,
    ]);

    $theirs = Comment::factory()->create([
        'commentable_type' => TestPost::class,
        'commentable_id' => $otherPost->id,
    ]);

    livewire(CommentsRelationManager::class, [
        'ownerRecord' => $this->post,
        'pageClass' => EditTestPost::class,
    ])
        ->assertCanSeeTableRecords(collect([$ours]))
        ->assertCanNotSeeTableRecords(collect([$theirs]));
});

it('can approve a comment from the relation manager', function (): void {
    $comment = Comment::factory()->create([
        'commentable_type' => TestPost::class,
        'commentable_id' => $this->post->id,
        'status' => CommentStatus::Pending,
    ]);

    livewire(CommentsRelationManager::class, [
        'ownerRecord' => $this->post,
        'pageClass' => EditTestPost::class,
    ])
        ->callAction(TestAction::make('approve')->table($comment))
        ->assertNotified();

    expect($comment->fresh()->status)->toBe(CommentStatus::Approved);
});

it('can reject a comment from the relation manager', function (): void {
    $comment = Comment::factory()->create([
        'commentable_type' => TestPost::class,
        'commentable_id' => $this->post->id,
        'status' => CommentStatus::Pending,
    ]);

    livewire(CommentsRelationManager::class, [
        'ownerRecord' => $this->post,
        'pageClass' => EditTestPost::class,
    ])
        ->callAction(TestAction::make('reject')->table($comment))
        ->assertNotified();

    expect($comment->fresh()->status)->toBe(CommentStatus::Rejected);
});

it('can post a reply from the relation manager', function (): void {
    $parent = Comment::factory()->create([
        'commentable_type' => TestPost::class,
        'commentable_id' => $this->post->id,
    ]);

    livewire(CommentsRelationManager::class, [
        'ownerRecord' => $this->post,
        'pageClass' => EditTestPost::class,
    ])
        ->callAction(
            TestAction::make('reply')->table($parent),
            [
                'parent_id' => $parent->id,
                'original_text' => 'A reply from the relation manager',
                'text_mode' => 'escaped_html',
            ]
        )
        ->assertNotified();

    $this->assertDatabaseHas('replique_comments', [
        'parent_id' => $parent->id,
        'original_text' => 'A reply from the relation manager',
        'status' => CommentStatus::Approved->value,
    ]);
});

it('can soft delete a comment from the relation manager', function (): void {
    $comment = Comment::factory()->create([
        'commentable_type' => TestPost::class,
        'commentable_id' => $this->post->id,
    ]);

    livewire(CommentsRelationManager::class, [
        'ownerRecord' => $this->post,
        'pageClass' => EditTestPost::class,
    ])
        ->callAction(TestAction::make(DeleteAction::class)->table($comment));

    expect($comment->fresh()->trashed())->toBeTrue();
});

it('can restore a soft deleted comment from the relation manager', function (): void {
    $comment = Comment::factory()->create([
        'commentable_type' => TestPost::class,
        'commentable_id' => $this->post->id,
    ]);
    $comment->delete();

    livewire(CommentsRelationManager::class, [
        'ownerRecord' => $this->post,
        'pageClass' => EditTestPost::class,
    ])
        ->callAction(TestAction::make(RestoreAction::class)->table($comment));

    expect($comment->fresh()->trashed())->toBeFalse();
});
