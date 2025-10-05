<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(config('settings.tables.user_settings', 'user_settings'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('key')->index();
            $table->json('label')->nullable();
            $table->json('description')->nullable();
            $table->text('value')->nullable();
            $table->string('type')->default('string');
            $table->boolean('encrypted')->default(false);
            $table->json('validation_rules')->nullable();
            $table->json('options')->nullable();
            $table->string('input_type')->default('text');
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('settings.tables.user_settings', 'user_settings'));
    }
};
