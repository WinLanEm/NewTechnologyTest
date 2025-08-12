<?php

namespace App\Http\Controllers;

use App\Http\Requests\TopCategoryRequest;
use App\Models\CategoryPosition;


class TopCategoryController extends Controller
{
    public function __invoke(TopCategoryRequest $request)
    {
        $date = $request->get('date');

        $categories = CategoryPosition::selectRaw('category_id, MIN(position) as min_position')
            ->when($date, fn($q) => $q->where('date', $date))
            ->groupBy('category_id')
            ->orderBy('category_id', 'asc')
            ->pluck('min_position', 'category_id');

        return response()->json([
            'status_code' => 200,
            'message' => 'ok',
            'data' => $categories,
        ]);
    }
}
