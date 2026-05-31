<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoginHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminLoginHistoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = LoginHistory::query()->orderByDesc('created_at');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        // paginate to avoid huge responses
        $histories = $query->paginate(30);

        return response()->json($histories);
    }
}
