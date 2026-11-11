<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Queue;
use Illuminate\View\View;

class QueueDisplayController extends Controller
{
    public function index(): View
    {
        $today = today();
        $todayQueues = Queue::query()->whereDate('queue_date', $today);

        return view('queues.display', [
            'today' => $today,
            'currentQueue' => (clone $todayQueues)
                ->where('status', Queue::STATUS_CALLED)
                ->latest('called_at')
                ->latest('id')
                ->first(['id', 'queue_number', 'called_at']),
            'nextQueues' => (clone $todayQueues)
                ->where('status', Queue::STATUS_WAITING)
                ->orderBy('position')
                ->orderBy('id')
                ->limit(5)
                ->get(['id', 'queue_number', 'position']),
            'waitingQueueCount' => (clone $todayQueues)
                ->where('status', Queue::STATUS_WAITING)
                ->count(),
        ]);
    }
}
