<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Support\Validator;
use App\Models\Departure;
use App\Models\Tour;

class DepartureController extends BaseController
{
    // [GET] Danh sách & Theo dõi chỗ trống
    public function index(Request $req): Response
    {
        $list = (new Departure())->getAllWithStats();
        
        return $this->render('departure/index', [
            'title' => 'Quản lý Đợt Khởi Hành',
            'departures' => $list
        ]);
    }

    // [GET] Form tạo mới
    public function create(Request $req): Response
    {
        // Lấy danh sách Tour đang hoạt động để chọn
        $tours = (new Tour())->where('is_active', 1);

        return $this->render('departure/create', [
            'tours' => $tours,
            'errors' => [],
            'old' => []
        ]);
    }

    // [POST] Lưu đợt mới
    public function store(Request $req): Response
    {
        $data = [
            'tour_id'      => (int)$req->input('tour_id'),
            'start_date'   => $req->input('start_date'),
            'end_date'     => $req->input('end_date'),
            'capacity'     => (int)$req->input('capacity'),
            'pickup_point' => trim((string)$req->input('pickup_point')),
            'note'         => trim((string)$req->input('note')),
            'status'       => 'OPEN'
        ];

        // 1. Validate dữ liệu đầu vào
        $rules = [
            'tour_id'    => 'required|exists:tour,id',
            'start_date' => 'required|date',
            'end_date'   => 'required|date', // Sẽ check logic date sau
            'capacity'   => 'required|numeric|min:1'
        ];
        
        // (Bỏ qua phần Validator setup chi tiết để tập trung vào logic)
        $v = new Validator($data, $rules, []);
        if ($v->fails()) {
            return $this->render('departure/create', [
                'tours' => (new Tour())->where('is_active', 1),
                'errors' => $v->errors(), 'old' => $data
            ]);
        }

        // 2. CHECK RULE: EndDate >= StartDate
        if (strtotime($data['end_date']) < strtotime($data['start_date'])) {
            $_SESSION['flash_error'] = "Ngày về phải lớn hơn hoặc bằng ngày đi.";
            return $this->render('departure/create', [
                'tours' => (new Tour())->where('is_active', 1),
                'errors' => ['end_date' => ['Ngày về không hợp lệ']], 'old' => $data
            ]);
        }

        // 3. CHECK RULE: Không trùng lặp (Tour + Ngày đi) - Dựa vào Unique Key DB
        // Tốt nhất nên check code trước để báo lỗi thân thiện
        $exists = (new Departure())->builder()
            ->where('tour_id', $data['tour_id'])
            ->where('start_date', $data['start_date'])
            ->first();
            
        if ($exists) {
            $_SESSION['flash_error'] = "Tour này đã có đợt khởi hành vào ngày " . $data['start_date'];
            return $this->render('departure/create', [
                'tours' => (new Tour())->where('is_active', 1),
                'errors' => [], 'old' => $data
            ]);
        }

        // 4. Lưu
        try {
            (new Departure())->create($data);
            $_SESSION['flash_success'] = "Tạo đợt khởi hành thành công.";
            return $this->redirect(route('departure.index'));
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "Lỗi hệ thống: " . $e->getMessage();
            return $this->redirect(route('departure.create'));
        }
    }

    // [GET] Form sửa
    public function edit(Request $req): Response
    {
        $id = (int)($req->params['id'] ?? 0);
        $departure = (new Departure())->find($id);

        if (!$departure) {
            $_SESSION['flash_error'] = "Không tìm thấy đợt khởi hành.";
            return $this->redirect(route('departure.index'));
        }
        
        // Lấy thông tin tour hiện tại để hiển thị tên (thường không cho đổi tour khi đã tạo)
        $tour = (new Tour())->find($departure['tour_id']);

        return $this->render('departure/edit', [
            'departure' => $departure,
            'tour' => $tour,
            'sold_seats' => (new Departure())->getSoldSeats($id), // Để hiển thị cảnh báo nếu cần
            'errors' => []
        ]);
    }

    // [POST] Cập nhật
    public function update(Request $req): Response
    {
        $id = (int)($req->params['id'] ?? 0);
        $model = new Departure();
        $oldDeparture = $model->find($id);

        if (!$oldDeparture) return $this->redirect(route('departure.index'));

        $data = [
            'start_date'   => $req->input('start_date'),
            'end_date'     => $req->input('end_date'),
            'capacity'     => (int)$req->input('capacity'),
            'pickup_point' => trim((string)$req->input('pickup_point')),
            'status'       => $req->input('status'), // OPEN, CLOSED, etc.
            'note'         => trim((string)$req->input('note')),
        ];

        // 1. CHECK RULE: EndDate >= StartDate
        if (strtotime($data['end_date']) < strtotime($data['start_date'])) {
            $_SESSION['flash_error'] = "Ngày về phải lớn hơn hoặc bằng ngày đi.";
            return $this->redirect(route('departure.edit', ['id' => $id]));
        }

        // 2. CHECK RULE QUAN TRỌNG: Không giảm capacity < số đã bán
        $soldSeats = $model->getSoldSeats($id);
        
        if ($data['capacity'] < $soldSeats) {
            $_SESSION['flash_error'] = "Không thể giảm số chỗ xuống <b>{$data['capacity']}</b> vì đã bán <b>{$soldSeats}</b> chỗ.";
            return $this->redirect(route('departure.edit', ['id' => $id]));
        }

        // 3. Lưu
        try {
            $model->update($id, $data);
            $_SESSION['flash_success'] = "Cập nhật đợt khởi hành thành công.";
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "Lỗi cập nhật: " . $e->getMessage();
        }

        return $this->redirect(route('departure.index'));
    }
}