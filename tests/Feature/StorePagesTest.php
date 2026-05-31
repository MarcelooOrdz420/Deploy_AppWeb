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
}
