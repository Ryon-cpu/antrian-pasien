<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DoctorController extends Controller
{
    public function index(): View
    {
        return view('officer.doctors.index', [
            'doctors' => Doctor::query()
                ->withCount('queues')
                ->orderBy('full_name')
                ->paginate(12),
        ]);
    }

    public function create(): View
    {
        return view('officer.doctors.form', [
            'doctor' => new Doctor,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Doctor::query()->create($this->validatedDoctorData($request));

        return redirect()
            ->route('doctors.index')
            ->with('status', 'Data dokter berhasil ditambahkan.');
    }

    public function edit(Doctor $doctor): View
    {
        return view('officer.doctors.form', [
            'doctor' => $doctor,
        ]);
    }

    public function update(Request $request, Doctor $doctor): RedirectResponse
    {
        $doctor->update($this->validatedDoctorData($request));

        return redirect()
            ->route('doctors.index')
            ->with('status', 'Data dokter berhasil diperbarui.');
    }

    /**
     * @return array{
     *     full_name: string,
     *     title: ?string,
     *     specialization: ?string,
     *     education: ?string,
     *     experience: ?string,
     *     schedule: ?string
     * }
     */
    private function validatedDoctorData(Request $request): array
    {
        return $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:50'],
            'specialization' => ['nullable', 'string', 'max:100'],
            'education' => ['nullable', 'string'],
            'experience' => ['nullable', 'string'],
            'schedule' => ['nullable', 'string'],
        ]);
    }
}
