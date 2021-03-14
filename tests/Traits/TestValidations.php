<?php

declare(strict_types=1);

namespace Tests\Traits;

use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Support\Facades\Lang;

trait TestValidations
{
    protected function assertInvalidationFieldsInStoreAction(
        array $data,
        string $rule,
        array $ruleParams = []
    )
    {
        $response = $this->json('POST', $this->routeStore(), $data);
        $fields = array_keys($data);
        $this->assertInvalidationFields($response, $fields, $rule, $ruleParams);
    }

    protected function assertInvalidationFieldsInUpdateAction(
        array $data,
        string $rule,
        array $ruleParams = []
    )
    {
        $response = $this->json('PUT', $this->routeUpdate(), $data);
        $fields = array_keys($data);
        $this->assertInvalidationFields($response, $fields, $rule, $ruleParams);
    }

    private function assertInvalidationFields(TestResponse $response, array $fields, string $rule, array $ruleParams = [])
    {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors($fields);

        foreach ($fields as $field) {
            $fieldName = str_replace('_', ' ', $field);
            $response
                ->assertJsonFragment([
                Lang::get("validation.{$rule}", ['attribute' => $fieldName] + $ruleParams)
            ]);
        }
    }
}
