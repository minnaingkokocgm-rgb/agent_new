<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visitors', function (Blueprint $table) {
            $table->string('company')->nullable()->after('email');
            $table->string('job_title')->nullable()->after('company');
            $table->string('country')->nullable()->after('job_title');
        });
    }

    public function down(): void
    {
        Schema::table('visitors', function (Blueprint $table) {
            $table->dropColumn(['company', 'job_title', 'country']);
        });
    }
};
