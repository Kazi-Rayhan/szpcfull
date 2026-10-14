<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContestPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (blank(config('app.key'))) {
            config(['app.key' => 'base64:'.base64_encode(str_repeat('a', 32))]);
        }
    }

    public function test_payment_and_registration_success_pages_are_hidden(): void
    {
        $this->get(route('register.success'))->assertRedirect(route('home'));
        $this->get(route('payment.show', 'SZPC0001'))->assertRedirect(route('home'));
    }
}
