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
        Schema::create('concours_sessions', function (Blueprint $table) {
            $table->id();
            $table->integer('concours_id');
            $table->integer('session_id');
            $table->foreign('concours_id')->references('id')->on('concours')->onDelete('cascade');
            $table->foreign('session_id')->references('id')->on('filieres')->onDelete('cascade');
            $table->unique(["session_id", "concours_id"]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('concours_sessions');
    }
};
