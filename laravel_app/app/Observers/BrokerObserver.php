<?php

declare(strict_types = 1);

namespace App\Observers;

use App\Actions\Broker\SyncBrokerCredentialAction;
use App\Models\Broker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Наблюдатель модели Broker: подготавливает данные перед сохранением и синхронизирует credential.
 */
final class BrokerObserver
{
    /**
     * @param SyncBrokerCredentialAction $syncBrokerCredentialAction Экшн синхронизации данных credential.
     */
    public function __construct(
        private SyncBrokerCredentialAction $syncBrokerCredentialAction,
    ) {}

    /**
     * Обрабатывает создание брокера: очищает транзитные поля и назначает владельца.
     */
    public function creating(Broker $broker): void
    {
        $this->sanitizeTransientCredentialFields($broker);
        $broker->user_id = Auth::id();
    }

    /**
     * Очищает транзитные поля формы перед любым сохранением модели.
     */
    public function saving(Broker $broker): void
    {
        $this->sanitizeTransientCredentialFields($broker);
    }

    /**
     * После сохранения брокера синхронизирует credential из текущего HTTP request.
     */
    public function saved(Broker $broker): void
    {
        if (!app()->bound('request')) {
            return;
        }

        /** @var mixed $request */
        $request = app('request');

        if (!$request instanceof Request) {
            return;
        }

        $this->syncBrokerCredentialAction->execute($broker, $request);
    }

    /**
     * Удаляет из модели поля формы, которые не должны попадать в таблицу brokers.
     */
    private function sanitizeTransientCredentialFields(Broker $broker): void
    {
        $transientFields = ['token', 'expire_at'];
        $strippedFields = [];
        $attributes = $broker->getAttributes();

        foreach ($transientFields as $field) {
            if (\array_key_exists($field, $attributes)) {
                $strippedFields[] = $field;
                $broker->offsetUnset($field);
            }
        }

        if ($strippedFields !== []) {
            Log::debug('[FIX] Removed transient broker form fields before persistence.', [
                'fields' => $strippedFields,
                'broker_id' => $broker->getKey(),
            ]);
        }
    }
}
