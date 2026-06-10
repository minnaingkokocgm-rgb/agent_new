<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->string('post_code')->nullable()->after('company');
            $table->string('address')->nullable()->after('post_code');
            $table->string('organization')->nullable()->after('address');
            $table->string('occupation')->nullable()->after('organization');
            $table->string('age_range')->nullable()->after('occupation');
            $table->boolean('opt_out')->default(false)->after('age_range');
        });

        Schema::table('visitors', function (Blueprint $table) {
            $table->string('post_code')->nullable()->after('company');
            $table->string('address')->nullable()->after('post_code');
            $table->string('organization')->nullable()->after('address');
            $table->string('occupation')->nullable()->after('organization');
            $table->string('age_range')->nullable()->after('occupation');
            $table->boolean('opt_out')->default(false)->after('age_range');
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn(['post_code', 'address', 'organization', 'occupation', 'age_range', 'opt_out']);
        });

        Schema::table('visitors', function (Blueprint $table) {
            $table->dropColumn(['post_code', 'address', 'organization', 'occupation', 'age_range', 'opt_out']);
        });
    }
};
