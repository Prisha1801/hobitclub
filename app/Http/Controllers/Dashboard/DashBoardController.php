<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Booking;
use Carbon\Carbon;
use DB;
class DashBoardController extends Controller
{
    // public function overview(Request $request)
    // {
    //     $days = $request->get('days', 7);
    //     $from = Carbon::now()->subDays($days);

    //     return [
    //         'Overview' => $this->data($from),
    //         // 'revenue_chart' => $this->revenueChart($from),
    //         // 'booking_stats' => $this->bookingStats($from),
    //         // 'recent_transactions' => $this->recentTransactions()
    //     ];        
    // }

    public function overview(Request $request)
    {
        $filter = $request->filter;

        if ($request->filled(['from_date', 'to_date'])) {
            $fromDate = Carbon::parse($request->from_date)->startOfDay();
            $toDate   = Carbon::parse($request->to_date)->endOfDay();
        } else {
            switch ($filter) {
                case 'day':
                    $fromDate = Carbon::today();
                    $toDate   = Carbon::today()->endOfDay();
                    break;

                case 'week':
                    $fromDate = Carbon::now()->startOfWeek();
                    $toDate   = Carbon::now()->endOfWeek();
                    break;

                case 'year':
                    $fromDate = Carbon::now()->startOfYear();
                    $toDate   = Carbon::now()->endOfYear();
                    break;

                case 'month':
                default:
                    $fromDate = Carbon::now()->startOfMonth();
                    $toDate   = Carbon::now()->endOfMonth();
                    break;
            }
        }

        /* -------------------------
        | PREVIOUS PERIOD
        |-------------------------*/
        $periodDays = $fromDate->diffInDays($toDate) + 1;

        $previousFrom = (clone $fromDate)->subDays($periodDays);
        $previousTo   = (clone $fromDate)->subSecond();

        /* -------------------------
        | USERS
        |-------------------------*/
        $currentUsers = User::where('is_active', 1)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->count();

        $previousUsers = User::where('is_active', 1)
            ->whereBetween('created_at', [$previousFrom, $previousTo])
            ->count();

        $userPercentage = $previousUsers > 0
            ? round((($currentUsers - $previousUsers) / $previousUsers) * 100, 1)
            : 100;

        /* -------------------------
        | BOOKINGS
        |-------------------------*/
        $currentBookings = Booking::whereBetween('created_at', [$fromDate, $toDate])->count();

        $previousBookings = Booking::whereBetween('created_at', [$previousFrom, $previousTo])->count();

        $bookingPercentage = $previousBookings > 0
            ? round((($currentBookings - $previousBookings) / $previousBookings) * 100, 1)
            : 100;

        /* -------------------------
        | REVENUE
        |-------------------------*/
        $currentRevenue = Booking::whereIn('status', ['completed', 'confirmed'])
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->sum('amount');

        $previousRevenue = Booking::whereIn('status', ['completed', 'confirmed'])
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$previousFrom, $previousTo])
            ->sum('amount');

        $revenuePercentage = $previousRevenue > 0
            ? round((($currentRevenue - $previousRevenue) / $previousRevenue) * 100, 1)
            : 100;
        
        /* -------------------------
        | GROWTH RATE (Bookings %)
        |-------------------------*/

        // current period growth
        $currentGrowth = $previousBookings > 0
            ? round((($currentBookings - $previousBookings) / $previousBookings) * 100, 1)
            : 0;

        // previous period bookings (one more step back)
        $prevPrevFrom = (clone $previousFrom)->subDays($periodDays);
        $prevPrevTo   = (clone $previousFrom)->subSecond();

        $prevPrevBookings = Booking::whereBetween('created_at', [$prevPrevFrom, $prevPrevTo])->count();

        // previous period growth
        $previousGrowth = $prevPrevBookings > 0
            ? round((($previousBookings - $prevPrevBookings) / $prevPrevBookings) * 100, 1)
            : 0;

        // growth badge percentage (vs last month)
        $growthPercentage = $previousGrowth != 0
            ? round((($currentGrowth - $previousGrowth) / abs($previousGrowth)) * 100, 1)
            : 100;
            /* -------------------------
        | REVENUE CHART
        |-------------------------*/
        
        $revenueChart = Booking::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(amount) as total')
            )
            ->whereIn('status', ['completed', 'confirmed'])
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        /* -------------------------
        | BOOKING CHART
        |-------------------------*/
        $bookingChart = Booking::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total')
            )
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'filter_applied' => $filter ?? 'month',
            'date_range' => [
                'from' => $fromDate->toDateString(),
                'to'   => $toDate->toDateString(),
            ],
            'cards' => [
                'users' => [
                    'total' => $currentUsers,
                    'percentage' => $userPercentage,
                    'trend' => $userPercentage >= 0 ? 'up' : 'down',
                ],
                'bookings' => [
                    'total' => $currentBookings,
                    'percentage' => $bookingPercentage,
                    'trend' => $bookingPercentage >= 0 ? 'up' : 'down',
                ],
                'revenue' => [
                    'total' => $currentRevenue,
                    'percentage' => $revenuePercentage,
                    'trend' => $revenuePercentage >= 0 ? 'up' : 'down',
                ],
                'groth' => [
                    'total' => $currentGrowth,
                    'percentage' => $growthPercentage,
                    'trend' => $growthPercentage >= 0 ? 'up' : 'down',
                ],
            ],
            'revenue_chart' => $revenueChart,
            'booking_chart' => $bookingChart,
        ]);
    }
}
