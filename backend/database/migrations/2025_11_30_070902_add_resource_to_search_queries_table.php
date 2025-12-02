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
        Schema::table('search_queries', function (Blueprint $table) {
            $table->string('resource', 20)->default('people')->after('id');
            $table->index('resource');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('search_queries', function (Blueprint $table) {
            $table->dropIndex(['resource']);
            $table->dropColumn('resource');
        });
    }
};
