<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Booking;
use Carbon\Carbon;

class DashBoardController extends Controller
{
    public function overview(Request $request)
    {
        $days = $request->get('days', 7);
        $from = Carbon::now()->subDays($days);

        return [
            'Overview' => $this->data($from),
            // 'revenue_chart' => $this->revenueChart($from),
            // 'booking_stats' => $this->bookingStats($from),
            // 'recent_transactions' => $this->recentTransactions()
        ];        
    }

    private function data($from)
    {
        return [
            'total_users' => [
                'value' => User::count(),
                'change' => $this->percentageChange(
                    User::where('created_at', '>=', $from)->count(),
                    User::whereBetween('created_at', [$from->copy()->subDays(7), $from])->count()
                ). ' %'
            ],
            // 'total_bookings' => [
            //     'value' => Booking::count(),
            //     'change' => $this->percentageChange(
            //         Booking::where('created_at', '>=', $from)->count(),
            //         Booking::whereBetween('created_at', [$from->copy()->subDays(7), $from])->count()
            //     )
            // ],
            // 'total_revenue' => [
            //     'value' => Transaction::sum('amount'),
            //     'change' => $this->percentageChange(
            //         Transaction::where('created_at', '>=', $from)->sum('amount'),
            //         Transaction::whereBetween('created_at', [$from->copy()->subDays(7), $from])->sum('amount')
            //     )
            // ],
            // 'growth_rate' => [
            //     'value' => round(Booking::where('created_at', '>=', $from)->count() / max(Booking::count(),1) * 100, 2)
            // ]
        ];
    }

    // private function revenueChart($from)
    // {
    //     return Transaction::selectRaw('DATE(created_at) as date, SUM(amount) as total')
    //         ->where('created_at', '>=', $from)
    //         ->groupBy('date')
    //         ->orderBy('date')
    //         ->get();
    // }

    // private function bookingStats($from)
    // {
    //     return Booking::selectRaw('MONTH(created_at) as month, COUNT(*) as total')
    //         ->where('created_at', '>=', $from)
    //         ->groupBy('month')
    //         ->orderBy('month')
    //         ->get()
    //         ->map(fn($row) => [
    //             'month' => Carbon::create()->month($row->month)->format('M'),
    //             'total' => $row->total
    //         ]);
    // }

    // private function recentTransactions()
    // {
    //     return Transaction::with('user:id,name')
    //         ->latest()
    //         ->limit(5)
    //         ->get([
    //             'id',
    //             'transaction_id',
    //             'user_id',
    //             'amount',
    //             'status',
    //             'created_at'
    //         ]);
    // }

    private function percentageChange($current, $previous)
    {
        if ($previous == 0) return 100;
        return round((($current - $previous) / $previous) * 100, 2);
    }
}
