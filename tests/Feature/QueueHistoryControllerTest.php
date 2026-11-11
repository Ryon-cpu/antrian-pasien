<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Queue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QueueHistoryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_queue_history(): void
    {
        $this->get(route('queues.history'))
            ->assertRedirectToRoute('login');
    }

    public function test_queue_history_shows_only_the_selected_date_and_status_counts(): void
    {
        $this->travelTo(today()->setTime(9, 0));
        $user = User::factory()->create();
        $selectedDate = today()->subDay()->toDateString();
        $doctor = Doctor::query()->create([
            'full_name' => 'Maya Putri',
            'title' => 'dr.',
        ]);

        $waitingQueue = $this->createQueue('Pasien Menunggu', 1, $selectedDate, Queue::STATUS_WAITING);
        $waitingQueue->update(['doctor_id' => $doctor->id]);
        $this->createQueue('Pasien Dipanggil', 2, $selectedDate, Queue::STATUS_CALLED);
        $this->createQueue('Pasien Selesai', 3, $selectedDate, Queue::STATUS_COMPLETED);
        $this->createQueue('Pasien Dibatalkan', 4, $selectedDate, Queue::STATUS_CANCELLED);
        $this->createQueue('Pasien Hari Ini', 1, today()->toDateString());

        $this->actingAs($user)
            ->get(route('queues.history', ['date' => $selectedDate]))
            ->assertOk()
            ->assertViewIs('officer.history')
            ->assertViewHas('selectedDate', fn ($date): bool => $date->toDateString() === $selectedDate)
            ->assertViewHas('totalQueueCount', 4)
            ->assertViewHas('waitingQueueCount', 1)
            ->assertViewHas('calledQueueCount', 1)
            ->assertViewHas('completedQueueCount', 1)
            ->assertViewHas('cancelledQueueCount', 1)
            ->assertSee('Menu', false)
            ->assertSee('Pasien Menunggu')
            ->assertSee('Pasien Dipanggil')
            ->assertSee('Pasien Selesai')
            ->assertSee('Pasien Dibatalkan')
            ->assertSee('dr. Maya Putri')
            ->assertDontSee('Pasien Hari Ini')
            ->assertSee(route('queues.history'), false)
            ->assertSee('Riwayat antrean');
    }

    public function test_queue_history_defaults_to_today(): void
    {
        $this->travelTo(today()->setTime(9, 0));
        $user = User::factory()->create();
        $this->createQueue('Pasien Hari Ini', 1, today()->toDateString());
        $this->createQueue('Pasien Kemarin', 1, today()->subDay()->toDateString());

        $this->actingAs($user)
            ->get(route('queues.history'))
            ->assertOk()
            ->assertViewHas('selectedDate', fn ($date): bool => $date->toDateString() === today()->toDateString())
            ->assertSee('Pasien Hari Ini')
            ->assertDontSee('Pasien Kemarin');
    }

    public function test_empty_date_shows_empty_state_and_zero_counts(): void
    {
        $this->travelTo(today()->setTime(9, 0));
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('queues.history', ['date' => today()->subDay()->toDateString()]))
            ->assertOk()
            ->assertViewHas('totalQueueCount', 0)
            ->assertSee('Belum ada riwayat antrean')
            ->assertSee('Tidak ada antrean yang terdaftar pada tanggal ini.');
    }

    public function test_future_history_date_is_rejected_and_returns_to_today(): void
    {
        $this->travelTo(today()->setTime(9, 0));
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('queues.history', ['date' => today()->addDay()->toDateString()]))
            ->assertRedirectToRoute('queues.history', ['date' => today()->toDateString()])
            ->assertSessionHasErrors([
                'date' => 'Tanggal riwayat tidak boleh melewati hari ini.',
            ]);
    }

    public function test_malformed_history_date_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('queues.history', ['date' => 'kemarin']))
            ->assertRedirectToRoute('queues.history', ['date' => today()->toDateString()])
            ->assertSessionHasErrors([
                'date' => 'Format tanggal harus YYYY-MM-DD.',
            ]);
    }

    public function test_queue_history_paginates_and_keeps_the_selected_date_in_links(): void
    {
        $this->travelTo(today()->setTime(9, 0));
        $user = User::factory()->create();
        $selectedDate = today()->subDay()->toDateString();

        for ($queueNumber = 1; $queueNumber <= 16; $queueNumber++) {
            $this->createQueue(
                'Pasien '.str_pad((string) $queueNumber, 2, '0', STR_PAD_LEFT),
                $queueNumber,
                $selectedDate,
            );
        }

        $this->actingAs($user)
            ->get(route('queues.history', ['date' => $selectedDate, 'page' => 2]))
            ->assertOk()
            ->assertViewHas('queues', fn ($queues): bool => $queues->currentPage() === 2
                && $queues->total() === 16
                && str_contains($queues->url(1), 'date='.$selectedDate))
            ->assertSee('Pasien 16')
            ->assertDontSee('Pasien 01');
    }

    private function createQueue(
        string $patientName,
        int $queueNumber,
        string $queueDate,
        string $status = Queue::STATUS_WAITING,
    ): Queue {
        $patient = Patient::query()->create([
            'name' => $patientName,
            'age' => 35,
            'address' => 'Jalan Melati',
            'symptom' => 'Pemeriksaan rutin',
        ]);

        return Queue::query()->create([
            'patient_id' => $patient->id,
            'queue_number' => $queueNumber,
            'position' => $queueNumber,
            'queue_date' => $queueDate,
            'status' => $status,
        ]);
    }
}
