<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sms_digest_subscribers', function (Blueprint $table) {
            $table->timestamp('last_sent_at')->nullable()->after('last_prepared_at');
        });
    }

    public function down(): void
    {
        Schema::table('sms_digest_subscribers', function (Blueprint $table) {
            $table->dropColumn('last_sent_at');
        });
    }
};
