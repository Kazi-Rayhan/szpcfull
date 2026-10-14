<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComingSoonTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (blank(config('app.key'))) {
            config(['app.key' => 'base64:'.base64_encode(str_repeat('a', 32))]);
        }
    }

    public function test_home_shows_the_coming_soon_carousel(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-testid="coming-soon"', false)
            ->assertSee('4th')
            ->assertSee('Coming soon')
            ->assertSee('gallery/one.jpeg', false)
            ->assertSee('gallery/two.jpeg', false)
            ->assertSee('gallery/three.jpeg', false)
            ->assertSee('data-testid="boot-terminal"', false)
            ->assertSee('University of Global Village');
    }

    public function test_old_public_pages_redirect_home(): void
    {
        $this->get(route('segment'))->assertRedirect(route('home'));
        $this->get(route('register'))->assertRedirect(route('home'));
        $this->get(route('payment'))->assertRedirect(route('home'));
        $this->get(route('payment.show', 'SZPC0001'))->assertRedirect(route('home'));
    }
}
