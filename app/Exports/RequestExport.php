<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;
use App\Models\ProjectRequest;
use App\Models\MaintenanceRequest;

class RequestExport implements FromView
{
    protected $request;
    protected $type;

    public function __construct($type, $id)
    {
        $this->type = $type;
        if ($type === 'project') {
            $this->request = ProjectRequest::with(['proposer', 'customer', 'items'])->findOrFail($id);
        } else {
            $this->request = MaintenanceRequest::with(['proposer', 'customer', 'products', 'staff'])->findOrFail($id);
        }
    }

    public function view(): View
    {
        if ($this->type === 'project') {
            return view('exports.project', [
                'request' => $this->request
            ]);
        } else {
            return view('exports.maintenance', [
                'request' => $this->request
            ]);
        }
    }
} 