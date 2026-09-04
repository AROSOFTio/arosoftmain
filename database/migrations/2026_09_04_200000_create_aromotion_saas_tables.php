<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aromotion_subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('plan', 40)->default('beta');
            $table->string('status', 24)->default('active');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('current_period_ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'plan']);
        });

        Schema::create('aromotion_devices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('device_uuid', 120);
            $table->string('device_name', 180)->nullable();
            $table->string('platform', 40)->default('windows');
            $table->string('app_version', 40)->nullable();
            $table->string('token_hash', 64)->unique();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'device_uuid']);
            $table->index(['user_id', 'last_seen_at']);
        });

        Schema::create('aromotion_projects', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('project_uuid', 120);
            $table->string('name', 240);
            $table->unsignedBigInteger('duration_ms')->default(0);
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('status', 32)->default('local');
            $table->string('app_version', 40)->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'project_uuid']);
            $table->index(['user_id', 'last_synced_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aromotion_projects');
        Schema::dropIfExists('aromotion_devices');
        Schema::dropIfExists('aromotion_subscriptions');
    }
};
