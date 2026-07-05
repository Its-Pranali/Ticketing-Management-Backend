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
        Schema::create('pacs', function (Blueprint $table) {
            $table->id();
            $table->integer('state_id');
            $table->integer('dist_id');
            $table->integer('taluka_id');
            $table->integer('bank_id');
            $table->integer('branch_id');
            $table->string('village_id', 255);
            $table->string('nabard_pacs_id', 255);
            $table->string('pacs_id', 255);
            $table->string('pacs_name', 255);
            $table->string('pacs_name_ll', 255);
            $table->string('ceo_name', 255);
            $table->string('ceo_mobile', 255);
            $table->string('ceo_email', 255);
            $table->string('status', 255);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pacs');
    }
};
