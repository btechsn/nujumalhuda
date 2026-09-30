<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            
            $table->char('user_id', 26);
            $table->string('type', 100);
            $table->string('channel', 20);
            
            $table->string('title');
            $table->text('message');
            $table->jsonb('data')->nullable();
            $table->string('action_url')->nullable();
            
            $table->timestampTz('read_at')->nullable();
            $table->timestampTz('sent_at')->nullable();
            $table->timestampsTz();
            
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
            
            $table->index(['user_id', 'read_at']);
            $table->index(['user_id', 'type']);
            
            $table->check('channel IN (\'in_app\', \'email\', \'sms\', \'push\')');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
