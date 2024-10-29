<?php

use App\Models\Tenant;
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
        Schema::table('wards', function (Blueprint $table) {
            $table->foreignIdFor(Tenant::class)->nullable()->after('id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->foreignIdFor(Tenant::class)->nullable()->after('id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
        });

        Schema::table('holidays', function (Blueprint $table) {
            $table->foreignIdFor(Tenant::class)->nullable()->after('id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wards', function (Blueprint $table) {
            $table->dropConstrainedForeignIdFor(Tenant::class);
        });
        Schema::table('departments', function (Blueprint $table) {
            $table->dropConstrainedForeignIdFor(Tenant::class);
        });
        Schema::table('holidays', function (Blueprint $table) {
            $table->dropConstrainedForeignIdFor(Tenant::class);
        });
    }
};
