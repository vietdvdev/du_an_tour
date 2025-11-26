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
use App\Models\Supplier;
use App\Support\Validator;

class TourController extends BaseController
{
    /**
     * Helper: Lấy danh sách tất cả các Danh mục Tour đang hoạt động
     */
    private function getAvailableCategories(): array
    {
        return (new Tour_category())->all();
    }

    // =========================================================================
    // CRUD CƠ BẢN (INDEX, SHOW, CREATE, STORE, EDIT, UPDATE, DELETE)
    // =========================================================================

    // [GET] Danh sách Tour
    public function index(Request $req): Response
    {
        $tourModel = new Tour();
        $categoryModel = new Tour_category();
        $categoryTable = $categoryModel->getTable();

        $ListTour = $tourModel->builder()
            ->select('tour.*', $categoryTable . '.name AS category_name')
            ->leftJoin($categoryTable, $categoryTable . '.id', '=', 'tour.category_id')
            ->orderBy('tour.id', 'DESC')
            ->get();

        return $this->render('tour/index', [
            'title' => 'Quản lý Tour',
            'ListTour' => $ListTour
        ]);
    }

    // [GET] Form tạo mới
    public function create(Request $req): Response
    {
        return $this->render('tour/create', [
            'categories' => $this->getAvailableCategories(),
            'errors' => [],
            'old' => []
        ]);
    }

