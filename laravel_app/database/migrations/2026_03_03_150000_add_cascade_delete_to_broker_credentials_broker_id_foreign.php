<?php

declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('broker_credentials', function (Blueprint $table): void {
            $table->dropForeign(['broker_id']);
            $table->foreign('broker_id')->references('id')->on('brokers')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('broker_credentials', function (Blueprint $table): void {
            $table->dropForeign(['broker_id']);
            $table->foreign('broker_id')->references('id')->on('brokers');
        });
    }
};
