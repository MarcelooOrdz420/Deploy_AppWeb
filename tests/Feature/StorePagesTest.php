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
            $this->get($path)
                ->assertOk()
                ->assertHeader('Cache-Control', 'no-store, no-cache, must-revalidate, private');
        }
    }
}
