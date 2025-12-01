<?php
namespace App\Models;

class BookingLog extends BaseModel
{
    protected string $table = 'booking_log';

    /**
     * Hàm tiện ích để ghi log nhanh
     */
    public static function record($bookingId, $oldState, $newState, $note = '', $user = 'Admin')
    {
        (new self())->create([
            'booking_id' => $bookingId,
            'old_state'  => $oldState,
            'new_state'  => $newState,
            'changed_by' => $user, // Sau này lấy từ Session User
            'note'       => $note,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}