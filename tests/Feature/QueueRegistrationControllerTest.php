<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QueueRegistrationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_can_open_the_queue_registration_form_without_logging_in(): void
    {
        Doctor::query()->create([
            'full_name' => 'Maya Putri',
            'title' => 'dr.',
            'specialization' => 'Dokter Umum',
        ]);

        $this->get(route('queues.create'))
            ->assertOk()
            ->assertViewIs('queues.create')
            ->assertSee('Ambil nomor antrean')
            ->assertSee('Dokter pilihan')
            ->assertSee('dr. Maya Putri')
            ->assertSee('Dokter Umum');
    }

    public function test_patient_can_register_and_view_their_queue_ticket(): void
    {
        $this->travelTo(today()->setTime(8, 15));

        $response = $this->post(route('queues.store'), [
            'name' => 'Siti Aminah',
            'age' => 28,
            'address' => 'Jalan Melati',
            'symptom' => 'Pemeriksaan rutin',
        ]);

        $patient = Patient::query()->where('name', 'Siti Aminah')->firstOrFail();
        $queue = $patient->queue()->firstOrFail();

        $response->assertRedirectToRoute('queues.receipt', $patient->unique_code);
        $this->assertDatabaseHas('patients', [
            'id' => $patient->id,
            'name' => 'Siti Aminah',
            'age' => 28,
        ]);
        $this->assertDatabaseHas('queues', [
            'id' => $queue->id,
            'queue_number' => 1,
            'position' => 1,
            'status' => Queue::STATUS_WAITING,
        ]);
        $this->assertSame(today()->toDateString(), $queue->queue_date->toDateString());
        $this->assertDatabaseHas('queue_logs', [
            'queue_id' => $queue->id,
            'action' => 'registered',
            'by_user_id' => null,
        ]);

        $this->get(route('queues.receipt', $patient->unique_code))
            ->assertOk()
            ->assertViewIs('queues.receipt')
            ->assertViewHas('waitingPosition', 1)
            ->assertSee('001')
            ->assertSee('Siti Aminah')
            ->assertSee('Menunggu dipanggil')
            ->assertDontSee('Dokter pilihan')
            ->assertSee('content="10"', false)
            ->assertSee('Anda berada di urutan berikutnya.');
    }

    public function test_patient_can_register_with_an_optional_doctor_selection(): void
    {
        $this->travelTo(today()->setTime(8, 15));
        $doctor = Doctor::query()->create([
            'full_name' => 'Maya Putri',
            'title' => 'dr.',
            'specialization' => 'Dokter Umum',
        ]);

        $response = $this->post(route('queues.store'), [
            'name' => 'Siti Aminah',
            'age' => 28,
            'address' => 'Jalan Melati',
            'symptom' => 'Pemeriksaan rutin',
            'doctor_id' => $doctor->id,
        ]);

        $patient = Patient::query()->where('name', 'Siti Aminah')->firstOrFail();
        $queue = $patient->queue()->firstOrFail();

        $response->assertRedirectToRoute('queues.receipt', $patient->unique_code);
        $this->assertDatabaseHas('queues', [
            'id' => $queue->id,
            'doctor_id' => $doctor->id,
            'status' => Queue::STATUS_WAITING,
        ]);

        $this->get(route('queues.receipt', $patient->unique_code))
            ->assertOk()
            ->assertSee('Dokter pilihan')
            ->assertSee('dr. Maya Putri')
            ->assertSee('Dokter Umum');
    }

    public function test_ticket_position_counts_only_waiting_queues_ahead_of_the_patient(): void
    {
        $this->travelTo(today()->setTime(8, 15));

        $patientAhead = Patient::query()->create([
            'name' => 'Pasien Menunggu Depan',
            'age' => 37,
            'address' => 'Jalan Mawar',
            'symptom' => 'Kontrol kesehatan',
        ]);
        $calledPatient = Patient::query()->create([
            'name' => 'Pasien Sedang Dilayani',
            'age' => 41,
            'address' => 'Jalan Kenanga',
            'symptom' => 'Pemeriksaan rutin',
        ]);
        $ticketPatient = Patient::query()->create([
            'name' => 'Pemilik Tiket',
            'age' => 30,
            'address' => 'Jalan Anggrek',
            'symptom' => 'Sakit kepala',
            'unique_code' => str_repeat('a', 32),
        ]);
        $completedPatient = Patient::query()->create([
            'name' => 'Pasien Sudah Selesai',
            'age' => 44,
            'address' => 'Jalan Teratai',
            'symptom' => 'Pemeriksaan rutin',
        ]);

        $patientAhead->queue()->create([
            'queue_number' => 1,
            'position' => 1,
            'queue_date' => today(),
            'status' => Queue::STATUS_WAITING,
        ]);
        $calledPatient->queue()->create([
            'queue_number' => 2,
            'position' => 2,
            'queue_date' => today(),
            'status' => Queue::STATUS_CALLED,
        ]);
        $ticketPatient->queue()->create([
            'queue_number' => 3,
            'position' => 3,
            'queue_date' => today(),
            'status' => Queue::STATUS_WAITING,
        ]);
        $completedPatient->queue()->create([
            'queue_number' => 4,
            'position' => 4,
            'queue_date' => today(),
            'status' => Queue::STATUS_COMPLETED,
        ]);

        $this->get(route('queues.receipt', $ticketPatient->unique_code))
            ->assertOk()
            ->assertViewHas('waitingPosition', 2)
            ->assertSee('Ada 1 antrean menunggu di depan Anda.');
    }

    public function test_ticket_reflects_live_queue_status_and_stops_refreshing_when_finished(): void
    {
        $patient = Patient::query()->create([
            'name' => 'Pasien Tiket Aktif',
            'age' => 33,
            'address' => 'Jalan Melati',
            'symptom' => 'Pemeriksaan rutin',
            'unique_code' => str_repeat('b', 32),
        ]);
        $queue = $patient->queue()->create([
            'queue_number' => 7,
            'position' => 7,
            'queue_date' => today(),
            'status' => Queue::STATUS_WAITING,
        ]);

        $this->get(route('queues.receipt', $patient->unique_code))
            ->assertOk()
            ->assertSee('content="10"', false)
            ->assertSee('Posisi antrean Anda');

        $queue->update([
            'status' => Queue::STATUS_CALLED,
            'called_at' => now(),
        ]);

        $this->get(route('queues.receipt', $patient->unique_code))
            ->assertOk()
            ->assertSee('Sedang dilayani')
            ->assertSee('Nomor Anda sedang dipanggil. Silakan menuju petugas.')
            ->assertDontSee('Posisi antrean Anda')
            ->assertSee('content="10"', false);

        $queue->update([
            'status' => Queue::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        $this->get(route('queues.receipt', $patient->unique_code))
            ->assertOk()
            ->assertSee('Pemeriksaan Anda telah selesai.')
            ->assertDontSee('content="10"', false);
    }

    public function test_ticket_numbers_continue_after_today_queues_and_ignore_other_dates(): void
    {
        $this->travelTo(today()->setTime(8, 15));

        $todayPatient = Patient::query()->create([
            'name' => 'Pasien Sebelumnya',
            'age' => 37,
            'address' => 'Jalan Mawar',
            'symptom' => 'Kontrol kesehatan',
        ]);
        $yesterdayPatient = Patient::query()->create([
            'name' => 'Pasien Kemarin',
            'age' => 41,
            'address' => 'Jalan Kenanga',
            'symptom' => 'Pemeriksaan rutin',
        ]);
        $todayPatient->queue()->create([
            'queue_number' => 5,
            'position' => 12,
            'queue_date' => today(),
            'status' => Queue::STATUS_COMPLETED,
        ]);
        $yesterdayPatient->queue()->create([
            'queue_number' => 99,
            'position' => 99,
            'queue_date' => today()->subDay(),
            'status' => Queue::STATUS_WAITING,
        ]);

        $this->post(route('queues.store'), [
            'name' => 'Pasien Baru',
            'age' => 30,
            'address' => 'Jalan Anggrek',
            'symptom' => 'Sakit kepala',
        ])->assertRedirect();

        $queue = Patient::query()->where('name', 'Pasien Baru')->firstOrFail()->queue()->firstOrFail();

        $this->assertSame(6, $queue->queue_number);
        $this->assertSame(13, $queue->position);
        $this->assertSame(today()->toDateString(), $queue->queue_date->toDateString());
    }

    public function test_invalid_patient_data_is_rejected_without_creating_records(): void
    {
        $response = $this->from(route('queues.create'))->post(route('queues.store'), [
            'name' => '',
            'age' => 256,
            'address' => '',
            'symptom' => '',
        ]);

        $response->assertRedirectToRoute('queues.create')
            ->assertSessionHasErrors(['name', 'age', 'address', 'symptom']);
        $this->assertDatabaseCount('patients', 0);
        $this->assertDatabaseCount('queues', 0);
        $this->assertDatabaseCount('queue_logs', 0);
    }

    public function test_unknown_doctor_selection_is_rejected_without_creating_records(): void
    {
        $response = $this->from(route('queues.create'))->post(route('queues.store'), [
            'name' => 'Siti Aminah',
            'age' => 28,
            'address' => 'Jalan Melati',
            'symptom' => 'Pemeriksaan rutin',
            'doctor_id' => 999,
        ]);

        $response->assertRedirectToRoute('queues.create')
            ->assertSessionHasErrors('doctor_id');
        $this->assertDatabaseCount('patients', 0);
        $this->assertDatabaseCount('queues', 0);
        $this->assertDatabaseCount('queue_logs', 0);
    }

    public function test_unknown_ticket_code_returns_not_found(): void
    {
        $this->get(route('queues.receipt', str_repeat('a', 32)))
            ->assertNotFound();
    }
}
