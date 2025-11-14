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
        Schema::create('message_stats', function (Blueprint $table) {
            $table->id();
            $table->date('stat_date');
            $table->string('channel'); // 'web' or 'telegram'
            $table->integer('message_count')->default(0);
            $table->integer('conversation_count')->default(0);
            $table->timestamps();

            $table->unique(['stat_date', 'channel']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_stats');
    }
};
