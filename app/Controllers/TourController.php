<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Tour;
use App\Models\Tour_category;
use App\Models\TourImage;
use App\Models\TourItinerary;
use App\Models\TourPrice;
use App\Models\TourPolicy;
use App\Models\TourSupplier;
use App\Support\Validator; 


class TourController extends BaseController
{
    /**
     * Lấy danh sách tất cả các Danh mục Tour đang hoạt động
     */
    private function getAvailableCategories(): array
    {
        return (new Tour_category())->all();
    }

    // [R] Đọc: Danh sách Tour (INDEX) - Đã sửa để dùng JOIN không alias
    public function index(Request $req): Response
    {
        $tourModel = new Tour();
        $categoryModel = new Tour_category();

        // Lấy tên bảng Category từ Model.getTable() để đảm bảo tính chính xác
        $categoryTable = $categoryModel->getTable(); 
        
        // Thực hiện JOIN để lấy tên danh mục (category_name) mà không dùng alias
        $ListTour = $tourModel->builder()
            // Dùng tên bảng đầy đủ cho cột 'name' của bảng category
            ->select('tour.*', $categoryTable . '.name AS category_name') 
            
            // Dùng tên bảng đầy đủ cho câu lệnh JOIN
            ->leftJoin($categoryTable, $categoryTable . '.id', '=', 'tour.category_id') 
            
            ->orderBy('tour.id', 'DESC')
            ->get();
            
        return $this->render('tour/index', [
            'title' => 'Quản lý Tour',
            'ListTour' => $ListTour
        ]);
    }
    
    // GET /tour/create: Hiển thị form tạo Tour cơ bản
    public function create(Request $req): Response
    {
        // Load danh mục để đổ vào form
        $categories = $this->getAvailableCategories();
        
        return $this->render('tour/create', [
            'categories' => $categories,
            'errors' => [], 
            'old' => []
        ]);
    }

    // POST /tour/store (Bước 1/4: Tạo Tour Cốt Lõi)
    public function store(Request $req): Response
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        $data = [
            'code'          => trim((string)$req->input('code')),
            'name'          => trim((string)$req->input('name')),
            'category_id'   => (int)($req->input('category_id')),
            'description'   => trim((string)$req->input('description')),
        ];

        $rules = [
            'code'          => 'required|max:50|unique:tour,code', 
            'name'          => 'required|max:255',
            'category_id'   => 'required|exists:tour_category,id', 
            'description'   => 'nullable|max:5000',
        ];

        $messages = [
            'code.required'     => 'Vui lòng nhập Mã Tour.',
            'code.unique'       => 'Mã Tour này đã tồn tại.',
            'name.required'     => 'Vui lòng nhập Tên Tour.',
            'category_id.required' => 'Vui lòng chọn Danh mục Tour.',
            'category_id.exists'   => 'Danh mục được chọn không hợp lệ.',
        ];

        $v = new Validator($data, $rules, $messages);

        if ($v->fails()) {
            $categories = $this->getAvailableCategories();
            return $this->render('tour/create', [
                'categories' => $categories,
                'errors'     => $v->errors(),
                'old'        => $data
            ]);
        }

