<?php


namespace App\Controllers;


use App\Core\Request;
use App\Core\Response;
use App\Core\Database;


class ReportController extends BaseController
{
    /**
     * [GET] Báo cáo doanh thu theo Tour (Departure)
     */
    public function revenue(Request $req): Response
    {
        // 1. Nhận tham số lọc từ URL (nếu có)
        // Mặc định: Lấy từ đầu tháng này đến cuối tháng này
        $defaultStart = date('Y-m-01');
        $defaultEnd   = date('Y-m-t');


        $fromDate = $req->input('from_date') ?? $defaultStart;
        $toDate   = $req->input('to_date') ?? $defaultEnd;


        // 2. Xây dựng câu truy vấn có điều kiện thời gian
        // Chúng ta lọc theo ngày khởi hành (d.start_date)
        $sql = "
            SELECT
                d.id,
                t.code as tour_code,
                t.name as tour_name,
                d.start_date,
                d.end_date,
               
                (SELECT COALESCE(SUM(b.pax_count), 0)
                 FROM booking b
                 WHERE b.departure_id = d.id AND b.state != 'CANCELLED') as total_pax,


                (SELECT COALESCE(SUM(b.total_amount), 0)
                 FROM booking b
                 WHERE b.departure_id = d.id AND b.state != 'CANCELLED') as total_revenue,


                (SELECT COALESCE(SUM(bs.cost_amount), 0)
                 FROM booking_service bs
                 JOIN booking b2 ON bs.booking_id = b2.id
                 WHERE b2.departure_id = d.id AND b2.state != 'CANCELLED') as total_service_cost,


                (SELECT COALESCE(SUM(e.amount), 0)
                 FROM expense e
                 WHERE e.departure_id = d.id) as total_expense


            FROM departure d
            JOIN tour t ON d.tour_id = t.id
            WHERE d.start_date >= :from_date AND d.start_date <= :to_date
            ORDER BY d.start_date DESC
        ";


        try {
            $stmt = Database::pdo()->prepare($sql);
            // Bind tham số để tránh SQL Injection
            $stmt->execute([
                'from_date' => $fromDate,
                'to_date'   => $toDate
            ]);
            $reports = $stmt->fetchAll(\PDO::FETCH_ASSOC);


            // Tính toán lợi nhuận
            foreach ($reports as &$row) {
                $row['total_cost'] = $row['total_service_cost'] + $row['total_expense'];
                $row['profit'] = $row['total_revenue'] - $row['total_cost'];
               
                if ($row['total_revenue'] > 0) {
                    $row['profit_margin'] = round(($row['profit'] / $row['total_revenue']) * 100, 2);
                } else {
                    $row['profit_margin'] = 0;
                }
            }


        } catch (\Throwable $e) {
            $reports = [];
            $_SESSION['flash_error'] = "Lỗi khi tạo báo cáo: " . $e->getMessage();
        }


        return $this->render('report/revenue', [
            'reports'  => $reports,
            'fromDate' => $fromDate,
            'toDate'   => $toDate
        ]);
    }
}

