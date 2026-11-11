<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Queue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class QueueRegistrationController extends Controller
{
    public function create(): View
    {
        return view('queues.create', [
            'doctors' => Doctor::query()
                ->orderBy('full_name')
                ->get(['id', 'full_name', 'title', 'specialization']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'age' => ['required', 'integer', 'min:0', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'symptom' => ['required', 'string', 'max:5000'],
            'doctor_id' => ['nullable', 'integer', 'exists:doctors,id'],
        ]);
        $today = today();

        $queue = Cache::lock('queue-registration:'.$today->toDateString(), 30)
            ->block(10, function () use ($validated, $today): Queue {
                return DB::transaction(function () use ($validated, $today): Queue {
                    $queueNumber = (int) Queue::query()
                        ->whereDate('queue_date', $today)
                        ->max('queue_number') + 1;
                    $position = (int) Queue::query()
                        ->whereDate('queue_date', $today)
                        ->max('position') + 1;

                    $patient = Patient::query()->create([
                        'name' => $validated['name'],
                        'age' => $validated['age'],
                        'address' => $validated['address'],
                        'symptom' => $validated['symptom'],
                        'unique_code' => Str::random(32),
                    ]);
                    $queue = $patient->queue()->create([
                        'doctor_id' => $validated['doctor_id'] ?? null,
                        'queue_number' => $queueNumber,
                        'position' => $position,
                        'queue_date' => $today,
                        'status' => Queue::STATUS_WAITING,
                    ]);
                    $queue->logs()->create([
                        'action' => 'registered',
                    ]);

                    return $queue;
                });
            });

        return redirect()->route('queues.receipt', $queue->patient->unique_code);
    }

    public function show(string $uniqueCode): View
    {
        $patient = Patient::query()
            ->where('unique_code', $uniqueCode)
            ->with('queue.doctor')
            ->firstOrFail();
        $queue = $patient->queue;

        if ($queue === null) {
            abort(404);
        }

        $waitingPosition = null;

        if ($queue->status === Queue::STATUS_WAITING) {
            $waitingPosition = Queue::query()
                ->whereDate('queue_date', $queue->queue_date)
                ->where('status', Queue::STATUS_WAITING)
                ->where(function (Builder $query) use ($queue): void {
                    $query->where('position', '<', $queue->position)
                        ->orWhere(function (Builder $query) use ($queue): void {
                            $query->where('position', $queue->position)
                                ->where('id', '<', $queue->id);
                        });
                })
                ->count() + 1;
        }

        return view('queues.receipt', [
            'patient' => $patient,
            'queue' => $queue,
            'waitingPosition' => $waitingPosition,
        ]);
    }
}
