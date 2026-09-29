<?php

namespace Tests\Feature;

use App\Enums\ContestType;
use App\Models\Registration;
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

        config([
            'services.reve_sms.api_key' => 'test-key',
            'services.reve_sms.secret_key' => 'test-secret',
            'services.reve_sms.caller_id' => 'TEST',
        ]);
    }

    public function test_szpc_and_jpc_can_submit_a_bkash_transaction_id(): void
    {
        $szpc = $this->registration(ContestType::Szpc, 'SZPC0001');
        $jpc = $this->registration(ContestType::Jpc, 'JPC00001');

        $this->get(route('payment.show', $szpc->reference_code))
            ->assertOk()
            ->assertSee('Pay registration fee (SZPC-2026)')
            ->assertSee('৳1,000')
            ->assertSee('data-testid="payment-transaction-form"', false);

        $this->post(route('payment.transaction', $szpc->reference_code), [
            'payment_transaction_id' => '8N90ABCD12',
        ])->assertRedirect(route('payment.show', $szpc->reference_code));

        $this->assertDatabaseHas('registrations', [
            'id' => $szpc->id,
            'payment_transaction_id' => '8N90ABCD12',
        ]);

        $this->get(route('payment.show', $jpc->reference_code))
            ->assertOk()
            ->assertSee('Pay registration fee (JPC-2026)')
            ->assertSee('৳500');

        $this->post(route('payment.transaction', $jpc->reference_code), [
            'payment_transaction_id' => '9K11WXYZ34',
        ])->assertRedirect(route('payment.show', $jpc->reference_code));

        $this->assertNotNull($jpc->fresh()->payment_submitted_at);
    }

    public function test_success_page_shows_bkash_payment_for_szpc(): void
    {
        $registration = $this->registration(ContestType::Szpc, 'SZPC0002');

        $this->withSession([
            'registration_success' => [
                'reference_code' => $registration->reference_code,
                'contest_type' => $registration->contest_type->value,
                'phone' => $registration->phone,
            ],
        ])->get(route('register.success'))
            ->assertOk()
            ->assertSee('Pay registration fee (SZPC-2026)')
            ->assertSee('৳1,000');
    }

    private function registration(ContestType $contest, string $reference): Registration
    {
        return Registration::query()->create([
            'reference_code' => $reference,
            'contest_type' => $contest,
            'team_name' => 'Byte Force',
            'phone' => '01700000001',
        ]);
    }
}
