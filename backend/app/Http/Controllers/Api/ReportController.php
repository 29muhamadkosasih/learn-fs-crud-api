<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Course;
use App\Models\Product;
use App\Models\User;

class ReportController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'Reports berhasil diambil.',
            'data' => [
                'totals' => [
                    'books' => Book::count(),
                    'products' => Product::count(),
                    'courses' => Course::count(),
                    'users' => User::count(),
                ],
                'generated_at' => now()->toDateTimeString(),
            ],
        ]);
    }
}
