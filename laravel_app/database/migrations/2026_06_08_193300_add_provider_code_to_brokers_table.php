<?php

declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Добавляет provider_code в brokers и заполняет его fallback-значением для существующих записей.
     */
    public function up(): void
    {
        Schema::table('brokers', function (Blueprint $table) {
            $table->string('provider_code')->default('tbank')->after('profile_name');
        });

        /** @var mixed $providers */
        $providers = config('broker-providers.providers', []);
        $fallbackProviderCode = 'tbank';

        if (\is_array($providers)) {
            $firstProviderCode = array_key_first($providers);
            if (\is_string($firstProviderCode) && trim($firstProviderCode) !== '') {
                $fallbackProviderCode = strtolower(trim($firstProviderCode));
            }
        }

        DB::table('brokers')
            ->where(function ($query): void {
                $query->whereNull('provider_code')
                    ->orWhere('provider_code', '=', '');
            })
            ->update(['provider_code' => $fallbackProviderCode]);
    }

    /**
     * Откатывает миграцию, удаляя колонку provider_code.
     */
    public function down(): void
    {
        Schema::table('brokers', function (Blueprint $table) {
            $table->dropColumn('provider_code');
        });
    }
};
