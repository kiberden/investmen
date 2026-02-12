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
        Schema::create('broker_credentials', function (Blueprint $table) {
            $table->id();
            $table->text('token');
            $table->text('secret');
            $table->dateTime('expire_at')->nullable();
            $table->foreignId('broker_id')->constrained('brokers');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('broker_credentials');
    }
};
