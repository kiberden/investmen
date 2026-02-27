<?php

declare(strict_types = 1);

namespace App\Actions\Broker;

use App\Models\Broker;
use App\Models\BrokerCredential;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

final class SyncBrokerCredentialAction
{
    /**
     * @return list<string>
     */
    public static function credentialColumns(): array
    {
        /** @var list<string> $fillable */
        $fillable = ( new BrokerCredential )->getFillable();

        return array_values(array_intersect($fillable, ['token', 'secret', 'expire_at']));
    }

    public function execute(Broker $broker, Request $request): void
    {
        $credentialData = $this->resolveCredentialData($request);

        if ($credentialData === []) {
            return;
        }

        /** @var array<string, mixed> $validatedData */
        $validatedData = Validator::make($credentialData, [
            'token' => ['sometimes', 'string'],
            'secret' => ['sometimes', 'string'],
            'expire_at' => ['sometimes', 'date'],
        ])->validate();

        if ($validatedData === []) {
            return;
        }

        $broker->credential()->updateOrCreate([], $validatedData);
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveCredentialData(Request $request): array
    {
        /** @var array<string, mixed> $requestData */
        $requestData = $request->all();
        $candidates = [
            $request->only(self::credentialColumns()),
            data_get($requestData, 'data', []),
            data_get($requestData, 'item', []),
        ];

        foreach ($candidates as $candidate) {
            if (!\is_array($candidate)) {
                continue;
            }

            $credentialData = $this->extractCredentialData($candidate);

            if ($credentialData !== []) {
                return $credentialData;
            }
        }

        return [];
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function extractCredentialData(array $data): array
    {
        return array_filter(
            [
                'token' => $data['token'] ?? null,
                'secret' => $data['secret'] ?? null,
                'expire_at' => $data['expire_at'] ?? null,
            ],
            static fn(mixed $value): bool => $value !== null && $value !== '',
        );
    }
}
