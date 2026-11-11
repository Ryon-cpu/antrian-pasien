<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class QueueDisplayControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_display_shows_latest_called_and_next_waiting_queues(): void
    {
        $this->travelTo(today()->setTime(10, 0));

        $calledEarlierPatient = Patient::query()->create([
            'name' => 'Pasien Panggilan Lama',
            'age' => 46,
            'address' => 'Jalan Melati',
            'symptom' => 'Kontrol kesehatan',
        ]);
        $currentPatient = Patient::query()->create([
            'name' => 'Pasien Panggilan Baru',
            'age' => 52,
            'address' => 'Jalan Mawar',
            'symptom' => 'Pemeriksaan rutin',
        ]);
        $waitingLaterPatient = Patient::query()->create([
            'name' => 'Pasien Antrean Ketiga',
            'age' => 31,
            'address' => 'Jalan Kenanga',
            'symptom' => 'Pemeriksaan rutin',
        ]);
        $waitingNextPatient = Patient::query()->create([
            'name' => 'Pasien Antrean Kedua',
            'age' => 29,
            'address' => 'Jalan Anggrek',
            'symptom' => 'Kontrol kesehatan',
        ]);
        $yesterdayPatient = Patient::query()->create([
            'name' => 'Pasien Kemarin',
            'age' => 44,
            'address' => 'Jalan Teratai',
            'symptom' => 'Pemeriksaan rutin',
        ]);

        Queue::query()->create([
            'patient_id' => $calledEarlierPatient->id,
            'queue_number' => 2,
            'position' => 2,
            'queue_date' => today(),
            'status' => Queue::STATUS_CALLED,
            'called_at' => now()->subMinutes(5),
        ]);
        Queue::query()->create([
            'patient_id' => $currentPatient->id,
            'queue_number' => 8,
            'position' => 8,
            'queue_date' => today(),
            'status' => Queue::STATUS_CALLED,
            'called_at' => now()->subMinute(),
        ]);
        Queue::query()->create([
            'patient_id' => $waitingLaterPatient->id,
            'queue_number' => 3,
            'position' => 3,
            'queue_date' => today(),
            'status' => Queue::STATUS_WAITING,
        ]);
        Queue::query()->create([
            'patient_id' => $waitingNextPatient->id,
            'queue_number' => 1,
            'position' => 1,
            'queue_date' => today(),
            'status' => Queue::STATUS_WAITING,
        ]);
        Queue::query()->create([
            'patient_id' => $yesterdayPatient->id,
            'queue_number' => 99,
            'position' => 99,
            'queue_date' => today()->subDay(),
            'status' => Queue::STATUS_WAITING,
        ]);

        $response = $this->get(route('queues.display'));

        $response->assertOk()
            ->assertViewIs('queues.display')
            ->assertViewHas('currentQueue', fn (Queue $queue): bool => $queue->queue_number === 8)
            ->assertViewHas('nextQueues', fn (Collection $queues): bool => $queues->pluck('queue_number')->all() === [1, 3])
            ->assertViewHas('waitingQueueCount', 2)
            ->assertSee('008')
            ->assertSee('001')
            ->assertSee('003')
            ->assertDontSee('099')
            ->assertDontSee('Pasien Panggilan Baru')
            ->assertSee('content="10"', false);
    }

    public function test_public_display_shows_empty_states_when_there_are_no_queues(): void
    {
        $this->get(route('queues.display'))
            ->assertOk()
            ->assertViewHas('currentQueue', null)
            ->assertSee('Belum ada antrean dipanggil')
            ->assertSee('Belum ada antrean menunggu.');
    }
}
