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
        Schema::table('app_users', function (Blueprint $table) {
            $table->time('in_time')->nullable()->after('emp_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_users', function (Blueprint $table) {
            $table->dropColumn('in_time');
        });
    }
};
