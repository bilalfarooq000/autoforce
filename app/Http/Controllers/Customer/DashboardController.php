<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $vehicles = $request->user()->vehicles()->withCount('inspections', 'invoices')->get();

        return view('customer.dashboard', compact('vehicles'));
    }
}
