<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Assignment;
use App\Models\Departure;
use App\Models\User; 

class AssignmentController extends BaseController
{
    // [GET] Bảng điều hành
    public function index(Request $req): Response
    {
        // 1. Lấy các đợt khởi hành sắp tới (OPEN)
        $departures = (new Departure())->getAllWithStats(); 
        // Lưu ý: Hàm getAllWithStats đã viết ở bài trước, cần đảm bảo nó lấy đủ thông tin

        // 2. Lấy danh sách Hướng dẫn viên (Role = HDV hoặc GUIDE)
        // Tùy vào dữ liệu trong bảng users của bạn
        $guides = (new User())->builder() // Dùng builder nếu chưa có Model User hoàn chỉnh
            ->where('role', '1') // Hoặc 'GUIDE' tùy DB
            ->where('is_active', 1)
            ->get();

        // 3. Lấy danh sách phân công hiện tại để hiển thị lên bảng
        $assignmentModel = new Assignment();
        $assignmentsRaw = $assignmentModel->builder()
            ->select('assignment.*, users.full_name')
            ->join('users', 'users.id', '=', 'assignment.guide_id')
            ->get();
            
        // Gom nhóm assignments theo departure_id để dễ hiển thị ở View
        $assignedMap = [];
        foreach ($assignmentsRaw as $assign) {
            $assignedMap[$assign['departure_id']][] = $assign;
        }

        return $this->render('assignment/index', [
            'title' => 'Điều hành Tour',
            'departures' => $departures,
            'guides' => $guides,
            'assignedMap' => $assignedMap
        ]);
    }

    // [POST] Gán HDV
    public function store(Request $req): Response
    {
        $departureId = (int)$req->input('departure_id');
        $guideId = (int)$req->input('guide_id');
        $role = $req->input('role') ?? 'MAIN';

        if ($departureId <= 0 || $guideId <= 0) {
            $_SESSION['flash_error'] = "Dữ liệu không hợp lệ.";
            return $this->redirect(route('assignment.index'));
        }

        // Lấy thông tin ngày đi/về của Tour để check trùng lịch
        $dep = (new Departure())->find($departureId);
        if (!$dep) return $this->redirect(route('assignment.index'));

        $model = new Assignment();

        // 1. Check trùng lịch của HDV này (ĐÃ UNCOMMENT ĐỂ KÍCH HOẠT)
        if ($model->checkOverlap($guideId, $dep['start_date'], $dep['end_date'])) {
             // Có thể query thêm để biết trùng với tour nào nếu cần chi tiết hơn
             $_SESSION['flash_error'] = "HDV này đã có lịch đi tour khác trong khoảng thời gian này (" . 
                date('d/m', strtotime($dep['start_date'])) . " - " . date('d/m', strtotime($dep['end_date'])) . ")!";
             return $this->redirect(route('assignment.index'));
        }

        // 2. Check đã gán vào tour này chưa
        $exists = $model->builder()
            ->where('departure_id', $departureId)
            ->where('guide_id', $guideId)
            ->first();

        if ($exists) {
            $_SESSION['flash_error'] = "HDV này đã được gán vào tour rồi.";
        } else {
            try {
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

    // [POST] Xóa phân công
    public function delete(Request $req): Response
    {
        $id = (int)$req->input('assignment_id');
        if ($id > 0) {
            (new Assignment())->delete($id);
            $_SESSION['flash_success'] = "Đã hủy phân công.";
        }
        return $this->redirect(route('assignment.index'));
    }
}