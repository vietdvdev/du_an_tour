<?php
namespace App\Controllers;

// Import các lớp cần thiết
use App\Core\Request;
use App\Core\Response;
use App\Support\Validator; // Lớp hỗ trợ kiểm tra dữ liệu
use App\Models\Departure; // Model cho Đợt khởi hành
use App\Models\Tour; // Model cho Tour

/**
 * Class DepartureController
 * Xử lý các nghiệp vụ liên quan đến quản lý Đợt Khởi Hành (Tour Departure).
 */
class DepartureController extends BaseController
{
    /**
     * [GET] Hiển thị danh sách Đợt Khởi Hành & Theo dõi chỗ trống.
     * @param Request $req
     * @return Response
     */
    public function index(Request $req): Response
    {
        // Giả định Model Departure có phương thức lấy tất cả đợt khởi hành kèm theo số liệu thống kê (chỗ đã bán)
        $list = (new Departure())->getAllWithStats();
        
        return $this->render('departure/index', [
            'title' => 'Quản lý Đợt Khởi Hành',
            'departures' => $list
        ]);
    }

    /**
     * [GET] Hiển thị Form tạo mới Đợt Khởi Hành.
     * @param Request $req
     * @return Response
     */
    public function create(Request $req): Response
    {
        // Lấy danh sách Tour đang hoạt động để người dùng chọn
        $tours = (new Tour())->builder()
            ->where('is_active', 1) // Chỉ lấy tour đang hoạt động
            ->where('is_custom', 0) // Lọc: Chỉ lấy tour thường (Standard)
            ->get();

        return $this->render('departure/create', [
            'tours'  => $tours,
            'errors' => [],
            'old'    => [] // Dữ liệu cũ để điền lại form nếu có lỗi
        ]);
    }

    /**
     * [POST] Xử lý lưu Đợt Khởi Hành mới.
     * @param Request $req
     * @return Response
     */
    public function store(Request $req): Response
    {
        // Gom dữ liệu từ input
        $data = [
            'tour_id'      => (int)$req->input('tour_id'),
            'start_date'   => $req->input('start_date'),
            'end_date'     => $req->input('end_date'),
            'capacity'     => (int)$req->input('capacity'), // Sức chứa tối đa
            'pickup_point' => trim((string)$req->input('pickup_point')),
            'note'         => trim((string)$req->input('note')),
            'status'       => 'OPEN' // Mặc định trạng thái mở
        ];

        // 1. Validate dữ liệu đầu vào cơ bản
        $rules = [
            'tour_id'    => 'required|exists:tour,id', // Tour phải tồn tại trong bảng tour
            'start_date' => 'required|date',
            'end_date'   => 'required|date',
            'capacity'   => 'required|numeric|min:1'
        ];
        
        $v = new Validator($data, $rules, []);
        if ($v->fails()) {
            // Nếu validate thất bại, trả về form kèm lỗi
            return $this->render('departure/create', [
                'tours' => (new Tour())->where('is_active', 1),
                'errors' => $v->errors(), 'old' => $data
            ]);
        }

        // 2. CHECK RULE: Ngày về (end_date) phải lớn hơn hoặc bằng Ngày đi (start_date)
        if (strtotime($data['end_date']) < strtotime($data['start_date'])) {
            $_SESSION['flash_error'] = "Ngày về phải lớn hơn hoặc bằng ngày đi.";
            return $this->render('departure/create', [
                'tours' => (new Tour())->where('is_active', 1),
                'errors' => ['end_date' => ['Ngày về không hợp lệ']], 'old' => $data
            ]);
        }

        // 3. CHECK RULE: Kiểm tra trùng lặp (Tour + Ngày đi)
        // Ngăn chặn tạo hai đợt khởi hành cho cùng một Tour trong cùng một ngày
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

        // 4. Lưu dữ liệu
        try {
            (new Departure())->create($data);
            $_SESSION['flash_success'] = "Tạo đợt khởi hành thành công.";
            return $this->redirect(route('departure.index'));
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "Lỗi hệ thống: " . $e->getMessage();
            return $this->redirect(route('departure.create'));
        }
    }

    /**
     * [GET] Hiển thị Form sửa thông tin Đợt Khởi Hành.
     * @param Request $req
     * @return Response
     */
    public function edit(Request $req): Response
    {
        $id = (int)($req->params['id'] ?? 0);
        $departure = (new Departure())->find($id);

        if (!$departure) {
            $_SESSION['flash_error'] = "Không tìm thấy đợt khởi hành.";
            return $this->redirect(route('departure.index'));
        }
        
        // Lấy thông tin tour để hiển thị
        $tour = (new Tour())->find($departure['tour_id']);

        // Lấy số chỗ đã bán để hiển thị cảnh báo/kiểm tra khi cập nhật capacity
        $soldSeats = (new Departure())->getSoldSeats($id); 

        return $this->render('departure/edit', [
            'departure' => $departure,
            'tour' => $tour,
            'sold_seats' => $soldSeats, 
            'errors' => []
        ]);
    }

    /**
     * [POST] Xử lý cập nhật thông tin Đợt Khởi Hành.
     * @param Request $req
     * @return Response
     */
    public function update(Request $req): Response
    {
        $id = (int)($req->params['id'] ?? 0);
        $model = new Departure();
        $oldDeparture = $model->find($id);

        if (!$oldDeparture) return $this->redirect(route('departure.index'));

        // Gom dữ liệu cần cập nhật
        $data = [
            'start_date'   => $req->input('start_date'),
            'end_date'     => $req->input('end_date'),
            'capacity'     => (int)$req->input('capacity'),
            'pickup_point' => trim((string)$req->input('pickup_point')),
            'status'       => $req->input('status'), // Trạng thái: OPEN, CLOSED, FULL, etc.
            'note'         => trim((string)$req->input('note')),
        ];

        // 1. CHECK RULE: EndDate >= StartDate
        if (strtotime($data['end_date']) < strtotime($data['start_date'])) {
            $_SESSION['flash_error'] = "Ngày về phải lớn hơn hoặc bằng ngày đi.";
            return $this->redirect(route('departure.edit', ['id' => $id]));
        }

        // 2. CHECK RULE QUAN TRỌNG: Không giảm capacity xuống thấp hơn số chỗ đã bán
        $soldSeats = $model->getSoldSeats($id); // Lấy số chỗ đã bán thực tế
        
        if ($data['capacity'] < $soldSeats) {
            $_SESSION['flash_error'] = "Không thể giảm số chỗ xuống <b>{$data['capacity']}</b> vì đã bán <b>{$soldSeats}</b> chỗ.";
            return $this->redirect(route('departure.edit', ['id' => $id]));
        }

        // 3. Lưu cập nhật
        try {
            $model->update($id, $data);
            $_SESSION['flash_success'] = "Cập nhật đợt khởi hành thành công.";
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "Lỗi cập nhật: " . $e->getMessage();
        }

        return $this->redirect(route('departure.index'));
    }
}