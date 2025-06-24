<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;
use App\Models\ProjectRequest;
use App\Models\MaintenanceRequest;
use App\Models\CustomerMaintenanceRequest;

class RequestExport implements FromView
{
    protected $request;
    protected $type;

    public function __construct($type, $id)
    {
        $this->type = $type;
        if ($type === 'project') {
            $this->request = ProjectRequest::with(['proposer', 'customer', 'items'])->findOrFail($id);
        } elseif ($type === 'maintenance') {
            $this->request = MaintenanceRequest::with(['proposer', 'customer', 'products', 'staff'])->findOrFail($id);
        } elseif ($type === 'customer-maintenance') {
            $this->request = CustomerMaintenanceRequest::with(['customer', 'approvedByUser'])->findOrFail($id);
        } else {
            throw new \InvalidArgumentException("Unsupported request type: {$type}");
        }
    }

    public function view(): View
    {
        if ($this->type === 'project') {
            return view('exports.project', [
                'request' => $this->request
            ]);
        } elseif ($this->type === 'maintenance') {
            return view('exports.maintenance', [
                'request' => $this->request
            ]);
        } elseif ($this->type === 'customer-maintenance') {
            return view('exports.customer-maintenance', [
                'request' => $this->request
            ]);
        } else {
            throw new \InvalidArgumentException("Unsupported request type: {$this->type}");
        }
    }
} 