<?php

namespace Tests\Feature;

use App\Enums\ContestType;
use App\Models\Registration;
use App\Models\SiteSetting;
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

        config([
            'services.reve_sms.api_key' => 'test-key',
            'services.reve_sms.secret_key' => 'test-secret',
            'services.reve_sms.caller_id' => 'TEST',
        ]);
    }

    public function test_registration_and_payment_stay_open_by_default(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('data-testid="contest-type-selector"', false);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-testid="nav-register-btn"', false)
            ->assertSee('data-testid="nav-payment-verify-btn"', false);
    }

    public function test_closing_registration_hides_the_form_and_rejects_new_entries(): void
    {
        SiteSetting::current()->update(['registration_enabled' => false]);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('data-testid="nav-register-btn"', false)
            ->assertDontSee('data-testid="copy-link-szpc"', false)
            ->assertSee('data-testid="hero-registration-closed"', false);

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('data-testid="registration-closed"', false)
            ->assertDontSee('data-testid="registration-form"', false);

        $this->post(route('register.store'), [
            'contest_type' => ContestType::Szpc->value,
            'team_name' => 'Byte Force',
            'university' => 'University of Global Village',
            'member1_full_name' => 'Ada Lovelace',
            'member1_phone' => '01700000001',
            'member1_tshirt' => 'M',
        ])->assertRedirect(route('register'));

        $this->assertDatabaseCount('registrations', 0);
    }

    public function test_closing_payment_hides_the_transaction_form_and_rejects_submission(): void
    {
        SiteSetting::current()->update(['payment_enabled' => false]);

        $registration = Registration::query()->create([
            'reference_code' => 'SZPC0001',
            'contest_type' => ContestType::Szpc,
            'team_name' => 'Byte Force',
            'phone' => '01700000001',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('data-testid="nav-payment-verify-btn"', false);

        $this->get(route('payment.show', $registration->reference_code))
            ->assertOk()
            ->assertSee('data-testid="payment-closed"', false)
            ->assertDontSee('data-testid="payment-transaction-form"', false);

        $this->post(route('payment.transaction', $registration->reference_code), [
            'payment_transaction_id' => '8N90ABCD12',
        ])->assertSessionHasErrors('payment_transaction_id');

        $this->assertNull($registration->fresh()->payment_transaction_id);
    }
}
