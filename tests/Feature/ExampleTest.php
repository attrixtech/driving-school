<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_the_public_portal_returns_a_successful_response(): void
    {
        $response = $this->get('/portal');
        $response->assertStatus(200);
        $response->assertSee('Apex Driving Academy');
    }
}
