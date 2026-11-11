<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Queue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OfficerDashboardController extends Controller
{
    public function callNext(Request $request): RedirectResponse
    {
        $queue = DB::transaction(function () use ($request): ?Queue {
            $queue = Queue::query()
                ->whereDate('queue_date', today())
                ->where('status', Queue::STATUS_WAITING)
                ->orderBy('position')
                ->orderBy('id')
                ->lockForUpdate()
                ->first();

            if ($queue === null) {
                return null;
            }

            $queue->update([
                'status' => Queue::STATUS_CALLED,
                'called_at' => now(),
            ]);

            $queue->logs()->create([
                'action' => 'called',
                'by_user_id' => $request->user()->getAuthIdentifier(),
            ]);

            return $queue;
        });

        if ($queue === null) {
            return redirect()->route('dashboard')
                ->with('error', 'Tidak ada antrean yang menunggu untuk dipanggil.');
        }

        return redirect()->route('dashboard')->with(
            'status',
            'Antrean nomor '.str_pad((string) $queue->queue_number, 3, '0', STR_PAD_LEFT).' berhasil dipanggil.',
        );
    }

    public function complete(Request $request, Queue $queue): RedirectResponse
    {
        $completedQueue = DB::transaction(function () use ($request, $queue): ?Queue {
            $queue = Queue::query()
                ->whereKey($queue->getKey())
                ->whereDate('queue_date', today())
                ->where('status', Queue::STATUS_CALLED)
                ->lockForUpdate()
                ->first();

            if ($queue === null) {
                return null;
            }

            $queue->update([
                'status' => Queue::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);

            $queue->logs()->create([
                'action' => 'completed',
                'by_user_id' => $request->user()->getAuthIdentifier(),
            ]);

            return $queue;
        });

        if ($completedQueue === null) {
            return redirect()->route('dashboard')
                ->with('error', 'Antrean tidak dapat diselesaikan karena tidak sedang dilayani hari ini.');
        }

        return redirect()->route('dashboard')->with(
            'status',
            'Pemeriksaan antrean nomor '.str_pad((string) $completedQueue->queue_number, 3, '0', STR_PAD_LEFT).' selesai.',
        );
    }

    public function cancel(Request $request, Queue $queue): RedirectResponse
    {
        $cancelledQueue = DB::transaction(function () use ($request, $queue): ?Queue {
            $queue = Queue::query()
                ->whereKey($queue->getKey())
                ->whereDate('queue_date', today())
                ->where('status', Queue::STATUS_WAITING)
                ->lockForUpdate()
                ->first();

            if ($queue === null) {
                return null;
            }

            $queue->update([
                'status' => Queue::STATUS_CANCELLED,
            ]);

            $queue->logs()->create([
                'action' => 'cancelled',
                'by_user_id' => $request->user()->getAuthIdentifier(),
            ]);

            return $queue;
        });

        if ($cancelledQueue === null) {
            return redirect()->route('dashboard')
                ->with('error', 'Antrean tidak dapat dibatalkan karena tidak sedang menunggu hari ini.');
        }

        return redirect()->route('dashboard')->with(
            'status',
            'Antrean nomor '.str_pad((string) $cancelledQueue->queue_number, 3, '0', STR_PAD_LEFT).' berhasil dibatalkan.',
        );
    }

    public function index(Request $request): View
    {
        $today = today();
        $queueCounts = Queue::query()
            ->whereDate('queue_date', $today)
            ->select('status')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(static fn (int|string $total): int => (int) $total);

        return view('officer.dashboard', [
            'user' => $request->user(),
            'today' => $today,
            'totalQueueCount' => $queueCounts->sum(),
            'waitingQueueCount' => $queueCounts->get(Queue::STATUS_WAITING, 0),
            'calledQueueCount' => $queueCounts->get(Queue::STATUS_CALLED, 0),
            'completedQueueCount' => $queueCounts->get(Queue::STATUS_COMPLETED, 0),
            'activeQueueCount' => $queueCounts->get(Queue::STATUS_WAITING, 0)
                + $queueCounts->get(Queue::STATUS_CALLED, 0)
                + $queueCounts->get(Queue::STATUS_COMPLETED, 0),
            'currentQueue' => Queue::query()
                ->with([
                    'patient:id,name,age',
                    'doctor:id,full_name,title',
                ])
                ->whereDate('queue_date', $today)
                ->where('status', Queue::STATUS_CALLED)
                ->latest('called_at')
                ->latest('id')
                ->first(['id', 'patient_id', 'doctor_id', 'queue_number', 'position', 'status', 'called_at']),
            'queues' => Queue::query()
                ->with([
                    'patient:id,name,age',
                    'doctor:id,full_name,title',
                ])
                ->whereDate('queue_date', $today)
                ->orderBy('position')
                ->limit(8)
                ->get(['id', 'patient_id', 'doctor_id', 'queue_number', 'position', 'status', 'created_at']),
        ]);
    }
}
