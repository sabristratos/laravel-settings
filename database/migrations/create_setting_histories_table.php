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
        Schema::create(config('settings.tables.setting_histories', 'setting_histories'), function (Blueprint $table) {
            $table->id();
            $table->string('setting_key')->index();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->string('old_type')->nullable();
            $table->string('new_type')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('action')->default('updated');
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('settings.tables.setting_histories', 'setting_histories'));
    }
};
