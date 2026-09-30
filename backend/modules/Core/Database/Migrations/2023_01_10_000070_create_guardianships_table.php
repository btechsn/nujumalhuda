<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guardianships', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            
            $table->char('guardian_id', 26);
            $table->char('ward_id', 26);
            
            $table->string('relationship', 50)->default('parent');
            $table->boolean('is_primary')->default(false);
            $table->boolean('can_view_progress')->default(true);
            $table->boolean('can_receive_notifications')->default(true);
            $table->text('notes')->nullable();
            
            $table->timestampsTz();
            
            $table->foreign('guardian_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
            
            $table->foreign('ward_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
            
            $table->unique(['guardian_id', 'ward_id']);
            $table->index('ward_id');
            
            $table->check('guardian_id != ward_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guardianships');
    }
};
