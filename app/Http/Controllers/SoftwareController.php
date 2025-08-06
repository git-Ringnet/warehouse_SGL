<?php

namespace App\Http\Controllers;

use App\Models\Software;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SoftwareController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Software::query();

        // Tìm kiếm
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('version', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }

        // Lọc theo loại file
        if ($request->filled('file_type')) {
            $query->where('file_type', $request->file_type);
        }

        $software = $query->orderBy('created_at', 'desc')->paginate(10);
        
        return view('software.index', compact('software'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('software.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'version' => 'nullable|string|max:50',
            'status' => 'nullable|string|in:active,inactive,beta',
            'software_file' => 'nullable|file|max:512000', // 500MB (tăng lên để phù hợp với file lớn)
            'manual_file' => 'nullable|file|mimes:pdf,doc,docx,txt|max:10240', // 10MB cho tài liệu hướng dẫn
            'platform' => 'nullable|string',
            'release_date' => 'nullable|date',
            'description' => 'nullable|string',
            'changelog' => 'nullable|string',
        ]);
        
        // Debug: Log validated data
        Log::info('Validated data:', $validated);

        try {
            // Debug: Log request data
            Log::info('Request data:', $request->all());
            
            // Tạo dữ liệu cơ bản
            $data = [
                'name' => $request->name,
                'type' => $request->type,
            ];
            
            // Thêm các trường không bắt buộc nếu có giá trị
            if ($request->filled('version')) {
                $data['version'] = $request->version;
            }
            if ($request->filled('release_date')) {
                $data['release_date'] = $request->release_date;
            }
            if ($request->filled('platform')) {
                $data['platform'] = $request->platform;
            }
            if ($request->filled('status')) {
                $data['status'] = $request->status;
            }
            if ($request->filled('description')) {
                $data['description'] = $request->description;
            }
            if ($request->filled('changelog')) {
                $data['changelog'] = $request->changelog;
            }
            
            // Xử lý file phần mềm nếu có
            if ($request->hasFile('software_file')) {
                $file = $request->file('software_file');
                $originalFileName = $file->getClientOriginalName();
                $fileType = strtolower($file->getClientOriginalExtension());
                $fileSize = $this->formatSizeUnits($file->getSize());
                
                // Tạo tên file duy nhất
                $fileName = Str::slug($request->name) . '-' . ($request->version ?? 'unknown') . '-' . time() . '.' . $fileType;
                
                // Lưu file vào storage/app/public/software
                $filePath = $file->storeAs('software', $fileName, 'public');
                
                // Thêm thông tin file vào dữ liệu
                $data['file_path'] = $filePath;
                $data['file_name'] = $originalFileName;
                $data['file_size'] = $fileSize;
                $data['file_type'] = $fileType;
            }
            
            // Xử lý tài liệu hướng dẫn nếu có
            if ($request->hasFile('manual_file')) {
                $manualFile = $request->file('manual_file');
                $originalManualFileName = $manualFile->getClientOriginalName();
                $manualFileType = strtolower($manualFile->getClientOriginalExtension());
                $manualFileSize = $this->formatSizeUnits($manualFile->getSize());
                
                // Tạo tên file duy nhất
                $manualFileName = Str::slug($request->name) . '-manual-' . ($request->version ?? 'unknown') . '-' . time() . '.' . $manualFileType;
                
                // Lưu file vào storage/app/public/manuals
                $manualFilePath = $manualFile->storeAs('manuals', $manualFileName, 'public');
                
                // Thêm thông tin tài liệu hướng dẫn vào dữ liệu
                $data['manual_path'] = $manualFilePath;
                $data['manual_name'] = $originalManualFileName;
                $data['manual_size'] = $manualFileSize;
            }
            
            // Debug: Log data trước khi tạo
            Log::info('Data to create software:', $data);
            
            $software = Software::create($data);
            
            // Debug: Log kết quả sau khi tạo
            Log::info('Software created successfully:', $software->toArray());

            // Ghi nhật ký tạo mới phần mềm
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'create',
                    'software',
                    'Tạo mới phần mềm: ' . $data['name'],
                    null,
                    $data
                );
            }

            $message = 'Phần mềm đã được tạo thành công';
            if (!$request->hasFile('software_file')) {
                $message .= ' (chưa có file phần mềm)';
            }
            return redirect()->route('software.index')->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Lỗi khi tạo phần mềm: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->with('error', 'Đã xảy ra lỗi khi tạo phần mềm: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Software $software)
    {
        // Ghi nhật ký xem chi tiết phần mềm
        if (Auth::check()) {
            UserLog::logActivity(
                Auth::id(),
                'view',
                'software',
                'Xem chi tiết phần mềm: ' . $software->name,
                null,
                $software->toArray()
            );
        }

        return view('software.show', compact('software'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Software $software)
    {
        return view('software.edit', compact('software'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Software $software)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'version' => 'nullable|string|max:50',
            'status' => 'nullable|string|in:active,inactive,beta',
            'software_file' => 'nullable|file|max:512000', // 500MB (tăng lên để phù hợp với file lớn)
            'manual_file' => 'nullable|file|mimes:pdf,doc,docx,txt|max:10240', // 10MB cho tài liệu hướng dẫn
            'platform' => 'nullable|string',
            'release_date' => 'nullable|date',
            'description' => 'nullable|string',
            'changelog' => 'nullable|string',
        ]);

        try {
            // Lưu dữ liệu cũ trước khi cập nhật
            $oldData = $software->toArray();

            $data = [
                'name' => $request->name,
                'type' => $request->type,
            ];
            
            // Thêm các trường không bắt buộc nếu có giá trị
            if ($request->filled('version')) {
                $data['version'] = $request->version;
            }
            if ($request->filled('release_date')) {
                $data['release_date'] = $request->release_date;
            }
            if ($request->filled('platform')) {
                $data['platform'] = $request->platform;
            }
            if ($request->filled('status')) {
                $data['status'] = $request->status;
            }
            if ($request->filled('description')) {
                $data['description'] = $request->description;
            }
            if ($request->filled('changelog')) {
                $data['changelog'] = $request->changelog;
            }
            
            // Nếu có file mới được tải lên
            if ($request->hasFile('software_file')) {
                $file = $request->file('software_file');
                $originalFileName = $file->getClientOriginalName();
                $fileType = strtolower($file->getClientOriginalExtension());
                $fileSize = $this->formatSizeUnits($file->getSize());
                
                // Tạo tên file duy nhất
                $fileName = Str::slug($request->name) . '-' . ($request->version ?? 'unknown') . '-' . time() . '.' . $fileType;
                
                // Xóa file cũ nếu có
                if (!empty($software->file_path) && Storage::disk('public')->exists($software->file_path)) {
                    Storage::disk('public')->delete($software->file_path);
                }
                
                // Lưu file mới
                $filePath = $file->storeAs('software', $fileName, 'public');
                
                // Cập nhật thông tin file
                $data['file_path'] = $filePath;
                $data['file_name'] = $originalFileName;
                $data['file_size'] = $fileSize;
                $data['file_type'] = $fileType;
            }
            
            // Nếu có tài liệu hướng dẫn mới được tải lên
            if ($request->hasFile('manual_file')) {
                $manualFile = $request->file('manual_file');
                $originalManualFileName = $manualFile->getClientOriginalName();
                $manualFileType = strtolower($manualFile->getClientOriginalExtension());
                $manualFileSize = $this->formatSizeUnits($manualFile->getSize());
                
                // Tạo tên file duy nhất
                $manualFileName = Str::slug($request->name) . '-manual-' . ($request->version ?? 'unknown') . '-' . time() . '.' . $manualFileType;
                
                // Xóa file cũ nếu có
                if (!empty($software->manual_path) && Storage::disk('public')->exists($software->manual_path)) {
                    Storage::disk('public')->delete($software->manual_path);
                }
                
                // Lưu file mới
                $manualFilePath = $manualFile->storeAs('manuals', $manualFileName, 'public');
                
                // Cập nhật thông tin tài liệu hướng dẫn
                $data['manual_path'] = $manualFilePath;
                $data['manual_name'] = $originalManualFileName;
                $data['manual_size'] = $manualFileSize;
            }
            
            $software->update($data);

            // Ghi nhật ký cập nhật phần mềm
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'update',
                    'software',
                    'Cập nhật phần mềm: ' . $software->name,
                    $oldData,
                    $software->toArray()
                );
            }
            
            $message = 'Phần mềm đã được cập nhật thành công';
            if (!$request->hasFile('software_file') && empty($software->file_path)) {
                $message .= ' (chưa có file phần mềm)';
            }
            return redirect()->route('software.show', $software)->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Lỗi khi cập nhật phần mềm: ' . $e->getMessage());
            return back()->with('error', 'Đã xảy ra lỗi khi cập nhật phần mềm')->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Software $software)
    {
        try {
            // Lưu dữ liệu cũ trước khi xóa
            $oldData = $software->toArray();
            $softwareName = $software->name;

            // Xóa file phần mềm nếu có
            if (!empty($software->file_path) && Storage::disk('public')->exists($software->file_path)) {
                Storage::disk('public')->delete($software->file_path);
            }
            
            // Xóa file tài liệu hướng dẫn nếu có
            if (!empty($software->manual_path) && Storage::disk('public')->exists($software->manual_path)) {
                Storage::disk('public')->delete($software->manual_path);
            }
            
            $software->delete();
            
            // Ghi nhật ký xóa phần mềm
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'delete',
                    'software',
                    'Xóa phần mềm: ' . $softwareName,
                    $oldData,
                    null
                );
            }

            return redirect()->route('software.index')->with('success', 'Phần mềm đã được xóa thành công');
        } catch (\Exception $e) {
            Log::error('Lỗi khi xóa phần mềm: ' . $e->getMessage());
            return back()->with('error', 'Đã xảy ra lỗi khi xóa phần mềm');
        }
    }
    
    /**
     * Download the software file.
     */
    public function download(Software $software)
    {
        try {
            if (empty($software->file_path)) {
                return back()->with('error', 'Phần mềm này chưa có file để tải xuống. Vui lòng tải lên file trước.');
            }
            
            $filePath = storage_path('app/public/' . $software->file_path);
            
            if (!file_exists($filePath)) {
                return back()->with('error', 'File không tồn tại');
            }
            
            // Tăng số lượt tải xuống
            $software->incrementDownloadCount();

            // Ghi nhật ký tải xuống phần mềm
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'download',
                    'software',
                    'Tải xuống phần mềm: ' . $software->name,
                    null,
                    $software->toArray()
                );
            }
            
            // Trả về file để tải xuống
            return response()->download($filePath, $software->file_name);
        } catch (\Exception $e) {
            Log::error('Lỗi khi tải xuống phần mềm: ' . $e->getMessage());
            return back()->with('error', 'Đã xảy ra lỗi khi tải xuống phần mềm');
        }
    }
    
    /**
     * Download the manual file.
     */
    public function downloadManual(Software $software)
    {
        try {
            if (empty($software->manual_path)) {
                return back()->with('error', 'Không có tài liệu hướng dẫn cho phần mềm này');
            }
            
            $filePath = storage_path('app/public/' . $software->manual_path);
            
            if (!file_exists($filePath)) {
                return back()->with('error', 'Tài liệu hướng dẫn không tồn tại');
            }
            
            // Trả về file để tải xuống
            return response()->download($filePath, $software->manual_name);
        } catch (\Exception $e) {
            Log::error('Lỗi khi tải xuống tài liệu hướng dẫn: ' . $e->getMessage());
            return back()->with('error', 'Đã xảy ra lỗi khi tải xuống tài liệu hướng dẫn');
        }
    }
    
    /**
     * Format file size to human readable format.
     */
    private function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }

        return $bytes;
    }
}
