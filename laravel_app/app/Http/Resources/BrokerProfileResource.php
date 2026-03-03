<?php

declare(strict_types = 1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin array<string, mixed>
 */
final class BrokerProfileResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        unset($request);

        return [
            'broker_id' => (int) data_get($this->resource, 'broker_id'),
            'broker_profile_name' => (string) data_get($this->resource, 'broker_profile_name', ''),
            'provider' => (string) data_get($this->resource, 'provider', ''),
            'environment' => (string) data_get($this->resource, 'environment', ''),
            'account_id' => (string) data_get($this->resource, 'account_id', ''),
            'account_name' => (string) data_get($this->resource, 'account_name', ''),
            'account_type' => (string) data_get($this->resource, 'account_type', ''),
            'account_status' => (string) data_get($this->resource, 'account_status', ''),
            'access_level' => (string) data_get($this->resource, 'access_level', ''),
            'opened_at' => data_get($this->resource, 'opened_at'),
            'closed_at' => data_get($this->resource, 'closed_at'),
            'error' => data_get($this->resource, 'error'),
        ];
    }
}
