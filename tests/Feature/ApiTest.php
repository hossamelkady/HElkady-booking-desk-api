<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_endpoint_returns_ok(): void
    {
        $this->getJson('/api/health')->assertOk()->assertJson(['status' => 'ok']);
    }

    public function test_can_create_and_list_bookings(): void
    {
        $this->postJson('/api/bookings', ['customer_name' => 'Samira Hassan', 'service' => 'Consultation', 'starts_at' => '2026-12-02T10:00:00Z', 'ends_at' => '2026-12-02T10:45:00Z', 'status' => 'confirmed'])
            ->assertCreated()->assertJsonPath('data.customer_name', 'Samira Hassan');

        $this->getJson('/api/bookings')->assertOk()->assertJsonPath('total', 1);
    }

    public function test_invalid_payload_is_rejected(): void
    {
        $this->postJson('/api/bookings', [])->assertUnprocessable();
    }
}
