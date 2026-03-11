<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('replique_blocked_ips', function (Blueprint $table): void {
            $table->id();
            $table->string('ip_address')->unique();
            $table->string('reason')->nullable();
            $table->unsignedBigInteger('blocked_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('replique_blocked_ips');
    }
};
