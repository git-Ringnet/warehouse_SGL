<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CustomerMaintenanceRequest;
use Illuminate\Support\Facades\Log;

class CustomerDashboardController extends Controller
{
    public function index()
    {
        $customer = Auth::guard('customer')->user();
        
        // Debug customer authentication
        Log::info('Customer User:', [
            'customer' => $customer,
            'customer_id' => $customer->customer_id ?? null
        ]);
        
        // Lấy tất cả danh sách phiếu yêu cầu bảo trì của khách hàng
        $maintenanceRequests = CustomerMaintenanceRequest::where('customer_id', $customer->customer_id)
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Debug maintenance requests
        Log::info('Maintenance Requests:', [
            'count' => $maintenanceRequests->count(),
            'requests' => $maintenanceRequests->toArray()
        ]);
            
        return view('customer.dashboard', compact('customer', 'maintenanceRequests'));
    }
} 