<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Assignment;
use App\Models\Departure;
use App\Models\User;
use App\Core\Database; // Import để dùng DB::pdo()

/**
 * Class AssignmentController
 * Quản lý việc phân công Hướng dẫn viên (HDV) cho các đợt khởi hành
 */
class AssignmentController extends BaseController // Sửa BaseController thành Controller nếu hệ thống bạn dùng tên này
{
    /**
     * [GET] Hiển thị Bảng điều hành (Dashboard phân công)
     * Chức năng:
     * 1. Lấy danh sách các Tour sắp khởi hành đã có khách đặt.
     * 2. Lấy danh sách HDV khả dụng.
     * 3. Lấy danh sách phân công hiện tại để hiển thị trạng thái.
     */
    public function index(Request $req): Response
    {
        // 1. VIẾT QUERY TÙY CHỈNH ĐỂ LỌC THEO YÊU CẦU:
        // - Chỉ lấy tour chưa đi (start_date >= hôm nay)
        // - Chỉ lấy tour đã có booking (INNER JOIN booking)
        // - Tính tổng số khách (SUM pax_count)
        
        $sql = "SELECT d.*,
                       t.code as tour_code,
                       t.name as tour_name,
                       SUM(b.pax_count) as total_pax
                FROM departure d
                JOIN tour t ON d.tour_id = t.id
                JOIN booking b ON b.departure_id = d.id
                WHERE d.start_date >= CURDATE()
                  AND b.state != 'CANCELLED'
                GROUP BY d.id
                ORDER BY d.start_date ASC";

        try {
            // Thực thi câu lệnh SQL thuần để lấy dữ liệu tổng hợp
            /** @var \PDO $pdo */
            $pdo = Database::pdo();
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $departures = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $departures = [];
        }

        // 2. Lấy danh sách Hướng dẫn viên (Role = 1/GUIDE)
        // Chỉ lấy những tài khoản đang hoạt động (is_active = 1)
        $guides = (new User())->builder()
            ->where('role', '1')
            ->where('is_active', 1)
            ->get();

        // 3. Lấy danh sách phân công hiện tại
        // Join bảng assignments với bảng users để lấy tên HDV hiển thị ra view
        $assignmentModel = new Assignment();
        $assignmentsRaw = $assignmentModel->builder()
            ->select('assignment.*, users.full_name')
            ->join('users', 'users.id', '=', 'assignment.guide_id')
            ->get();
            
        // Gom nhóm assignments theo departure_id
        // Cấu trúc: [departure_id => [danh sách HDV đã gán...]]
        // Giúp View dễ dàng kiểm tra xem Tour này đã có ai phụ trách chưa
        $assignedMap = [];
        foreach ($assignmentsRaw as $assign) {
            $assignedMap[$assign['departure_id']][] = $assign;
        }

        // Render giao diện và truyền dữ liệu
        return $this->render('assignment/index', [
            'title' => 'Điều hành Tour',
            'departures' => $departures,
            'guides' => $guides,
            'assignedMap' => $assignedMap
        ]);
    }

    /**
     * [POST] Xử lý Gán Hướng dẫn viên vào Tour
     * Chức năng:
     * 1. Validate dữ liệu đầu vào.
     * 2. Kiểm tra trùng lịch (HDV có đang bận đi tour khác không).
     * 3. Kiểm tra trùng lặp (HDV đã được gán vào chính tour này chưa).
     * 4. Lưu phân công vào database.
     */
    public function store(Request $req): Response
    {
        // Lấy dữ liệu từ Form
        $departureId = (int)$req->input('departure_id');
        $guideId = (int)$req->input('guide_id');
        $role = $req->input('role') ?? 'MAIN';

        // Validate cơ bản
        if ($departureId <= 0 || $guideId <= 0) {
            $_SESSION['flash_error'] = "Dữ liệu không hợp lệ.";
            return $this->redirect(route('assignment.index'));
        }

        // Lấy thông tin ngày đi/về của Tour để check trùng lịch
        $dep = (new Departure())->find($departureId);
        if (!$dep) return $this->redirect(route('assignment.index'));

        $model = new Assignment();

        // 1. Check trùng lịch của HDV này (Quan trọng)
        // Lưu ý: Cần đảm bảo hàm checkOverlap đã tồn tại trong Model Assignment
        // Logic: Nếu khoảng thời gian của tour này giao nhau với tour khác mà HDV đang đi -> Báo lỗi
        if (method_exists($model, 'checkOverlap') && $model->checkOverlap($guideId, $dep['start_date'], $dep['end_date'])) {
             $_SESSION['flash_error'] = "HDV này đã có lịch đi tour khác trong khoảng thời gian này (" .
                date('d/m', strtotime($dep['start_date'])) . " - " . date('d/m', strtotime($dep['end_date'])) . ")!";
             return $this->redirect(route('assignment.index'));
        }

        // 2. Check đã gán vào tour này chưa (Tránh gán 1 người 2 lần vào cùng 1 đoàn)
        $exists = $model->builder()
            ->where('departure_id', $departureId)
            ->where('guide_id', $guideId)
            ->first();

        if ($exists) {
            $_SESSION['flash_error'] = "HDV này đã được gán vào tour rồi.";
        } else {
            try {
                // Tạo mới bản ghi phân công
                $model->create([
                    'departure_id' => $departureId,
                    'guide_id' => $guideId,
                    'role' => $role,
                    'start_date' => $dep['start_date'],
                    'end_date' => $dep['end_date']
                ]);
                $_SESSION['flash_success'] = "Phân công thành công.";
            } catch (\Throwable $e) {
                $_SESSION['flash_error'] = "Lỗi hệ thống: " . $e->getMessage();
            }
        }

        return $this->redirect(route('assignment.index'));
    }

    /**
     * [POST] Hủy phân công (Xóa HDV khỏi Tour)
     */
    public function delete(Request $req): Response
    {
        $assignmentId = (int)$req->input('assignment_id');
        
        if ($assignmentId > 0) {
            // Xóa bản ghi trong bảng assignment
            (new Assignment())->delete($assignmentId);
            $_SESSION['flash_success'] = "Đã hủy phân công.";
        }
        
        return $this->redirect(route('assignment.index'));
    }
}