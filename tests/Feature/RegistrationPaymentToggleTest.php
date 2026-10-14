<?php

namespace Tests\Feature;

use App\Enums\ContestType;
use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationPaymentToggleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (blank(config('app.key'))) {
            config(['app.key' => 'base64:'.base64_encode(str_repeat('a', 32))]);
        }
    }

    public function test_public_registration_and_payment_no_longer_accept_submissions(): void
    {
        $this->post(route('register.store'), [
            'contest_type' => ContestType::Szpc->value,
            'team_name' => 'Byte Force',
            'university' => 'University of Global Village',
            'member1_full_name' => 'Ada Lovelace',
            'member1_phone' => '01700000001',
            'member1_tshirt' => 'M',
        ])->assertRedirect(route('home'));

        $this->assertDatabaseCount('registrations', 0);

        $registration = Registration::query()->create([
            'reference_code' => 'SZPC0001',
            'contest_type' => ContestType::Szpc,
            'team_name' => 'Byte Force',
            'phone' => '01700000001',
        ]);

        $this->post(route('payment.transaction', $registration->reference_code), [
            'payment_transaction_id' => '8N90ABCD12',
        ])->assertRedirect(route('home'));

        $this->assertNull($registration->fresh()->payment_transaction_id);
    }
}
