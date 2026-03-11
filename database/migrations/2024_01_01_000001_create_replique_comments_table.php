<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('replique_comments', function (Blueprint $table): void {
            $table->id();

            // Polymorphic commentable
            $table->string('commentable_type');
            $table->unsignedBigInteger('commentable_id');
            $table->index(['commentable_type', 'commentable_id']);

            // Polymorphic commentator (nullable for anonymous)
            $table->string('commentator_type')->nullable();
            $table->unsignedBigInteger('commentator_id')->nullable();
            $table->index(['commentator_type', 'commentator_id']);

            // Anonymous submission fields
            $table->string('anonymous_email')->nullable();
            $table->string('anonymous_name')->nullable();

            // Comment content
            $table->text('original_text');
            $table->text('text');
            $table->string('text_mode')->default('escaped_html');

            // Threading
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->foreign('parent_id')->references('id')->on('replique_comments')->nullOnDelete();
            $table->tinyInteger('depth')->default(0)->unsigned();

            // Moderation
            $table->string('status')->default('pending');
            $table->string('ip_address')->nullable()->index();
            $table->boolean('is_pinned')->default(false);
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('replique_comments');
    }
};
