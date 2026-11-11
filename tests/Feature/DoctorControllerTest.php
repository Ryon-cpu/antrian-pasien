<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Queue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_doctor_management(): void
    {
        $this->get(route('doctors.index'))
            ->assertRedirectToRoute('login');

        $this->get(route('doctors.create'))
            ->assertRedirectToRoute('login');
    }

    public function test_doctor_list_shows_profiles_and_queue_counts(): void
    {
        $user = User::factory()->create();
        $doctor = Doctor::query()->create([
            'full_name' => 'Maya <script>alert(1)</script>',
            'title' => 'dr.',
            'specialization' => 'Dokter Umum',
            'schedule' => '<script>alert(1)</script>',
        ]);
        $patient = Patient::query()->create([
            'name' => 'Pasien Klinik',
            'age' => 28,
            'address' => 'Jalan Melati',
            'symptom' => 'Pemeriksaan rutin',
        ]);
        Queue::query()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'queue_number' => 1,
            'position' => 1,
            'queue_date' => today(),
            'status' => Queue::STATUS_WAITING,
        ]);

        $this->actingAs($user)
            ->get(route('doctors.index'))
            ->assertOk()
            ->assertViewIs('officer.doctors.index')
            ->assertViewHas('doctors', fn ($doctors): bool => $doctors->first()->queues_count === 1)
            ->assertSee('dr. Maya &lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('Dokter Umum')
            ->assertSee('Tambah dokter')
            ->assertSee('Dokter');
    }

    public function test_doctor_can_be_created_with_validated_profile_data(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('doctors.store'), [
                'full_name' => 'Rina Lestari',
                'title' => 'dr.',
                'specialization' => 'Dokter Anak',
                'education' => 'Universitas Indonesia',
                'experience' => 'Pengalaman klinik 8 tahun',
                'schedule' => 'Selasa dan Kamis, 09.00-13.00',
                'photo_path' => 'arbitrary/path.jpg',
            ])
            ->assertRedirectToRoute('doctors.index')
            ->assertSessionHas('status', 'Data dokter berhasil ditambahkan.');

        $this->assertDatabaseHas('doctors', [
            'full_name' => 'Rina Lestari',
            'title' => 'dr.',
            'specialization' => 'Dokter Anak',
            'education' => 'Universitas Indonesia',
            'experience' => 'Pengalaman klinik 8 tahun',
            'schedule' => 'Selasa dan Kamis, 09.00-13.00',
            'photo_path' => null,
        ]);
    }

    public function test_doctor_name_is_required_and_invalid_data_is_not_saved(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('doctors.create'))
            ->post(route('doctors.store'), [
                'full_name' => '',
                'title' => str_repeat('x', 51),
            ])
            ->assertRedirectToRoute('doctors.create')
            ->assertSessionHasErrors(['full_name', 'title']);

        $this->assertDatabaseCount('doctors', 0);
    }

    public function test_doctor_profile_can_be_updated(): void
    {
        $user = User::factory()->create();
        $doctor = Doctor::query()->create([
            'full_name' => 'Maya Putri',
            'specialization' => 'Dokter Umum',
            'schedule' => 'Senin',
        ]);

        $this->actingAs($user)
            ->put(route('doctors.update', $doctor), [
                'full_name' => 'Maya Putri Utami',
                'title' => 'dr.',
                'specialization' => 'Dokter Keluarga',
                'education' => 'Universitas Gadjah Mada',
                'experience' => 'Pengalaman 10 tahun',
                'schedule' => 'Senin dan Rabu',
            ])
            ->assertRedirectToRoute('doctors.index')
            ->assertSessionHas('status', 'Data dokter berhasil diperbarui.');

        $this->assertDatabaseHas('doctors', [
            'id' => $doctor->id,
            'full_name' => 'Maya Putri Utami',
            'specialization' => 'Dokter Keluarga',
            'education' => 'Universitas Gadjah Mada',
            'experience' => 'Pengalaman 10 tahun',
            'schedule' => 'Senin dan Rabu',
        ]);
    }

    public function test_edit_page_returns_not_found_for_unknown_doctor(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('doctors.edit', 999))
            ->assertNotFound();
    }
}
