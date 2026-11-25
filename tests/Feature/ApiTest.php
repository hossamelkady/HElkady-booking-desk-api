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

    public function test_overlapping_booking_is_rejected(): void
    {
        $base = ['customer_name' => 'A', 'service' => 'S', 'starts_at' => '2026-12-02T10:00:00Z', 'ends_at' => '2026-12-02T11:00:00Z'];
        $this->postJson('/api/bookings', $base)->assertCreated();
        $this->postJson('/api/bookings', array_merge($base, ['starts_at' => '2026-12-02T10:30:00Z', 'ends_at' => '2026-12-02T11:30:00Z']))->assertUnprocessable();
        $this->postJson('/api/bookings', array_merge($base, ['starts_at' => '2026-12-02T11:00:00Z', 'ends_at' => '2026-12-02T12:00:00Z']))->assertCreated();
    }

    public function test_update_checks_end_against_stored_start(): void
    {
        $id = $this->postJson('/api/bookings', ['customer_name' => 'A', 'service' => 'S', 'starts_at' => '2026-12-02T10:00:00Z', 'ends_at' => '2026-12-02T11:00:00Z'])->json('data.id');
        $this->putJson("/api/bookings/$id", ['ends_at' => '2026-12-02T09:00:00Z'])->assertUnprocessable();
    }
}
