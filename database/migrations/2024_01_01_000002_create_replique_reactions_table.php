<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('replique_reactions', function (Blueprint $table): void {
            $table->id();

            $table->unsignedBigInteger('comment_id');
            $table->foreign('comment_id')->references('id')->on('replique_comments')->cascadeOnDelete();

            // Polymorphic reactor (nullable for anonymous)
            $table->string('reactor_type')->nullable();
            $table->unsignedBigInteger('reactor_id')->nullable();
            $table->index(['reactor_type', 'reactor_id']);

            $table->string('anonymous_email')->nullable();

            $table->string('type')->default('like');

            $table->timestamps();

            // One reaction per type per reactor per comment
            $table->unique(['comment_id', 'reactor_type', 'reactor_id', 'type'], 'replique_reactions_reactor_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('replique_reactions');
    }
};
