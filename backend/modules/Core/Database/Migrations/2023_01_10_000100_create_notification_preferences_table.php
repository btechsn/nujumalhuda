<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            
            $table->char('user_id', 26);
            $table->string('category', 50);
            $table->string('channel', 20);
            $table->boolean('enabled')->default(true);
            
            $table->timestampsTz();
            
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
            
            $table->unique(['user_id', 'category', 'channel']);
            
            $table->check('channel IN (\'in_app\', \'email\', \'sms\', \'push\')');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
