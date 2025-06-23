<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Exports\RequestExport;
use App\Models\ProjectRequest;
use App\Models\MaintenanceRequest;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class RequestExportController extends Controller
{
    public function exportExcel($type, $id)
    {
        $filename = $type === 'project' ? 'project_request_' : 'maintenance_request_';
        $filename .= $id . '_' . date('YmdHis') . '.xlsx';

        return Excel::download(new RequestExport($type, $id), $filename);
    }

    public function exportPDF($type, $id)
    {
        if ($type === 'project') {
            $request = ProjectRequest::with(['proposer', 'customer', 'items'])->findOrFail($id);
            $view = 'exports.project';
            $filename = 'project_request_';
        } else {
            $request = MaintenanceRequest::with(['proposer', 'customer', 'products', 'staff'])->findOrFail($id);
            $view = 'exports.maintenance';
            $filename = 'maintenance_request_';
        }

        $filename .= $id . '_' . date('YmdHis') . '.pdf';

        $pdf = PDF::loadView($view, ['request' => $request])
            ->setPaper('a4')
            ->setWarnings(false)
            ->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'isFontSubsettingEnabled' => true,
                'isPhpEnabled' => true,
                'isJavascriptEnabled' => true,
                'chroot' => public_path(),
            ]);

        return $pdf->download($filename);
    }
} 