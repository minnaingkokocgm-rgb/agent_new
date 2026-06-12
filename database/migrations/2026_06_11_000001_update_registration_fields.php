<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            // Remove old fields no longer needed
            $table->dropColumn([
                'organization',
                'occupation',
                'age_range',
                'job_title',
                'country',
                'source',
                'notes',
            ]);
        });

        Schema::table('registrations', function (Blueprint $table) {
            // Add new fields
            $table->string('industry')->nullable()->after('company');
            $table->string('department')->nullable()->after('industry');
            $table->string('post')->nullable()->after('department');
            $table->string('password')->nullable()->after('email');
            $table->string('reception_category')->nullable()->after('opt_out');
            $table->string('responsible_organization')->nullable()->after('reception_category');
        });

        Schema::table('visitors', function (Blueprint $table) {
            // Remove old fields
            $table->dropColumn([
                'organization',
                'occupation',
                'age_range',
                'job_title',
                'country',
            ]);
        });

        Schema::table('visitors', function (Blueprint $table) {
            // Add new fields (no password for visitors)
            $table->string('industry')->nullable()->after('company');
            $table->string('department')->nullable()->after('industry');
            $table->string('post')->nullable()->after('department');
            $table->string('reception_category')->nullable()->after('opt_out');
            $table->string('responsible_organization')->nullable()->after('reception_category');
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn([
                'industry',
                'department',
                'post',
                'password',
                'reception_category',
                'responsible_organization',
            ]);
        });

        Schema::table('registrations', function (Blueprint $table) {
            $table->string('organization')->nullable();
            $table->string('occupation')->nullable();
            $table->string('age_range')->nullable();
            $table->string('job_title')->nullable();
            $table->string('country')->nullable();
            $table->string('source')->nullable();
            $table->text('notes')->nullable();
        });

        Schema::table('visitors', function (Blueprint $table) {
            $table->dropColumn([
                'industry',
                'department',
                'post',
                'reception_category',
                'responsible_organization',
            ]);
        });

        Schema::table('visitors', function (Blueprint $table) {
            $table->string('organization')->nullable();
            $table->string('occupation')->nullable();
            $table->string('age_range')->nullable();
            $table->string('job_title')->nullable();
            $table->string('country')->nullable();
        });
    }
};
