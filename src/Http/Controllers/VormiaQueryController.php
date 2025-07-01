<?php

namespace VormiaQueryPhp\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use VormiaQueryPhp\Services\VormiaResponseService;

class VormiaQueryController extends Controller
{
    public function loadData(Request $request)
    {
        // Example: fetch data from database or service
        $data = [
            ['id' => 1, 'name' => 'Alpha'],
            ['id' => 2, 'name' => 'Beta'],
        ];

        $response = VormiaResponseService::format($data);
        return response()->json($response);
    }
}
