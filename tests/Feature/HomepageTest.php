<?php

namespace Tests\Feature;

use Tests\TestCase;

class HomepageTest extends TestCase
{
    public function test_homepage_uses_the_eduka_brand_shell(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Tingkatkan Potensi.');
        $response->assertSee('Bangun Masa Depan.');
        $response->assertSee('Mulai Belajar');
    }
}
