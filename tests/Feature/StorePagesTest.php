<?php

namespace Tests\Feature;

use Tests\TestCase;

class StorePagesTest extends TestCase
{
    public function test_store_pages_are_available(): void
    {
        $this->get('/productos')->assertOk();
        $this->get('/login')->assertOk();
        $this->get('/admin/login')->assertOk();
    }

    public function test_sensitive_store_pages_disable_browser_cache(): void
    {
        foreach (['/carrito', '/mis-pedidos', '/admin/panel'] as $path) {
            $response = $this->get($path)->assertOk();
            $cacheControl = (string) $response->headers->get('Cache-Control');
            foreach (['no-store', 'no-cache', 'must-revalidate', 'private'] as $directive) {
                $this->assertStringContainsString($directive, $cacheControl);
            }
        }
    }

    public function test_checkout_uses_payment_dropdown_with_izipay_and_cash_on_delivery(): void
    {
        $this->get('/carrito')
            ->assertOk()
            ->assertSee('name="payment_method"', false)
            ->assertSee('value="izipay"', false)
            ->assertSee('value="cod"', false)
            ->assertDontSee('id="paymentInfo"', false);
    }

    public function test_admin_panel_does_not_render_manual_payment_validation(): void
    {
        $this->get('/admin/panel')
            ->assertOk()
            ->assertDontSee('Validar pago digital')
            ->assertDontSee('data-target="sec-company"', false)
            ->assertDontSee('id="sec-company"', false);
    }

    public function test_store_layout_hides_unconfigured_social_links_and_alert_button(): void
    {
        $this->get('/productos')
            ->assertOk()
            ->assertDontSee('id="clientAlertsBtn"', false)
            ->assertDontSee('aria-label="Facebook"', false)
            ->assertDontSee('aria-label="Instagram"', false)
            ->assertDontSee('aria-label="TikTok"', false)
            ->assertDontSee('id="footerWhatsapp"', false);
    }
}
