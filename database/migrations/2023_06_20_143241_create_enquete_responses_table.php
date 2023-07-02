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
        Schema::create('enquete_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('enquete_requests');
            $table->foreignId('enquete_item_id')->constrained('enquete_items');
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enquete_responses');
    }
};