        try {
            $insertData = [
                'code'          => $data['code'],
                'name'          => $data['name'],
                'category_id'   => $data['category_id'],
                'description'   => $data['description'],
                'state'         => 'DRAFT', // LOGIC BẮT BUỘC
                'is_active'     => true,
            ];
            
            $newTourId = (new Tour())->create($insertData); 

            $_SESSION['flash_success'] = "Tạo Tour <strong>{$data['name']}</strong> thành công. Tiếp tục cấu hình chi tiết.";
            
            return $this->redirect(route('tour.edit', ['id' => $newTourId])); 
            
        } catch (\Throwable $e) {
            error_log("[Tour.store] Lỗi DB: " . $e->getMessage());
            $categories = $this->getAvailableCategories();
            $_SESSION['flash_error'] = "Lỗi hệ thống: Không thể lưu Tour. Vui lòng kiểm tra log.";
            return $this->render('tour/create', [
                'categories' => $categories,
                'errors'     => ['general' => ['Lỗi: Không thể lưu dữ liệu Tour.']],
                'old'        => $data
            ]);
        }
    }
    
    // [R] Xem Chi tiết: Hiển thị trang chỉ đọc (SHOW)
    // Route: /tour/show/{id}
    public function show(Request $req): Response
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        $id = (int)($req->params['id'] ?? 0); 

        // 1. Kiểm tra Tour tồn tại
        $tour = (new Tour())->find($id);
        if (!$tour) {
            $_SESSION['flash_error'] = "Không tìm thấy Tour.";
            return $this->redirect(route('tour.index'));
        }
        
        // 2. Tải dữ liệu chi tiết cho View
        $itinerary = (new TourItinerary())->where('tour_id', $id);
        $prices = (new TourPrice())->where('tour_id', $id);
        $policy = (new TourPolicy())->firstWhere('tour_id', $id);
        $images = (new TourImage())->where('tour_id', $id);
        $suppliers = (new TourSupplier())->where('tour_id', $id); // Cần tải cho màn hình show
        
        // Có thể cần JOIN thêm Category Name nếu cần (nhưng ta đã xử lý nó trong index)

        // 3. Render View chỉ đọc (tour/show)
        return $this->render('tour/show', [
            'tour' => $tour,
            'itinerary' => $itinerary,
            'prices' => $prices,
            'policy' => $policy,
            'images' => $images,
            'suppliers' => $suppliers,
        ]);
    }

    // [U] Cập nhật: Hiển thị trang cấu hình chi tiết Tour (EDIT)
    public function edit(Request $req): Response
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // Manual lookup ID từ params
        $id = (int)($req->params['id'] ?? 0); 

        // 1. Kiểm tra Tour tồn tại
        $tour = (new Tour())->find($id);
        if (!$tour) {
            $_SESSION['flash_error'] = "Không tìm thấy Tour để chỉnh sửa.";
            return $this->redirect(route('tour.index'));
        }

        // 2. Tải dữ liệu chi tiết cho các tab
        $itinerary = (new TourItinerary())->where('tour_id', $id);
        $prices = (new TourPrice())->where('tour_id', $id);
        $policy = (new TourPolicy())->firstWhere('tour_id', $id);
        $suppliers = (new TourSupplier())->where('tour_id', $id);
        $images = (new TourImage())->where('tour_id', $id);

        // 3. Render View tổng hợp (multi-tab view)
        return $this->render('tour/edit', [
            'tour' => $tour,
            'itinerary' => $itinerary,
            'prices' => $prices,
            'policy' => $policy,
            'suppliers' => $suppliers,
            'images' => $images,
            'categories' => $this->getAvailableCategories(),
            'errors' => [],
        ]);
    }

    // [U] Cập nhật: Xử lý cập nhật thông tin chung (UPDATE)
    public function update(Request $req): Response
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        $id = (int)($req->params['id'] ?? 0);
        $tourModel = new Tour();

        // Kiểm tra Tour tồn tại (cho trường hợp URL bị chỉnh sửa)
        $existingTour = $tourModel->find($id);
        if (!$existingTour) {
            $_SESSION['flash_error'] = "Tour cần cập nhật không tồn tại.";
            return $this->redirect(route('tour.index'));
        }

        // 1. Gom dữ liệu
        $data = [
            'name'          => trim((string)$req->input('name')),
            'category_id'   => (int)($req->input('category_id')),
            'description'   => trim((string)$req->input('description')),
        ];
        
        // 2. Định nghĩa Rule
        $rules = [
            'name'          => 'required|max:255',
            'category_id'   => 'required|exists:tour_category,id', 
            'description'   => 'nullable|max:5000',
        ];

        $messages = [
            'name.required'     => 'Vui lòng nhập Tên Tour.',
            'category_id.required' => 'Vui lòng chọn Danh mục Tour.',
            'category_id.exists'   => 'Danh mục được chọn không hợp lệ.',
        ];

        // 4. Validate
        $v = new Validator($data, $rules, $messages);

        if ($v->fails()) {
            // Trả về lại trang edit, truyền data qua $old và $errors
            $tour = $tourModel->find($id); // Lấy lại tour hiện tại để fill các trường khác
            $oldData = array_merge($tour, $data); // Gộp dữ liệu cũ
            
            return $this->render('tour/edit', [
                'tour' => $tour,
                'errors' => $v->errors(),
                'old' => $oldData,
                // Tải lại các dữ liệu chi tiết khác cho các tab
                'itinerary' => (new TourItinerary())->where('tour_id', $id),
                'prices' => (new TourPrice())->where('tour_id', $id),
                'policy' => (new TourPolicy())->firstWhere('tour_id', $id),
                'suppliers' => (new TourSupplier())->where('tour_id', $id),
                'images' => (new TourImage())->where('tour_id', $id),
                'categories' => $this->getAvailableCategories(),
            ]);
        }
        
        // 5. Lưu vào DB
        try {
            $tourModel->update($id, $data);
            $_SESSION['flash_success'] = "Cập nhật thông tin cơ bản Tour thành công.";
            return $this->redirect(route('tour.edit', ['id' => $id])); 
        } catch (\Throwable $e) {
            error_log("[Tour.update] Lỗi DB: " . $e->getMessage());
            $_SESSION['flash_error'] = "Lỗi hệ thống: Không thể cập nhật Tour.";
            return $this->redirect(route('tour.edit', ['id' => $id]));
        }
    }




    // [D] Xóa: Xử lý xóa Tour (DELETE)
    public function delete(Request $req): Response
    {
      
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        $id = (int)($req->params['id'] ?? 0);

        if ($id <= 0) {
            $_SESSION['flash_error'] = "ID Tour không hợp lệ.";
            return $this->redirect(route('tour.index'));
        }

        try {
            $tourModel = new Tour();
            $tour = $tourModel->find($id);

            if (!$tour) {
                $_SESSION['flash_error'] = "Không tìm thấy Tour để xóa.";
                return $this->redirect(route('tour.index'));
            }

            $name = $tour['name'] ?? 'ID ' . $id;
            
            // Thực hiện xóa. Các bảng con sẽ tự động xóa nhờ ON DELETE CASCADE.
            if ($tourModel->delete($id)) {
                $_SESSION['flash_success'] = "Xóa Tour <strong>{$name}</strong> thành công.";
            } else {
                $_SESSION['flash_error'] = "Không thể xóa Tour <strong>{$name}</strong>. Lỗi xóa không xác định.";
            }

            return $this->redirect(route('tour.index'));
            
        } catch (\Throwable $e) {
            error_log("[Tour.delete] Lỗi khi xóa ID={$id}: " . $e->getMessage());
            // Lỗi xảy ra nếu Tour có Departure/Booking (vì các bảng này có RESTRICT/CASCADE)
            $_SESSION['flash_error'] = "Không thể xóa Tour. Tour đang có Đợt khởi hành hoặc Booking liên quan.";
            return $this->redirect(route('tour.index'));
        }
    }




    // [POST] Cập nhật Lịch trình
   // [POST] Cập nhật Lịch trình (Đã sửa lỗi Xóa)
   public function updateItinerary(Request $req): Response
    {
        $tourId = (int)($req->params['id'] ?? 0);
        if ($tourId <= 0) return $this->redirect(route('tour.index'));

        // 1. Lấy dữ liệu từ Form
        $rawItems = $req->input('itineraries'); 
        $items = is_array($rawItems) ? $rawItems : [];

        // 2. Validate cơ bản
        // Lưu ý: Khi dùng JS thêm mới, index có thể lộn xộn, nên dùng array_values để duyệt cho dễ nếu cần
        foreach ($items as $item) {
            if (empty($item['title'])) {
                $_SESSION['flash_error'] = "Có một ngày lịch trình bị thiếu tiêu đề.";
                return $this->redirect(route('tour.edit', ['id' => $tourId]) . '?tab=itinerary');
            }
        }

        try {
            $itineraryModel = new TourItinerary();
            
            // ==========================================================
            // [FIX LỖI OUT OF RANGE]
            // Reset lại key của mảng về 0, 1, 2... để day_no luôn đúng thứ tự
            // Bất kể JS gửi lên key là timestamp (số lớn) hay gì đi nữa.
            // ==========================================================
            $items = array_values($items); 

            // 3. Xử lý Thêm mới hoặc Cập nhật
            foreach ($items as $index => $data) {
                $currentId = (int)($data['id'] ?? 0);
                
                $saveData = [
                    'tour_id' => $tourId,
                    // $index bây giờ sẽ là 0, 1, 2... do hàm array_values() ở trên
                    'day_no'  => $index + 1, 
                    'title'   => trim($data['title']),
                    'content' => trim($data['content'] ?? ''),
                ];

                if ($currentId > 0) {
                    $itineraryModel->update($currentId, $saveData);
                } else {
                    $itineraryModel->create($saveData);
                }
            }

            $_SESSION['flash_success'] = "Cập nhật lịch trình thành công.";
            return $this->redirect(route('tour.edit', ['id' => $tourId]) . '?tab=itinerary');

        } catch (\Throwable $e) {
            error_log("[Itinerary Error] " . $e->getMessage());
            $_SESSION['flash_error'] = "Lỗi hệ thống: " . $e->getMessage();
            return $this->redirect(route('tour.edit', ['id' => $tourId]) . '?tab=itinerary');
        }
    }
   /**
     * [POST] Xóa 1 dòng lịch trình cụ thể và quay lại trang sửa
     */
    public function deleteItineraryItem(Request $req): Response
    {
        // 1. Lấy ID lịch trình cần xóa và ID Tour (để quay lại)
        $itineraryId = (int)($req->params['id'] ?? 0);
        
        // Lấy tour_id từ URL params hoặc query string nếu có, 
        // NHƯNG cách tốt nhất là query từ DB xem item này thuộc tour nào trước khi xóa.
        
        $itineraryModel = new TourItinerary();
        $item = $itineraryModel->find($itineraryId);

        if (!$item) {
            $_SESSION['flash_error'] = "Mục lịch trình không tồn tại.";
            // Nếu không tìm thấy item, ta cần fallback về trang danh sách hoặc trang edit (nếu biết ID tour)
            // Ở đây tạm về danh sách tour
            return $this->redirect(route('tour.index'));
        }

        $tourId = $item['tour_id']; // Lưu lại ID tour để redirect

        // 2. Thực hiện xóa
        try {
            $itineraryModel->delete($itineraryId);
            $_SESSION['flash_success'] = "Đã xóa ngày lịch trình thành công.";
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            $_SESSION['flash_error'] = "Lỗi: Không thể xóa mục này.";
        }

        // 3. Quay lại đúng Tab Lịch trình
        return $this->redirect(route('tour.edit', ['id' => $tourId]) . '?tab=itinerary');
    }

