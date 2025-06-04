<?php

namespace App\Http\Controllers;

use App\Models\Software;
use Illuminate\Http\Request;
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
        $request->validate([
            'name' => 'required|string|max:255',
            'version' => 'required|string|max:50',
            'type' => 'required|string',
            'status' => 'required|string|in:active,inactive,beta',
            'software_file' => 'required|file|max:40960', // 40MB (để phù hợp với post_max_size của server)
            'platform' => 'nullable|string',
            'release_date' => 'nullable|date',
            'description' => 'nullable|string',
            'changelog' => 'nullable|string',
        ]);

        try {
            $file = $request->file('software_file');
            $originalFileName = $file->getClientOriginalName();
            $fileType = strtolower($file->getClientOriginalExtension());
            $fileSize = $this->formatSizeUnits($file->getSize());
            
            // Tạo tên file duy nhất
            $fileName = Str::slug($request->name) . '-' . $request->version . '-' . time() . '.' . $fileType;
            
            // Lưu file vào storage/app/public/software
            $filePath = $file->storeAs('software', $fileName, 'public');
            
            Software::create([
                'name' => $request->name,
                'version' => $request->version,
                'type' => $request->type,
                'file_path' => $filePath,
                'file_name' => $originalFileName,
                'file_size' => $fileSize,
                'file_type' => $fileType,
                'release_date' => $request->release_date,
                'platform' => $request->platform,
                'status' => $request->status,
                'description' => $request->description,
                'changelog' => $request->changelog,
            ]);
            
            return redirect()->route('software.index')->with('success', 'Phần mềm đã được tạo thành công');
        } catch (\Exception $e) {
            Log::error('Lỗi khi tạo phần mềm: ' . $e->getMessage());
            return back()->with('error', 'Đã xảy ra lỗi khi tạo phần mềm')->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Software $software)
    {
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
            'version' => 'required|string|max:50',
            'type' => 'required|string',
            'status' => 'required|string|in:active,inactive,beta',
            'software_file' => 'nullable|file|max:40960', // 40MB (để phù hợp với post_max_size của server)
            'platform' => 'nullable|string',
            'release_date' => 'nullable|date',
            'description' => 'nullable|string',
            'changelog' => 'nullable|string',
        ]);

        try {
            $data = [
                'name' => $request->name,
                'version' => $request->version,
                'type' => $request->type,
                'release_date' => $request->release_date,
                'platform' => $request->platform,
                'status' => $request->status,
                'description' => $request->description,
                'changelog' => $request->changelog,
            ];
            
            // Nếu có file mới được tải lên
            if ($request->hasFile('software_file')) {
                $file = $request->file('software_file');
                $originalFileName = $file->getClientOriginalName();
                $fileType = strtolower($file->getClientOriginalExtension());
                $fileSize = $this->formatSizeUnits($file->getSize());
                
                // Tạo tên file duy nhất
                $fileName = Str::slug($request->name) . '-' . $request->version . '-' . time() . '.' . $fileType;
                
                // Xóa file cũ
                if (Storage::disk('public')->exists($software->file_path)) {
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
            
            $software->update($data);
            
            return redirect()->route('software.show', $software)->with('success', 'Phần mềm đã được cập nhật thành công');
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
            // Xóa file
            if (Storage::disk('public')->exists($software->file_path)) {
                Storage::disk('public')->delete($software->file_path);
            }
            
            $software->delete();
            
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
            $filePath = storage_path('app/public/' . $software->file_path);
            
            if (!file_exists($filePath)) {
                return back()->with('error', 'File không tồn tại');
            }
            
            // Tăng số lượt tải xuống
            $software->incrementDownloadCount();
            
            // Trả về file để tải xuống
            return response()->download($filePath, $software->file_name);
        } catch (\Exception $e) {
            Log::error('Lỗi khi tải xuống phần mềm: ' . $e->getMessage());
            return back()->with('error', 'Đã xảy ra lỗi khi tải xuống phần mềm');
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
