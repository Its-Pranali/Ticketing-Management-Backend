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
        Schema::create('talukas', function (Blueprint $table) {
            $table->id();
            $table->integer('state_id');
            $table->integer('dist_id');
            $table->string('taluka_name',255);
            $table->string('taluka_name_ll');
            $table->string('status',255);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('talukas');
    }
};
