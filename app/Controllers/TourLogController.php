<?php


namespace App\Controllers;


use App\Core\Request;
use App\Core\Response;
use App\Models\TourLog;
use App\Models\Assignment;
use App\Models\Departure;


class TourLogController extends BaseController
{
    /**
     * [GET] Xem danh sách nhật ký của 1 tour
     */
    public function index(Request $req): Response
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $guideId = $_SESSION['user_id'] ?? 0;
        $departureId = (int)$req->input('departure_id');


        if ($departureId <= 0) return $this->redirect(route('guide.my_tours'));


        // 1. Bảo mật: Check xem HDV có được phân công tour này không
        $isAssigned = (new Assignment())->builder()
            ->where('guide_id', $guideId)
            ->where('departure_id', $departureId)
            ->count();


        if (!$isAssigned) {
            $_SESSION['flash_error'] = "Bạn không có quyền truy cập tour này.";
            return $this->redirect(route('guide.my_tours'));
        }


        // 2. Lấy thông tin Tour
        $departure = (new Departure())->builder()
            ->select('departure.*, tour.name as tour_name, tour.code as tour_code')
            ->join('tour', 'tour.id', '=', 'departure.tour_id')
            ->where('departure.id', $departureId)
            ->first();


        // 3. Lấy danh sách Log
        $logs = (new TourLog())->builder()
            ->where('departure_id', $departureId)
            ->orderBy('log_date', 'DESC')
            // SỬA LỖI TẠI ĐÂY: Đổi 'created_at' thành 'log_time'
            ->orderBy('log_time', 'DESC')
            ->get();


        return $this->render('guide.log.index', [
            'departure' => $departure,
            'logs'      => $logs
        ]);
    }


    /**
     * [POST] Lưu nhật ký mới
     */
    public function store(Request $req): Response
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $guideId = $_SESSION['user_id'] ?? 0;
        $departureId = (int)$req->input('departure_id');


        // 1. Validate dữ liệu
        $content = trim((string)$req->input('content'));
        $logDate = $req->input('log_date');
       
        if (empty($content) || empty($logDate)) {
            $_SESSION['flash_error'] = "Vui lòng nhập ngày và nội dung.";
            return $this->redirect(route('guide.log.index') . "?departure_id=$departureId");
        }


        // 2. Xử lý Upload ảnh (Multiple)
        $uploadedFiles = [];
        if (!empty($_FILES['attachments']['name'][0])) {
            $files = $_FILES['attachments'];
            $count = count($files['name']);
            // Sửa lại đường dẫn thư mục upload cho đúng chuẩn
            $uploadDir = __DIR__ . '/../../public/uploads/logs/';


            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);


            for ($i = 0; $i < $count; $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                        $newName = "log_{$departureId}_{$guideId}_" . time() . "_$i." . $ext;
                        if (move_uploaded_file($files['tmp_name'][$i], $uploadDir . $newName)) {
                            // Lưu đường dẫn web tương đối
                            $uploadedFiles[] = '/uploads/logs/' . $newName;
                        }
                    }
                }
            }
        }


        // 3. Lưu vào DB
        try {
            (new TourLog())->create([
                'departure_id'   => $departureId,
                'guide_id'       => $guideId,
                'log_date'       => $logDate,
                'log_time'       => date('Y-m-d H:i:s'), // Cột này thay cho created_at
                'incident_level' => $req->input('incident_level') ?? 'NORMAL',
                'content'        => $content,
                'attachments'    => !empty($uploadedFiles) ? json_encode($uploadedFiles) : null
            ]);


            $_SESSION['flash_success'] = "Đã ghi nhật ký thành công.";


        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "Lỗi: " . $e->getMessage();
        }


        return $this->redirect(route('guide.log.index') . "?departure_id=$departureId");
    }
}




