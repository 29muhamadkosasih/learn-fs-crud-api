<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Course;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;

class DashboardAnalyticsController extends Controller
{
    public function index(): JsonResponse
    {
        $startDate = Carbon::now()->subDays(6)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $bookCounts = $this->getDailyCounts(Book::class, $startDate, $endDate);
        $productCounts = $this->getDailyCounts(Product::class, $startDate, $endDate);
        $courseCounts = $this->getDailyCounts(Course::class, $startDate, $endDate);
        $userCounts = $this->getDailyCounts(User::class, $startDate, $endDate);

        $weeklyTrend = [];

        foreach (CarbonPeriod::create($startDate->copy()->startOfDay(), $endDate->copy()->startOfDay()) as $date) {
            $dayKey = $date->toDateString();
            $books = $bookCounts[$dayKey] ?? 0;
            $products = $productCounts[$dayKey] ?? 0;
            $courses = $courseCounts[$dayKey] ?? 0;
            $users = $userCounts[$dayKey] ?? 0;

            $weeklyTrend[] = [
                'date' => $dayKey,
                'label' => $date->format('d M'),
                'books' => $books,
                'products' => $products,
                'courses' => $courses,
                'users' => $users,
                'total' => $books + $products + $courses + $users,
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Dashboard analytics berhasil diambil.',
            'data' => [
                'totals' => [
                    'books' => Book::count(),
                    'products' => Product::count(),
                    'courses' => Course::count(),
                    'users' => User::count(),
                ],
                'weekly_trend' => $weeklyTrend,
            ],
        ]);
    }

    private function getDailyCounts(string $modelClass, Carbon $startDate, Carbon $endDate): array
    {
        /** @var Model $modelClass */
        return $modelClass::query()
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('day')
            ->pluck('total', 'day')
            ->toArray();
    }
}
