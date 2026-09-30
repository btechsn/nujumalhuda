<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_snapshots', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            
            $table->date('date')->index();
            $table->string('metric', 100);
            $table->string('dimension', 100)->nullable();
            $table->decimal('value', 20, 4);
            $table->jsonb('metadata')->nullable();
            
            $table->timestampsTz();
            
            $table->unique(['date', 'metric', 'dimension'], 'unique_date_metric_dimension');
            $table->index(['metric', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_snapshots');
    }
};
