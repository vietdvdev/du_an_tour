<?php
namespace App\Models;


use App\Core\Model;


class Booking extends BaseModel
{
    protected string $table = 'booking';


    /**
     * Sinh mã booking ngẫu nhiên duy nhất
     * Format: BK-YYYYMMDD-XXXX (VD: BK-20231126-A1B2)
     */
    public static function generateCode(): string
        {
            return 'BK-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
        }
       
    /**
     * Lấy danh sách booking kèm thông tin Tour và Departure
     */
    public function getAllWithDetails()
    {
        return $this->builder()
            ->select('booking.*, departure.start_date, departure.end_date, tour.code as tour_code, tour.name as tour_name')
            ->join('departure', 'departure.id', '=', 'booking.departure_id')
            ->join('tour', 'tour.id', '=', 'departure.tour_id')
            ->orderBy('booking.id', 'DESC')
            ->get();
    }




    /**
     * Lấy danh sách Booking có lọc theo Loại và Thời gian
     * @param string $type Loại tour: 'all', 'system', 'custom'
     * @param string $time Thời gian: 'all', 'today', 'week', 'month', 'year'
     */


   
 public function getFilteredBookings($type = 'all', $time = 'all')
    {
        // 1. JOIN 3 bảng: booking, departure, tour
        // SELECT thêm:
        // - t.name as tour_name: Tên tour
        // - d.start_date: Ngày khởi hành (View đang dùng cái này)
        // - tour_type: Chuyển đổi is_custom (0/1) thành chuỗi 'custom'/'standard' cho View
        $sql = "SELECT b.*,
                       t.name as tour_name,
                       d.start_date,
                       CASE WHEN t.is_custom = 1 THEN 'custom' ELSE 'standard' END as tour_type
                FROM booking b
                JOIN departure d ON b.departure_id = d.id
                JOIN tour t ON d.tour_id = t.id
                WHERE 1=1";


        $params = [];


        // 2. Lọc theo Loại Tour (Dựa vào cột is_custom của bảng tour)
        if ($type === 'system') {
            // Tour hệ thống (Standard) => is_custom = 0
            $sql .= " AND t.is_custom = 0";
        } elseif ($type === 'custom') {
            // Tour yêu cầu => is_custom = 1
            $sql .= " AND t.is_custom = 1";
        }


        // 3. Lọc theo Thời gian (Dựa vào ngày tạo đơn booking.created_at)
        if ($time === 'today') {
            $sql .= " AND DATE(b.created_at) = CURDATE()";
        } elseif ($time === 'week') {
            $sql .= " AND YEARWEEK(b.created_at, 1) = YEARWEEK(CURDATE(), 1)";
        } elseif ($time === 'month') {
            $sql .= " AND MONTH(b.created_at) = MONTH(CURDATE()) AND YEAR(b.created_at) = YEAR(CURDATE())";
        } elseif ($time === 'year') {
            $sql .= " AND YEAR(b.created_at) = YEAR(CURDATE())";
        }


        // 4. Sắp xếp: Mới nhất lên đầu
        $sql .= " ORDER BY b.created_at DESC";


        try {
            /** @var \PDO $pdo */
            // [SỬA LỖI] Thêm dấu \App\Core\ để PHP tìm đúng class Database trong Core
            // thay vì tìm trong App\Models
            $pdo = \App\Core\Database::pdo();
           
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);


            /** @var \PDOStatement $stmt */
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);


        } catch (\Exception $e) {
            // Debug: In lỗi nếu có vấn đề
            echo "<pre>";
            echo "SQL Error: " . $e->getMessage() . "<br>";
            echo "SQL Query: " . $sql;
            echo "</pre>";
            die();
        }
    }
   
}

