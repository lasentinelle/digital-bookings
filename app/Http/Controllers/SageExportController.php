<?php

namespace App\Http\Controllers;

use App\Http\Requests\SageExportRequest;
use App\Models\Reservation;
use App\ReservationStatus;
use App\Services\SageCsvBuilder;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SageExportController extends Controller
{
    /**
     * Stream a SAGE-formatted CSV for the given date range.
     *
     * Cash exports are not yet supported — the format is still being defined by accounting.
     */
    public function __invoke(SageExportRequest $request): StreamedResponse|RedirectResponse
    {
        $start = Carbon::parse($request->validated('start_date'))->startOfDay();
        $end = Carbon::parse($request->validated('end_date'))->endOfDay();
        $paymentMode = $request->validated('payment_mode');

        if ($paymentMode === 'cash') {
            return redirect()
                ->route('reservations.index')
                ->with('error', 'Cash SAGE export is not yet available — the format is pending from accounting.');
        }

        $reservations = Reservation::query()
            ->with(['client', 'placement', 'salesperson', 'representedClient'])
            ->where('status', ReservationStatus::Confirmed)
            ->get();

        $builder = new SageCsvBuilder($start, $end);
        $rows = $builder->build($reservations);

        $filename = sprintf('sage-export-%s-%s.csv', $start->format('Ymd'), $end->format('Ymd'));

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            foreach ($rows as $row) {
                fputcsv($handle, $row, ';', '"', '\\');
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
