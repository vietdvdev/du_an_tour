<?php
namespace App\Models;

class TourSupplier extends BaseModel
{
    protected string $table = 'tour_supplier';
    // Bảng này không có cột 'id' tự tăng, nên primaryKey mặc định không quan trọng lắm
    // trừ khi bạn muốn dùng các hàm find/update mặc định.

    /**
     * Hàm xóa custom dành cho bảng trung gian (Composite Key)
     * Vì hàm delete($id) của BaseModel chỉ xóa theo 1 ID.
     */
    public function remove(int $tourId, int $supplierId): bool
    {
        // Sử dụng builder() từ BaseModel
        $deleted = $this->builder()
            ->where('tour_id', $tourId)
            ->where('supplier_id', $supplierId)
            ->delete();

        return $deleted > 0;
    }
    
    /**
     * Kiểm tra xem đã tồn tại chưa để tránh lỗi Duplicate entry
     */
    public function exists(int $tourId, int $supplierId): bool
    {
        $record = $this->builder()
            ->where('tour_id', $tourId)
            ->where('supplier_id', $supplierId)
            ->first();
            
        return !empty($record);
    }
}