    // [POST] Lưu mới Tour
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
            return $this->render('tour/create', [
                'categories' => $this->getAvailableCategories(),
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
                'state'         => 'DRAFT',
                'is_active'     => true,
            ];

            $newTourId = (new Tour())->create($insertData);

            $_SESSION['flash_success'] = "Tạo Tour <strong>{$data['name']}</strong> thành công. Tiếp tục cấu hình chi tiết.";
            return $this->redirect(route('tour.edit', ['id' => $newTourId]));
        } catch (\Throwable $e) {
            error_log("[Tour.store] Lỗi DB: " . $e->getMessage());
            $_SESSION['flash_error'] = "Lỗi hệ thống: Không thể lưu Tour.";
            return $this->render('tour/create', [
                'categories' => $this->getAvailableCategories(),
                'errors'     => ['general' => ['Lỗi: Không thể lưu dữ liệu Tour.']],
                'old'        => $data
            ]);
        }
    }

    // [GET] Xem chi tiết (Read-only)
    public function show(Request $req): Response
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $id = (int)($req->params['id'] ?? 0);

        $tour = (new Tour())->find($id);
        if (!$tour) {
            $_SESSION['flash_error'] = "Không tìm thấy Tour.";
            return $this->redirect(route('tour.index'));
        }

        return $this->render('tour/show', [
            'tour'      => $tour,
            'itinerary' => (new TourItinerary())->where('tour_id', $id),
            'prices'    => (new TourPrice())->where('tour_id', $id),
            'policy'    => (new TourPolicy())->firstWhere('tour_id', $id),
            'images'    => (new TourImage())->where('tour_id', $id),
            'suppliers' => (new TourSupplier())->where('tour_id', $id),
        ]);
    }

    // [GET] Form chỉnh sửa (Edit)
    public function edit(Request $req): Response
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $id = (int)($req->params['id'] ?? 0);

        $tour = (new Tour())->find($id);
        if (!$tour) {
            $_SESSION['flash_error'] = "Không tìm thấy Tour để chỉnh sửa.";
            return $this->redirect(route('tour.index'));
        }

        $allSuppliers = (new Supplier())->all();

        return $this->render('tour/edit', [
            'tour'          => $tour,
            'itinerary'     => (new TourItinerary())->where('tour_id', $id),
            'prices'        => (new TourPrice())->where('tour_id', $id),
            'policy'        => (new TourPolicy())->firstWhere('tour_id', $id),
            'images'        => (new TourImage())->where('tour_id', $id),
            'categories'    => $this->getAvailableCategories(),
            'allSuppliers'  => $allSuppliers,
            'errors'        => [],

            'suppliers'     => (new TourSupplier())->builder()
                ->select('tour_supplier.*, supplier.name as supplier_name, supplier.type as supplier_type')
                ->join('supplier', 'supplier.id', '=', 'tour_supplier.supplier_id')
                ->where('tour_supplier.tour_id', $id)
                ->get(),

        ]);
    }

    // [POST] Cập nhật thông tin chung
    public function update(Request $req): Response
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $id = (int)($req->params['id'] ?? 0);
        $tourModel = new Tour();

        $existingTour = $tourModel->find($id);
        if (!$existingTour) {
            $_SESSION['flash_error'] = "Tour cần cập nhật không tồn tại.";
            return $this->redirect(route('tour.index'));
        }

        $data = [
            'name'          => trim((string)$req->input('name')),
            'category_id'   => (int)($req->input('category_id')),
            'description'   => trim((string)$req->input('description')),
        ];

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

        $v = new Validator($data, $rules, $messages);

        if ($v->fails()) {
            $tour = $tourModel->find($id);
            $oldData = array_merge($tour, $data);

            return $this->render('tour/edit', [
                'tour'       => $tour,
                'errors'     => $v->errors(),
                'old'        => $oldData,
                'itinerary'  => (new TourItinerary())->where('tour_id', $id),
                'prices'     => (new TourPrice())->where('tour_id', $id),
                'policy'     => (new TourPolicy())->firstWhere('tour_id', $id),
                'suppliers'  => (new TourSupplier())->where('tour_id', $id),
                'images'     => (new TourImage())->where('tour_id', $id),
                'categories' => $this->getAvailableCategories(),
            ]);
        }

        try {
            $tourModel->update($id, $data);
            $_SESSION['flash_success'] = "Cập nhật thông tin cơ bản Tour thành công.";
        } catch (\Throwable $e) {
            error_log("[Tour.update] Lỗi DB: " . $e->getMessage());
            $_SESSION['flash_error'] = "Lỗi hệ thống: Không thể cập nhật Tour.";
        }
        return $this->redirect(route('tour.edit', ['id' => $id]));
    }

    // Trong Class TourController

        public function toggleStatus(Request $req): Response
        {
            $id = (int)($req->params['id'] ?? 0);
            
            // Lấy giá trị active từ form gửi lên (0 hoặc 1)
            $newStatus = (int)$req->input('is_active'); 

            if ($id > 0) {
                try {
                    // Cập nhật trong DB
                    (new \App\Models\Tour())->update($id, ['is_active' => $newStatus]);
                    
                    $_SESSION['flash_success'] = "Đã thay đổi trạng thái Tour thành công.";
                } catch (\Throwable $e) {
                    $_SESSION['flash_error'] = "Lỗi: Không thể cập nhật trạng thái.";
                }
            }

    // Quay lại trang danh sách
    return $this->redirect(route('tour.index'));
}

    // [POST] Xóa Tour
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

            if ($tourModel->delete($id)) {
                $_SESSION['flash_success'] = "Xóa Tour <strong>{$name}</strong> thành công.";
            } else {
                $_SESSION['flash_error'] = "Không thể xóa Tour <strong>{$name}</strong>. Lỗi xóa không xác định.";
            }
        } catch (\Throwable $e) {
            error_log("[Tour.delete] Lỗi khi xóa ID={$id}: " . $e->getMessage());
            $_SESSION['flash_error'] = "Không thể xóa Tour. Tour đang có Đợt khởi hành hoặc Booking liên quan.";
        }
        return $this->redirect(route('tour.index'));
    }

    // =========================================================================
    // XỬ LÝ LỊCH TRÌNH (ITINERARY)
    // =========================================================================

    // [POST] Cập nhật Lịch trình
    public function updateItinerary(Request $req): Response
    {
        $tourId = (int)($req->params['id'] ?? 0);
        if ($tourId <= 0) return $this->redirect(route('tour.index'));

        $rawItems = $req->input('itineraries');
        $items = is_array($rawItems) ? $rawItems : [];

        foreach ($items as $item) {
            if (empty($item['title'])) {
                $_SESSION['flash_error'] = "Có một ngày lịch trình bị thiếu tiêu đề.";
                return $this->redirect(route('tour.edit', ['id' => $tourId]) . '?tab=itinerary');
            }
        }

        try {
            $itineraryModel = new TourItinerary();
            $items = array_values($items); // Reset keys về 0,1,2... để làm day_no

            foreach ($items as $index => $data) {
                $currentId = (int)($data['id'] ?? 0);
                $saveData = [
                    'tour_id' => $tourId,
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
        } catch (\Throwable $e) {
            error_log("[Itinerary Error] " . $e->getMessage());
            $_SESSION['flash_error'] = "Lỗi hệ thống: " . $e->getMessage();
        }
        return $this->redirect(route('tour.edit', ['id' => $tourId]) . '?tab=itinerary');
    }

    // [POST] Xóa 1 dòng lịch trình
    public function deleteItineraryItem(Request $req): Response
    {
        $itineraryId = (int)($req->params['id'] ?? 0);
        $itineraryModel = new TourItinerary();
        $item = $itineraryModel->find($itineraryId);

        if (!$item) {
            $_SESSION['flash_error'] = "Mục lịch trình không tồn tại.";
            return $this->redirect(route('tour.index'));
        }

        $tourId = $item['tour_id'];

        try {
            $itineraryModel->delete($itineraryId);
            $_SESSION['flash_success'] = "Đã xóa ngày lịch trình thành công.";
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            $_SESSION['flash_error'] = "Lỗi: Không thể xóa mục này.";
        }

        return $this->redirect(route('tour.edit', ['id' => $tourId]) . '?tab=itinerary');
    }

    // =========================================================================
    // XỬ LÝ GIÁ TOUR (PRICING)
    // =========================================================================

    // [POST] Cập nhật Giá Tour
    public function updatePrice(Request $req): Response
    {
        $tourId = (int)($req->params['id'] ?? 0);
        if ($tourId <= 0) return $this->redirect(route('tour.index'));

        $rawPrices = $req->input('prices');
        $prices = is_array($rawPrices) ? $rawPrices : [];

        // Validate cơ bản
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

        // Validate overlap (trùng lặp thời gian)
        $count = count($prices);
        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                if ($prices[$i]['pax_type'] === $prices[$j]['pax_type']) {
                    if (
                        $prices[$i]['effective_from'] <= $prices[$j]['effective_to'] &&
                        $prices[$i]['effective_to'] >= $prices[$j]['effective_from']
                    ) {
                        $_SESSION['flash_error'] = "Khoảng thời gian bị trùng lặp cho loại khách " . $prices[$i]['pax_type'];
                        return $this->redirect(route('tour.edit', ['id' => $tourId]) . '?tab=price');
                    }
                }
            }
        }

        try {
            $priceModel = new TourPrice();
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

            // Xóa các giá đã bị remove khỏi form UI
            $idsToDelete = array_diff($existingIds, $submittedIds);
            foreach ($idsToDelete as $delId) {
                $priceModel->delete($delId);
            }

            $_SESSION['flash_success'] = "Cập nhật bảng giá thành công.";
        } catch (\Throwable $e) {
            error_log("[Price] " . $e->getMessage());
            $_SESSION['flash_error'] = "Lỗi hệ thống khi lưu giá.";
        }
        return $this->redirect(route('tour.edit', ['id' => $tourId]) . '?tab=price');
    }

    // =========================================================================
    // XỬ LÝ ẢNH & CÔNG BỐ (IMAGES & PUBLISH)
    // =========================================================================

    // [POST] Upload và Cập nhật hình ảnh
    public function updateImages(Request $req): Response
    {
        $tourId = (int)($req->params['id'] ?? 0);
        if ($tourId <= 0) return $this->redirect(route('tour.index'));

        // 1. Đặt ảnh bìa
        if ($req->input('set_cover_id')) {
            $coverId = (int)$req->input('set_cover_id');
            $imgModel = new TourImage();
            $allImages = $imgModel->where('tour_id', $tourId);
            foreach ($allImages as $img) {
                $imgModel->update($img['id'], ['is_cover' => 0]);
            }
            $imgModel->update($coverId, ['is_cover' => 1]);

            $_SESSION['flash_success'] = "Đã đặt ảnh bìa thành công.";
            return $this->redirect(route('tour.edit', ['id' => $tourId]) . '?tab=images');
        }

        // 2. Upload ảnh mới
        if (!empty($_FILES['images']['name'][0])) {
            $files = $_FILES['images'];
            $count = count($files['name']);
            $uploadDir = __DIR__ . '/../../public/uploads/tours/';

            if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

            $insertedCount = 0;
            $imgModel = new TourImage();

            for ($i = 0; $i < $count; $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    $tmpName = $files['tmp_name'][$i];
                    $name = basename($files['name'][$i]);
                    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

                    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) continue;

                    $newFileName = 'tour_' . $tourId . '_' . time() . '_' . $i . '.' . $ext;
                    $destPath = $uploadDir . $newFileName;

                    if (move_uploaded_file($tmpName, $destPath)) {
                        $imgModel->create([
                            'tour_id' => $tourId,
                            'url'     => '/uploads/tours/' . $newFileName,
                            'caption' => '',
                            'is_cover' => 0
                        ]);
                        $insertedCount++;
                    }
                }
            }

            if ($insertedCount > 0) {
                $_SESSION['flash_success'] = "Đã tải lên {$insertedCount} hình ảnh.";
            }
        }

        // 3. Xóa ảnh
        if ($req->input('delete_image_id')) {
            $delId = (int)$req->input('delete_image_id');
            $imgModel = new TourImage();
            $img = $imgModel->find($delId);
            if ($img) {
                $filePath = __DIR__ . '/../../public' . $img['url'];
                if (file_exists($filePath)) unlink($filePath);

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

        (new Tour())->update($tourId, ['state' => 'PUBLISHED']);

        $_SESSION['flash_success'] = "Tour đã được công bố thành công!";
        return $this->redirect(route('tour.index'));
    }

    // =========================================================================
    // XỬ LÝ CHÍNH SÁCH & NHÀ CUNG CẤP (POLICY & SUPPLIER)
    // =========================================================================

    // [POST] Cập nhật Chính sách (Hoàn/Hủy)
    public function updatePolicy(Request $req): Response
    {
        $tourId = (int)($req->params['id'] ?? 0);
        if ($tourId <= 0) return $this->redirect(route('tour.index'));

        $data = [
            'cancel_rules' => $req->input('cancel_rules'),
            'refund_rules' => $req->input('refund_rules')
        ];

        try {
            // Lưu ý: TourPolicy Model phải có protected $primaryKey = 'tour_id';
            $updated = (new TourPolicy())->update($tourId, $data);

            if ($updated) {
                $_SESSION['flash_success'] = "Cập nhật chính sách thành công.";
            } else {
                $_SESSION['flash_success'] = "Đã lưu (Không có thay đổi hoặc tạo mới).";
            }
        } catch (\Throwable $e) {
            error_log("[Policy] " . $e->getMessage());
            $_SESSION['flash_error'] = "Lỗi hệ thống khi lưu chính sách.";
        }

        return $this->redirect(route('tour.edit', ['id' => $tourId]) . '?tab=policy');
    }

    // [POST] Thêm NCC vào Tour
    public function addSupplier(Request $req): Response
    {
        $tourId     = (int)($req->params['id'] ?? 0);
        $supplierId = (int)$req->input('supplier_id');
        $role       = trim((string)$req->input('role'));

        if ($tourId <= 0 || $supplierId <= 0) {
            $_SESSION['flash_error'] = "Dữ liệu không hợp lệ.";
            return $this->redirect(route('tour.edit', ['id' => $tourId]) . '?tab=policy');
        }

        $tourSupplierModel = new TourSupplier();

        try {
            if ($tourSupplierModel->exists($tourId, $supplierId)) {
                $_SESSION['flash_error'] = "Nhà cung cấp này đã có trong tour.";
            } else {
                $tourSupplierModel->create([
                    'tour_id'     => $tourId,
                    'supplier_id' => $supplierId,
                    'role'        => $role
                ]);
                $_SESSION['flash_success'] = "Đã thêm nhà cung cấp vào tour.";
            }
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "Lỗi hệ thống: Không thể thêm nhà cung cấp.";
        }

        return $this->redirect(route('tour.edit', ['id' => $tourId]) . '?tab=policy');
    }

    // [POST] Xóa NCC khỏi Tour
    public function deleteSupplier(Request $req): Response
    {
        $tourId     = (int)($req->params['id'] ?? 0);
        $supplierId = (int)$req->input('supplier_id_to_delete');

        if ($tourId > 0 && $supplierId > 0) {
            $isDeleted = (new TourSupplier())->remove($tourId, $supplierId);

            if ($isDeleted) {
                $_SESSION['flash_success'] = "Đã gỡ bỏ nhà cung cấp khỏi tour.";
            } else {
                $_SESSION['flash_error'] = "Không tìm thấy dữ liệu để xóa.";
            }
        }

        return $this->redirect(route('tour.edit', ['id' => $tourId]) . '?tab=policy');
    }
}
