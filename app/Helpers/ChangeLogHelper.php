<?php

namespace App\Helpers;

use App\Http\Controllers\ChangeLogController;

class ChangeLogHelper
{
    /**
     * Log nhập kho
     */
    public static function nhapKho($itemCode, $itemName, $quantity, $importCode, $description = null, $detailedInfo = [], $notes = null)
    {
        return ChangeLogController::logImport($itemCode, $itemName, $quantity, $importCode, $description, $detailedInfo, $notes);
    }

    /**
     * Log xuất kho
     */
    public static function xuatKho($itemCode, $itemName, $quantity, $exportCode, $description = null, $detailedInfo = [], $notes = null)
    {
        return ChangeLogController::logExport($itemCode, $itemName, $quantity, $exportCode, $description, $detailedInfo, $notes);
    }

    /**
     * Log lắp ráp - ĐÃ BỊ VÔ HIỆU HÓA
     * Thay thế bằng xuất kho với phiếu xuất kho
     */
    /*
    public static function lapRap($itemCode, $itemName, $quantity, $assemblyCode, $description = null, $detailedInfo = [], $notes = null)
    {
        return ChangeLogController::logAssembly($itemCode, $itemName, $quantity, $assemblyCode, $description, $detailedInfo, $notes);
    }
    */

    /**
     * Log sửa chữa
     */
    public static function suaChua($itemCode, $itemName, $quantity, $repairCode, $description = null, $detailedInfo = [], $notes = null)
    {
        return ChangeLogController::logRepair($itemCode, $itemName, $quantity, $repairCode, $description, $detailedInfo, $notes);
    }

    /**
     * Log thu hồi
     */
    public static function thuHoi($itemCode, $itemName, $quantity, $recallCode, $description = null, $detailedInfo = [], $notes = null)
    {
        return ChangeLogController::logRecall($itemCode, $itemName, $quantity, $recallCode, $description, $detailedInfo, $notes);
    }

    /**
     * Log chuyển kho
     */
    public static function chuyenKho($itemCode, $itemName, $quantity, $transferCode, $description = null, $detailedInfo = [], $notes = null)
    {
        return ChangeLogController::logTransfer($itemCode, $itemName, $quantity, $transferCode, $description, $detailedInfo, $notes);
    }

    /**
     * Log thay đổi tuỳ chỉnh
     */
    public static function custom($data)
    {
        return ChangeLogController::createLogEntry($data);
    }

    /**
     * Log nhiều items cùng lúc - cho nhập/xuất nhiều vật tư
     */
    public static function logNhieu($items, $type, $documentCode, $description = null)
    {
        return ChangeLogController::logMultipleItems($items, $type, $documentCode, $description);
    }

    /**
     * Cập nhật changelog theo ID
     */
    public static function capNhat($id, $data)
    {
        return ChangeLogController::updateLogEntry($id, $data);
    }

    /**
     * Cập nhật mô tả
     */
    public static function capNhatMoTa($id, $description)
    {
        return ChangeLogController::updateDescription($id, $description);
    }

    /**
     * Cập nhật ghi chú
     */
    public static function capNhatGhiChu($id, $notes)
    {
        return ChangeLogController::updateNotes($id, $notes);
    }

    /**
     * Cập nhật số lượng
     */
    public static function capNhatSoLuong($id, $quantity)
    {
        return ChangeLogController::updateQuantity($id, $quantity);
    }

    /**
     * Cập nhật người thực hiện
     */
    public static function capNhatNguoiThucHien($id, $performedBy)
    {
        return ChangeLogController::updatePerformedBy($id, $performedBy);
    }

    /**
     * Cập nhật thông tin chi tiết
     */
    public static function capNhatThongTinChiTiet($id, $detailedInfo)
    {
        return ChangeLogController::updateDetailedInfo($id, $detailedInfo);
    }

    /**
     * Thêm thông tin chi tiết
     */
    public static function themThongTinChiTiet($id, $key, $value)
    {
        return ChangeLogController::addDetailedInfo($id, $key, $value);
    }

    /**
     * Xóa thông tin chi tiết
     */
    public static function xoaThongTinChiTiet($id, $key)
    {
        return ChangeLogController::removeDetailedInfo($id, $key);
    }

    /**
     * Cập nhật theo mã phiếu
     */
    public static function capNhatTheoMaPhieu($documentCode, $data)
    {
        return ChangeLogController::updateByDocumentCode($documentCode, $data);
    }

    /**
     * Cập nhật theo mã vật tư/sản phẩm
     */
    public static function capNhatTheoMaVatTu($itemCode, $data)
    {
        return ChangeLogController::updateByItemCode($itemCode, $data);
    }
} 