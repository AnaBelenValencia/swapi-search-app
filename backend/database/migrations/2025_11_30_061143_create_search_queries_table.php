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
        Schema::create('search_queries', function (Blueprint $table) {
            $table->id();
            $table->string('term')->nullable();
            $table->unsignedInteger('page')->default(1);
            $table->unsignedInteger('limit')->default(10);
            $table->unsignedInteger('results_count')->default(0);
            $table->float('response_time_ms')->nullable();
            $table->timestamp('searched_at')->useCurrent();
            $table->timestamps();
            $table->index('term');
            $table->index('searched_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_queries');
    }
};
