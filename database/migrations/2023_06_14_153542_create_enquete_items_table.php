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
        Schema::create('enquete_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enquete_id')->constrained('enquetes');
            $table->integer('no');
            $table->foreignId('enquete_template_id')->nullable();
            $table->string('type', 10)->nullable();
            $table->string('title')->nullable();
            $table->integer('max_length')->nullable();
            $table->text('items')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enquete_items');
    }
};