// [POST] Cập nhật Giá Tour
    public function updatePrice(Request $req): Response
    {
        $tourId = (int)($req->params['id'] ?? 0);
        if ($tourId <= 0) return $this->redirect(route('tour.index'));

        $rawPrices = $req->input('prices');
        $prices = is_array($rawPrices) ? $rawPrices : [];

        // 1. Validate logic nghiệp vụ (Ngày và Giá)
        foreach ($prices as $index => $p) {
            if ($p['base_price'] < 0) {
                $_SESSION['flash_error'] = "Giá tiền không hợp lệ ở dòng " . ($index + 1);
                return $this->redirect(route('tour.edit', ['id' => $tourId]) . '?tab=price');
            }
            if (strtotime($p['effective_from']) > strtotime($p['effective_to'])) {
                $_SESSION['flash_error'] = "Ngày kết thúc phải lớn hơn ngày bắt đầu (Dòng " . ($index + 1) . ")";
                return $this->redirect(route('tour.edit', ['id' => $tourId]) . '?tab=price');
            }
        }

        // 2. Kiểm tra trùng lặp khoảng thời gian (Overlap Check)
        // Logic: (StartA <= EndB) and (EndA >= StartB)
        // Kiểm tra nội bộ danh sách gửi lên
        $count = count($prices);
        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                if ($prices[$i]['pax_type'] === $prices[$j]['pax_type']) {
                    // Cùng loại khách, kiểm tra ngày
                    if ($prices[$i]['effective_from'] <= $prices[$j]['effective_to'] && 
                        $prices[$i]['effective_to'] >= $prices[$j]['effective_from']) {
                        $_SESSION['flash_error'] = "Khoảng thời gian bị trùng lặp cho loại khách " . $prices[$i]['pax_type'];
                        return $this->redirect(route('tour.edit', ['id' => $tourId]) . '?tab=price');
                    }
                }
            }
        }

        try {
            $priceModel = new TourPrice();
            // Lấy ID cũ để xử lý Sync (tương tự Itinerary)
            $existingRecords = $priceModel->where('tour_id', $tourId);
            $existingIds = array_column($existingRecords, 'id');
            $submittedIds = [];

            foreach ($prices as $p) {
                $id = (int)($p['id'] ?? 0);
                $saveData = [
                    'tour_id'        => $tourId,
                    'pax_type'       => $p['pax_type'],
                    'base_price'     => $p['base_price'],
                    'effective_from' => $p['effective_from'],
                    'effective_to'   => $p['effective_to']
                ];

                if ($id > 0 && in_array($id, $existingIds)) {
                    $priceModel->update($id, $saveData);
                    $submittedIds[] = $id;
                } else {
                    $priceModel->create($saveData);
                }
            }

            // Xóa các giá đã bị remove khỏi form
            $idsToDelete = array_diff($existingIds, $submittedIds);
            foreach ($idsToDelete as $delId) {
                $priceModel->delete($delId);
            }

            $_SESSION['flash_success'] = "Cập nhật bảng giá thành công.";
            return $this->redirect(route('tour.edit', ['id' => $tourId]) . '?tab=price');

        } catch (\Throwable $e) {
            error_log("[Price] " . $e->getMessage());
            $_SESSION['flash_error'] = "Lỗi hệ thống khi lưu giá.";
            return $this->redirect(route('tour.edit', ['id' => $tourId]) . '?tab=price');
        }
    }


    // [POST] Cập nhật Chính sách (Hoàn/Hủy)
    public function updatePolicy(Request $req): Response
    {
        $tourId = (int)($req->params['id'] ?? 0);
        if ($tourId <= 0) return $this->redirect(route('tour.index'));

        $cancelRules = trim((string)$req->input('cancel_rules'));
        $refundRules = trim((string)$req->input('refund_rules'));

        try {
            $policyModel = new TourPolicy();
            
            // Kiểm tra xem đã có chính sách cho tour này chưa
            // Lưu ý: Model TourPolicy cần set protected $primaryKey = 'tour_id'; để hàm find hoạt động đúng nếu dùng find($tourId)
            // Hoặc dùng where('tour_id', $tourId)->first()
            $exists = $policyModel->where('tour_id', $tourId); 
            
            $data = [
                'tour_id'      => $tourId,
                'cancel_rules' => $cancelRules,
                'refund_rules' => $refundRules
            ];

            if (!empty($exists)) {
                // Đã có -> Update (Lưu ý: update theo tour_id)
                // Nếu hàm update của bạn yêu cầu Primary Key là 'id', bạn cần cẩn thận.
                // Ở đây giả sử update($id, $data) hoạt động dựa trên PK của bảng.
                // SQL: UPDATE tour_policy SET ... WHERE tour_id = $tourId
                 $policyModel->update($tourId, $data); 
            } else {
                // Chưa có -> Create
                $policyModel->create($data);
            }

            $_SESSION['flash_success'] = "Cập nhật chính sách thành công.";
            return $this->redirect(route('tour.edit', ['id' => $tourId]) . '?tab=policy');

        } catch (\Throwable $e) {
            error_log("[Policy] " . $e->getMessage());
            $_SESSION['flash_error'] = "Lỗi hệ thống khi lưu chính sách.";
            return $this->redirect(route('tour.edit', ['id' => $tourId]) . '?tab=policy');
        }
    }


    // [POST] Upload và Cập nhật hình ảnh
    public function updateImages(Request $req): Response
    {
        $tourId = (int)($req->params['id'] ?? 0);
        if ($tourId <= 0) return $this->redirect(route('tour.index'));

        // 1. Xử lý thiết lập ảnh bìa (nếu người dùng click nút "Đặt làm ảnh bìa")
        // Giả sử form gửi lên 'set_cover_id'
        if ($req->input('set_cover_id')) {
            $coverId = (int)$req->input('set_cover_id');
            $imgModel = new TourImage();
            // Reset tất cả về 0
            // SQL Raw: UPDATE tour_image SET is_cover = 0 WHERE tour_id = ?
            // Do Model cơ bản thường không có updateWhere, ta có thể cần query raw hoặc loop.
            // Tạm thời giả định loop (không tối ưu nhưng an toàn với ORM đơn giản):
            $allImages = $imgModel->where('tour_id', $tourId);
            foreach($allImages as $img) {
                $imgModel->update($img['id'], ['is_cover' => 0]);
            }
            // Set ảnh được chọn thành 1
            $imgModel->update($coverId, ['is_cover' => 1]);
            
            $_SESSION['flash_success'] = "Đã đặt ảnh bìa thành công.";
            return $this->redirect(route('tour.edit', ['id' => $tourId]) . '?tab=images');
        }

        // 2. Xử lý Upload ảnh mới
        if (!empty($_FILES['images']['name'][0])) {
            $files = $_FILES['images'];
            $count = count($files['name']);
            $uploadDir = __DIR__ . '/../../public/uploads/tours/'; // Đường dẫn thư mục
            
            if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

            $insertedCount = 0;
            $imgModel = new TourImage();

            for ($i = 0; $i < $count; $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    $tmpName = $files['tmp_name'][$i];
                    $name = basename($files['name'][$i]);
                    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    
                    // Validate đuôi file
                    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) continue;

                    // Tạo tên file mới để tránh trùng
                    $newFileName = 'tour_' . $tourId . '_' . time() . '_' . $i . '.' . $ext;
                    $destPath = $uploadDir . $newFileName;

                    if (move_uploaded_file($tmpName, $destPath)) {
                        // Lưu DB
                        $imgModel->create([
                            'tour_id' => $tourId,
                            'url'     => '/uploads/tours/' . $newFileName,
                            'caption' => '',
                            'is_cover'=> 0
                        ]);
                        $insertedCount++;
                    }
                }
            }
            
            if ($insertedCount > 0) {
                $_SESSION['flash_success'] = "Đã tải lên {$insertedCount} hình ảnh.";
            }
        }

        // 3. Xử lý Xóa ảnh
        if ($req->input('delete_image_id')) {
            $delId = (int)$req->input('delete_image_id');
            $imgModel = new TourImage();
            $img = $imgModel->find($delId);
            if ($img) {
                // Xóa file vật lý
                $filePath = __DIR__ . '/../../public' . $img['url'];
                if (file_exists($filePath)) unlink($filePath);
                
                // Xóa DB
                $imgModel->delete($delId);
                $_SESSION['flash_success'] = "Đã xóa hình ảnh.";
            }
        }

        return $this->redirect(route('tour.edit', ['id' => $tourId]) . '?tab=images');
    }

    // [POST] Công bố Tour
    public function publish(Request $req): Response
    {
        $tourId = (int)($req->params['id'] ?? 0);
        if ($tourId <= 0) return $this->redirect(route('tour.index'));

        // 1. Kiểm tra ảnh bìa
        $imageModel = new TourImage();
        $images = $imageModel->where('tour_id', $tourId);
        
        $hasCover = false;
        foreach ($images as $img) {
            if ($img['is_cover'] == 1) {
                $hasCover = true;
                break;
            }
        }

        if (!$hasCover) {
            $_SESSION['flash_error'] = "Không thể công bố. Tour phải có ít nhất một ảnh bìa.";
            return $this->redirect(route('tour.edit', ['id' => $tourId]) . '?tab=images');
        }

        // 2. Cập nhật trạng thái
        (new Tour())->update($tourId, ['state' => 'PUBLISHED']);

        $_SESSION['flash_success'] = "Tour đã được công bố thành công!";
        return $this->redirect(route('tour.index'));
    }

    
}