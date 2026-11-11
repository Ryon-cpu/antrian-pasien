<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Queue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficerDashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_the_dashboard(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirectToRoute('login');
    }

    public function test_dashboard_shows_an_empty_state_when_there_are_no_queues(): void
    {
        $response = $this->actingAs(User::factory()->create())->get(route('dashboard'));

        $response->assertOk()
            ->assertViewHas('totalQueueCount', 0)
            ->assertViewHas('currentQueue', null)
            ->assertSee('Belum ada antrean')
            ->assertSee('Belum ada pasien dipanggil')
            ->assertDontSee('Selesaikan pemeriksaan')
            ->assertDontSee('Panggil antrean berikutnya');
    }

    public function test_dashboard_shows_today_queue_counts_and_the_latest_called_patient(): void
    {
        $this->travelTo(today()->setTime(9, 0));

        $today = today()->toDateString();
        $yesterday = today()->subDay()->toDateString();

        $waitingPatient = Patient::query()->create([
            'name' => 'Pasien Menunggu',
            'age' => 68,
            'address' => 'Jalan Melati',
            'symptom' => 'Pemeriksaan rutin',
        ]);
        $earlierCalledPatient = Patient::query()->create([
            'name' => 'Pasien Panggilan Awal',
            'age' => 72,
            'address' => 'Jalan Mawar',
            'symptom' => 'Kontrol kesehatan',
        ]);
        $currentPatient = Patient::query()->create([
            'name' => 'Pasien Sedang Dilayani',
            'age' => 65,
            'address' => 'Jalan Kenanga',
            'symptom' => 'Pemeriksaan kesehatan',
        ]);
        $completedPatient = Patient::query()->create([
            'name' => 'Pasien Selesai',
            'age' => 61,
            'address' => 'Jalan Anggrek',
            'symptom' => 'Pemeriksaan rutin',
        ]);
        $cancelledPatient = Patient::query()->create([
            'name' => 'Pasien Batal',
            'age' => 70,
            'address' => 'Jalan Kamboja',
            'symptom' => 'Kontrol kesehatan',
        ]);
        $yesterdayPatient = Patient::query()->create([
            'name' => 'Pasien Kemarin',
            'age' => 69,
            'address' => 'Jalan Teratai',
            'symptom' => 'Pemeriksaan rutin',
        ]);
        $doctor = Doctor::query()->create([
            'full_name' => 'Maya Putri',
            'title' => 'dr.',
            'specialization' => 'Dokter Umum',
        ]);

        $waitingQueue = Queue::query()->create([
            'patient_id' => $waitingPatient->id,
            'doctor_id' => $doctor->id,
            'queue_number' => 1,
            'position' => 1,
            'queue_date' => $today,
            'status' => Queue::STATUS_WAITING,
        ]);
        Queue::query()->create([
            'patient_id' => $earlierCalledPatient->id,
            'queue_number' => 2,
            'position' => 2,
            'queue_date' => $today,
            'status' => Queue::STATUS_CALLED,
            'called_at' => now()->subMinutes(10),
        ]);
        Queue::query()->create([
            'patient_id' => $currentPatient->id,
            'queue_number' => 3,
            'position' => 3,
            'queue_date' => $today,
            'status' => Queue::STATUS_CALLED,
            'called_at' => now()->subMinute(),
        ]);
        Queue::query()->create([
            'patient_id' => $completedPatient->id,
            'queue_number' => 4,
            'position' => 4,
            'queue_date' => $today,
            'status' => Queue::STATUS_COMPLETED,
        ]);
        Queue::query()->create([
            'patient_id' => $cancelledPatient->id,
            'queue_number' => 5,
            'position' => 5,
            'queue_date' => $today,
            'status' => Queue::STATUS_CANCELLED,
        ]);
        Queue::query()->create([
            'patient_id' => $yesterdayPatient->id,
            'queue_number' => 1,
            'position' => 1,
            'queue_date' => $yesterday,
            'status' => Queue::STATUS_WAITING,
        ]);

        $response = $this->actingAs(User::factory()->create())->get(route('dashboard'));

        $response->assertOk()
            ->assertViewIs('officer.dashboard')
            ->assertViewHas('totalQueueCount', 5)
            ->assertViewHas('waitingQueueCount', 1)
            ->assertViewHas('calledQueueCount', 2)
            ->assertViewHas('completedQueueCount', 1)
            ->assertViewHas('activeQueueCount', 4)
            ->assertSee('Menu', false)
            ->assertSee('Navigasi utama', false)
            ->assertSee(route('queues.create'), false)
            ->assertSee('Ambil antrean')
            ->assertSee(route('queues.display'), false)
            ->assertSee('Layar antrean')
            ->assertViewHas('currentQueue', fn (Queue $queue): bool => $queue->queue_number === 3
                && $queue->patient->name === 'Pasien Sedang Dilayani')
            ->assertSee('Panggil antrean berikutnya')
            ->assertSee(route('queues.call-next'), false)
            ->assertSee('dr. Maya Putri')
            ->assertSee('Selesaikan pemeriksaan')
            ->assertSee(route('queues.cancel', $waitingQueue), false)
            ->assertSee('Batalkan')
            ->assertSee('Pasien Menunggu')
            ->assertSee('Pasien Sedang Dilayani')
            ->assertSee('Pasien Selesai')
            ->assertSee('Pasien Batal')
            ->assertDontSee('Pasien Kemarin');
    }

    public function test_petugas_calls_the_earliest_waiting_queue_and_logs_the_action(): void
    {
        $this->travelTo(today()->setTime(10, 30));

        $user = User::factory()->create();
        $laterPatient = Patient::query()->create([
            'name' => 'Pasien Antrean Kedua',
            'age' => 35,
            'address' => 'Jalan Melati',
            'symptom' => 'Pemeriksaan rutin',
        ]);
        $nextPatient = Patient::query()->create([
            'name' => 'Pasien Antrean Pertama',
            'age' => 42,
            'address' => 'Jalan Mawar',
            'symptom' => 'Kontrol kesehatan',
        ]);
        $yesterdayPatient = Patient::query()->create([
            'name' => 'Pasien Antrean Kemarin',
            'age' => 39,
            'address' => 'Jalan Kenanga',
            'symptom' => 'Pemeriksaan rutin',
        ]);
        $laterQueue = Queue::query()->create([
            'patient_id' => $laterPatient->id,
            'queue_number' => 2,
            'position' => 2,
            'queue_date' => today(),
            'status' => Queue::STATUS_WAITING,
        ]);
        $nextQueue = Queue::query()->create([
            'patient_id' => $nextPatient->id,
            'queue_number' => 1,
            'position' => 1,
            'queue_date' => today(),
            'status' => Queue::STATUS_WAITING,
        ]);
        $yesterdayQueue = Queue::query()->create([
            'patient_id' => $yesterdayPatient->id,
            'queue_number' => 1,
            'position' => 1,
            'queue_date' => today()->subDay(),
            'status' => Queue::STATUS_WAITING,
        ]);

        $response = $this->actingAs($user)->post(route('queues.call-next'));

        $response->assertRedirectToRoute('dashboard')
            ->assertSessionHas('status', 'Antrean nomor 001 berhasil dipanggil.');
        $this->assertDatabaseHas('queues', [
            'id' => $nextQueue->id,
            'status' => Queue::STATUS_CALLED,
        ]);
        $this->assertDatabaseHas('queues', [
            'id' => $laterQueue->id,
            'status' => Queue::STATUS_WAITING,
        ]);
        $this->assertDatabaseHas('queues', [
            'id' => $yesterdayQueue->id,
            'status' => Queue::STATUS_WAITING,
        ]);
        $this->assertDatabaseHas('queue_logs', [
            'queue_id' => $nextQueue->id,
            'action' => 'called',
            'by_user_id' => $user->id,
        ]);
        $this->assertSame(now()->toDateTimeString(), $nextQueue->fresh()->called_at?->toDateTimeString());
        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Antrean nomor 001 berhasil dipanggil.');
    }

    public function test_calling_next_queue_shows_an_error_when_no_queue_is_waiting(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('queues.call-next'));

        $response->assertRedirectToRoute('dashboard')
            ->assertSessionHas('error', 'Tidak ada antrean yang menunggu untuk dipanggil.');
        $this->assertDatabaseCount('queue_logs', 0);
        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Tidak ada antrean yang menunggu untuk dipanggil.');
    }

    public function test_guest_is_redirected_to_login_when_calling_next_queue(): void
    {
        $response = $this->post(route('queues.call-next'));

        $response->assertRedirectToRoute('login');
        $this->assertDatabaseCount('queue_logs', 0);
    }

    public function test_petugas_completes_the_called_queue_and_logs_the_action(): void
    {
        $this->travelTo(today()->setTime(11, 45));

        $user = User::factory()->create();
        $patient = Patient::query()->create([
            'name' => 'Pasien Sedang Diperiksa',
            'age' => 46,
            'address' => 'Jalan Melati',
            'symptom' => 'Pemeriksaan rutin',
        ]);
        $queue = Queue::query()->create([
            'patient_id' => $patient->id,
            'queue_number' => 7,
            'position' => 7,
            'queue_date' => today(),
            'status' => Queue::STATUS_CALLED,
            'called_at' => now()->subMinutes(15),
        ]);

        $response = $this->actingAs($user)->post(route('queues.complete', $queue));

        $response->assertRedirectToRoute('dashboard')
            ->assertSessionHas('status', 'Pemeriksaan antrean nomor 007 selesai.');
        $this->assertDatabaseHas('queues', [
            'id' => $queue->id,
            'status' => Queue::STATUS_COMPLETED,
        ]);
        $this->assertDatabaseHas('queue_logs', [
            'queue_id' => $queue->id,
            'action' => 'completed',
            'by_user_id' => $user->id,
        ]);
        $this->assertSame(now()->toDateTimeString(), $queue->fresh()->completed_at?->toDateTimeString());
        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Pemeriksaan antrean nomor 007 selesai.')
            ->assertDontSee('Selesaikan pemeriksaan');
    }

    public function test_petugas_cannot_complete_a_queue_that_is_not_being_served(): void
    {
        $user = User::factory()->create();
        $patient = Patient::query()->create([
            'name' => 'Pasien Menunggu',
            'age' => 33,
            'address' => 'Jalan Mawar',
            'symptom' => 'Kontrol kesehatan',
        ]);
        $queue = Queue::query()->create([
            'patient_id' => $patient->id,
            'queue_number' => 8,
            'position' => 8,
            'queue_date' => today(),
            'status' => Queue::STATUS_WAITING,
        ]);

        $response = $this->actingAs($user)->post(route('queues.complete', $queue));

        $response->assertRedirectToRoute('dashboard')
            ->assertSessionHas('error', 'Antrean tidak dapat diselesaikan karena tidak sedang dilayani hari ini.');
        $this->assertDatabaseHas('queues', [
            'id' => $queue->id,
            'status' => Queue::STATUS_WAITING,
            'completed_at' => null,
        ]);
        $this->assertDatabaseCount('queue_logs', 0);
    }

    public function test_petugas_cannot_complete_a_queue_from_another_day(): void
    {
        $user = User::factory()->create();
        $patient = Patient::query()->create([
            'name' => 'Pasien Kemarin',
            'age' => 51,
            'address' => 'Jalan Kenanga',
            'symptom' => 'Pemeriksaan rutin',
        ]);
        $queue = Queue::query()->create([
            'patient_id' => $patient->id,
            'queue_number' => 10,
            'position' => 10,
            'queue_date' => today()->subDay(),
            'status' => Queue::STATUS_CALLED,
        ]);

        $response = $this->actingAs($user)->post(route('queues.complete', $queue));

        $response->assertRedirectToRoute('dashboard')
            ->assertSessionHas('error', 'Antrean tidak dapat diselesaikan karena tidak sedang dilayani hari ini.');
        $this->assertDatabaseHas('queues', [
            'id' => $queue->id,
            'status' => Queue::STATUS_CALLED,
            'completed_at' => null,
        ]);
        $this->assertDatabaseCount('queue_logs', 0);
    }

    public function test_guest_is_redirected_to_login_when_completing_a_queue(): void
    {
        $patient = Patient::query()->create([
            'name' => 'Pasien Sedang Diperiksa',
            'age' => 46,
            'address' => 'Jalan Melati',
            'symptom' => 'Pemeriksaan rutin',
        ]);
        $queue = Queue::query()->create([
            'patient_id' => $patient->id,
            'queue_number' => 9,
            'position' => 9,
            'queue_date' => today(),
            'status' => Queue::STATUS_CALLED,
        ]);

        $response = $this->post(route('queues.complete', $queue));

        $response->assertRedirectToRoute('login');
        $this->assertDatabaseHas('queues', [
            'id' => $queue->id,
            'status' => Queue::STATUS_CALLED,
        ]);
        $this->assertDatabaseCount('queue_logs', 0);
    }

    public function test_petugas_cancels_a_waiting_queue_and_logs_the_action(): void
    {
        $user = User::factory()->create();
        $patient = Patient::query()->create([
            'name' => 'Pasien Batal',
            'age' => 28,
            'address' => 'Jalan Melati',
            'symptom' => 'Pemeriksaan rutin',
        ]);
        $queue = Queue::query()->create([
            'patient_id' => $patient->id,
            'queue_number' => 12,
            'position' => 12,
            'queue_date' => today(),
            'status' => Queue::STATUS_WAITING,
        ]);

        $response = $this->actingAs($user)->post(route('queues.cancel', $queue));

        $response->assertRedirectToRoute('dashboard')
            ->assertSessionHas('status', 'Antrean nomor 012 berhasil dibatalkan.');
        $this->assertDatabaseHas('queues', [
            'id' => $queue->id,
            'status' => Queue::STATUS_CANCELLED,
        ]);
        $this->assertDatabaseHas('queue_logs', [
            'queue_id' => $queue->id,
            'action' => 'cancelled',
            'by_user_id' => $user->id,
        ]);
        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Antrean nomor 012 berhasil dibatalkan.')
            ->assertSee('Dibatalkan');
    }

    public function test_petugas_cannot_cancel_a_queue_that_is_not_waiting_today(): void
    {
        $user = User::factory()->create();
        $patient = Patient::query()->create([
            'name' => 'Pasien Sudah Dipanggil',
            'age' => 36,
            'address' => 'Jalan Mawar',
            'symptom' => 'Kontrol kesehatan',
        ]);
        $queue = Queue::query()->create([
            'patient_id' => $patient->id,
            'queue_number' => 13,
            'position' => 13,
            'queue_date' => today(),
            'status' => Queue::STATUS_CALLED,
        ]);

        $this->actingAs($user)
            ->post(route('queues.cancel', $queue))
            ->assertRedirectToRoute('dashboard')
            ->assertSessionHas('error', 'Antrean tidak dapat dibatalkan karena tidak sedang menunggu hari ini.');

        $this->assertDatabaseHas('queues', [
            'id' => $queue->id,
            'status' => Queue::STATUS_CALLED,
        ]);
        $this->assertDatabaseCount('queue_logs', 0);
    }

    public function test_petugas_cannot_cancel_a_queue_from_another_day(): void
    {
        $user = User::factory()->create();
        $patient = Patient::query()->create([
            'name' => 'Pasien Kemarin',
            'age' => 42,
            'address' => 'Jalan Kenanga',
            'symptom' => 'Pemeriksaan rutin',
        ]);
        $queue = Queue::query()->create([
            'patient_id' => $patient->id,
            'queue_number' => 14,
            'position' => 14,
            'queue_date' => today()->subDay(),
            'status' => Queue::STATUS_WAITING,
        ]);

        $this->actingAs($user)
            ->post(route('queues.cancel', $queue))
            ->assertRedirectToRoute('dashboard')
            ->assertSessionHas('error', 'Antrean tidak dapat dibatalkan karena tidak sedang menunggu hari ini.');

        $this->assertDatabaseHas('queues', [
            'id' => $queue->id,
            'status' => Queue::STATUS_WAITING,
        ]);
        $this->assertDatabaseCount('queue_logs', 0);
    }

    public function test_guest_is_redirected_to_login_when_cancelling_a_queue(): void
    {
        $patient = Patient::query()->create([
            'name' => 'Pasien Menunggu',
            'age' => 32,
            'address' => 'Jalan Anggrek',
            'symptom' => 'Pemeriksaan rutin',
        ]);
        $queue = Queue::query()->create([
            'patient_id' => $patient->id,
            'queue_number' => 15,
            'position' => 15,
            'queue_date' => today(),
            'status' => Queue::STATUS_WAITING,
        ]);

        $this->post(route('queues.cancel', $queue))
            ->assertRedirectToRoute('login');

        $this->assertDatabaseHas('queues', [
            'id' => $queue->id,
            'status' => Queue::STATUS_WAITING,
        ]);
        $this->assertDatabaseCount('queue_logs', 0);
    }
}
