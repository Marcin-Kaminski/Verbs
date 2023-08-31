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
        Schema::create('verbs', function (Blueprint $table) {
            $table->id();
            $table->string('verb_in_polish');
            $table->string('verb_in_infinitive');
            $table->string('verb_in_past_simple');
            $table->string('verb_in_past_participle');
            $table->string('additional_description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verbs');
    }
};
