<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Queue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class QueueHistoryController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $validator = Validator::make($request->query(), [
            'date' => ['sometimes', 'required', 'date_format:Y-m-d', 'before_or_equal:today'],
        ], [
            'date.date_format' => 'Format tanggal harus YYYY-MM-DD.',
            'date.before_or_equal' => 'Tanggal riwayat tidak boleh melewati hari ini.',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('queues.history', ['date' => today()->toDateString()])
                ->withErrors($validator);
        }

        $selectedDate = Carbon::parse(
            $validator->validated()['date'] ?? today()->toDateString(),
        );
        $queueCounts = Queue::query()
            ->whereDate('queue_date', $selectedDate)
            ->select('status')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(static fn (int|string $total): int => (int) $total);

        return view('officer.history', [
            'user' => $request->user(),
            'today' => today(),
            'selectedDate' => $selectedDate,
            'totalQueueCount' => $queueCounts->sum(),
            'waitingQueueCount' => $queueCounts->get(Queue::STATUS_WAITING, 0),
            'calledQueueCount' => $queueCounts->get(Queue::STATUS_CALLED, 0),
            'completedQueueCount' => $queueCounts->get(Queue::STATUS_COMPLETED, 0),
            'cancelledQueueCount' => $queueCounts->get(Queue::STATUS_CANCELLED, 0),
            'queues' => Queue::query()
                ->with([
                    'patient:id,name,age',
                    'doctor:id,full_name,title',
                ])
                ->whereDate('queue_date', $selectedDate)
                ->orderBy('position')
                ->orderBy('id')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }
}
