<?php

declare(strict_types = 1);

namespace Tests\Feature\Broker;

use App\MoonShine\Resources\Broker\Support\BrokerProviderCodeSupport;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

final class BrokerProviderCodeValidationTest extends TestCase
{
    public function test_it_accepts_valid_provider_code(): void
    {
        config()->set('broker-providers.providers', [
            'tbank' => ['name' => 'T-Bank'],
            'alfa' => ['name' => 'Alfa'],
        ]);

        $validator = Validator::make(
            ['provider_code' => 'tbank'],
            ['provider_code' => BrokerProviderCodeSupport::validationRules()],
        );

        $this->assertTrue($validator->passes(), 'Valid provider_code must pass validation.');
    }

    public function test_it_rejects_unknown_provider_code(): void
    {
        config()->set('broker-providers.providers', [
            'tbank' => ['name' => 'T-Bank'],
        ]);

        $validator = Validator::make(
            ['provider_code' => 'unknown'],
            ['provider_code' => BrokerProviderCodeSupport::validationRules()],
        );

        $this->assertTrue($validator->fails(), 'Unknown provider_code must fail validation.');
        $this->assertArrayHasKey('provider_code', $validator->errors()->messages());
    }

    public function test_it_rejects_empty_provider_code(): void
    {
        config()->set('broker-providers.providers', [
            'tbank' => ['name' => 'T-Bank'],
        ]);

        $validator = Validator::make(
            ['provider_code' => ''],
            ['provider_code' => BrokerProviderCodeSupport::validationRules()],
        );

        $this->assertTrue($validator->fails(), 'Empty provider_code must fail validation.');
    }

    public function test_it_rejects_when_no_providers_are_configured(): void
    {
        config()->set('broker-providers.providers', []);

        $validator = Validator::make(
            ['provider_code' => 'tbank'],
            ['provider_code' => BrokerProviderCodeSupport::validationRules()],
        );

        $this->assertTrue($validator->fails(), 'Validation must fail when provider list is empty.');
        $this->assertContains(
            'No broker providers configured for selection.',
            $validator->errors()->get('provider_code'),
        );
    }
}
