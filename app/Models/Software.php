<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Software extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'version',
        'type',
        'file_path',
        'file_name',
        'file_size',
        'file_type',
        'release_date',
        'platform',
        'status',
        'description',
        'changelog',
        'download_count'
    ];

    protected $casts = [
        'release_date' => 'date',
    ];

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'active' => 'Hoạt động',
            'inactive' => 'Đã ngừng',
            'beta' => 'Phiên bản beta',
            default => 'Không xác định'
        };
    }

    public function getStatusClassAttribute()
    {
        return match($this->status) {
            'active' => 'bg-blue-100 text-blue-800',
            'inactive' => 'bg-gray-100 text-gray-800',
            'beta' => 'bg-yellow-100 text-yellow-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function getTypeLabelAttribute()
    {
        return match($this->type) {
            'mobile_app' => 'Ứng dụng di động',
            'firmware' => 'Firmware',
            'desktop_app' => 'Ứng dụng máy tính',
            'driver' => 'Driver',
            'other' => 'Khác',
            default => 'Không xác định'
        };
    }

    public function getFileTypeClassAttribute()
    {
        return match($this->file_type) {
            'apk' => 'bg-green-100 text-green-800',
            'bin' => 'bg-yellow-100 text-yellow-800',
            'zip' => 'bg-blue-100 text-blue-800',
            'exe' => 'bg-purple-100 text-purple-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function getDownloadUrlAttribute()
    {
        return route('software.download', $this->id);
    }

    public function incrementDownloadCount()
    {
        $this->increment('download_count');
    }
}
