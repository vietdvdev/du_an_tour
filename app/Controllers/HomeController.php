<?php


namespace App\Controllers;


use App\Core\Request;
use App\Core\Response;
use App\Core\DB;
use App\Core\Database; // [QUAN TRỌNG] Thêm dòng này


class HomeController extends BaseController
{
    /**
     * Dashboard Admin
     */
    public function index(Request $req): Response
    {
        // 1. Thống kê tổng quan (Cards)
        $totalTours = DB::table('tour')->count();
        $totalBookings = DB::table('booking')->count();


        // Tính tổng doanh thu (Dùng Raw SQL qua Database::pdo() cho chắc chắn)
        $sqlRevenue = "SELECT SUM(total_amount) FROM booking WHERE state != 'CANCELLED'";
        $totalRevenue = (float)Database::pdo()->query($sqlRevenue)->fetchColumn();


        // 2. Dữ liệu Biểu đồ Doanh thu (12 tháng)
        $revenueData = $this->getMonthlyRevenue();


        // 3. Dữ liệu Biểu đồ Trạng thái Booking
        $statusData = $this->getBookingStatusStats();


        return $this->render('home/index', [
            'totalTours'    => $totalTours,
            'totalBookings' => $totalBookings,
            'totalRevenue'  => $totalRevenue,
            'revenueLabels' => json_encode($revenueData['labels']),
            'revenueValues' => json_encode($revenueData['values']),
            'statusLabels'  => json_encode($statusData['labels']),
            'statusValues'  => json_encode($statusData['values']),
        ]);
    }


    /**
     * Helper: Lấy doanh thu theo từng tháng trong năm nay
     */
    private function getMonthlyRevenue(): array
    {
        $year = date('Y');
        $sql = "
            SELECT MONTH(created_at) as month, SUM(total_amount) as total
            FROM booking
            WHERE YEAR(created_at) = :year AND state != 'CANCELLED'
            GROUP BY MONTH(created_at)
        ";


        // [SỬA LỖI] Thay DB::pdo() thành Database::pdo()
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute(['year' => $year]);
        $rows = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR); // [month => total]


        $labels = [];
        $values = [];


        // Loop 12 tháng để đảm bảo đủ cột dù tháng đó không có doanh thu
        for ($i = 1; $i <= 12; $i++) {
            $labels[] = "Tháng $i";
            $values[] = $rows[$i] ?? 0;
        }


        return ['labels' => $labels, 'values' => $values];
    }


    /**
     * Helper: Thống kê trạng thái Booking
     */
    private function getBookingStatusStats(): array
    {
        $sql = "SELECT state, COUNT(*) as count FROM booking GROUP BY state";


        // [SỬA LỖI] Thay DB::pdo() thành Database::pdo()
        $stmt = Database::pdo()->query($sql);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);


        $labels = [];
        $values = [];


        // Map trạng thái sang tiếng Việt cho đẹp
        $map = [
            'PLACED'    => 'Mới đặt',
            'DEPOSITED' => 'Đã cọc',
            'COMPLETED' => 'Hoàn thành',
            'CANCELLED' => 'Đã hủy'
        ];


        foreach ($rows as $row) {
            $labels[] = $map[$row['state']] ?? $row['state'];
            $values[] = $row['count'];
        }


        return ['labels' => $labels, 'values' => $values];
    }
}





