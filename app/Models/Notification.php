<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'message',
        'type',
        'data',
        'icon',
        'link',
        'user_id',
        'is_read',
        'related_type',
        'related_id',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'data' => 'array',
    ];

    /**
     * Lấy người dùng liên quan đến thông báo
     */
    public function user()
    {
        return $this->belongsTo(Employee::class, 'user_id');
    }

    /**
     * Lấy đối tượng liên quan đến thông báo (polymorphic)
     */
    public function related()
    {
        if ($this->related_type === 'assembly') {
            return $this->belongsTo(Assembly::class, 'related_id');
        } elseif ($this->related_type === 'testing') {
            return $this->belongsTo(\App\Models\Testing::class, 'related_id');
        }
        
        return null;
    }

    /**
     * Đánh dấu thông báo đã đọc
     */
    public function markAsRead()
    {
        $this->is_read = true;
        $this->save();
        
        return $this;
    }

    /**
     * Tạo thông báo mới
     */
    public static function createNotification($title, $message, $type = 'info', $userId = null, $relatedType = null, $relatedId = null, $link = null, $data = null)
    {
        $icon = self::getIconByType($type);
        
        return self::create([
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'data' => $data,
            'icon' => $icon,
            'link' => $link,
            'user_id' => $userId,
            'related_type' => $relatedType,
            'related_id' => $relatedId,
            'is_read' => false,
        ]);
    }

    /**
     * Lấy icon dựa vào loại thông báo
     */
    public static function getIconByType($type)
    {
        switch ($type) {
            case 'success':
                return 'fas fa-check-circle';
            case 'warning':
                return 'fas fa-exclamation-triangle';
            case 'error':
                return 'fas fa-times-circle';
            case 'info':
            default:
                return 'fas fa-info-circle';
        }
    }
}
