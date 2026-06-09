<?php

declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropUnique('accounts_broker_id_unique');
            $table->unique(['broker_id', 'external_account_id'], 'accounts_broker_external_account_unique');
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropUnique('accounts_broker_external_account_unique');
            $table->unique('broker_id');
        });
    }
};
