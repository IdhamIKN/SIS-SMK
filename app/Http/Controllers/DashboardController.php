<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        Log::channel('sis')->info('[Dashboard] Access', [
            'user_id' => $request->user()->id,
            'role' => $request->user()->getRoleNames()->first(),
        ]);

        return view('dashboard');
    }
}
