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


    // [U] Cập nhật Chi tiết Tour (Placeholder cho các hàm update phức tạp)
    // Các hàm này cần được viết riêng biệt (updateItinerary, updatePrice, updatePolicy,...)


    

public function updateItinerary(Request $req): Response
{

    return $this->redirect(route('tour.edit'));
}



    // Xử lý Công bố Tour (PUBLISH) - Bước 3/4

    // public function publish(Request $req): Response
    // {
    //     if (session_status() === PHP_SESSION_NONE) session_start();
        
    //     $id = (int)($req->params['id'] ?? 0);
    //     $tourModel = new Tour();
    //     $tour = $tourModel->find($id);

    //     if (!$tour || $tour['state'] !== 'DRAFT') {
    //         $_SESSION['flash_error'] = "Tour không hợp lệ hoặc đã được công bố.";
    //         return $this->redirect(route('tour.edit', ['id' => $id]));
    //     }

    //     $imageModel = new TourImage();
    //     $itineraryModel = new TourItinerary();

    //     // LOGIC BẮT BUỘC 1: Kiểm tra có ảnh bìa hay không
    //     // Giả định TourImage Model có phương thức hasCoverImage(tourId)
    //     if (!method_exists($imageModel, 'hasCoverImage') || !$imageModel->hasCoverImage($id)) {
    //         $_SESSION['flash_error'] = "Không thể công bố. Tour phải có ít nhất một ảnh bìa (is_cover=TRUE).";
    //         return $this->redirect(route('tour.edit', ['id' => $id]));
    //     }
        
    //     // LOGIC BẮT BUỘC 2: Kiểm tra Tour có lịch trình hay không (ít nhất 1 ngày)
    //     $itineraryCount = $itineraryModel->builder()->where('tour_id', $id)->count(); 
    //     if ($itineraryCount === 0) {
    //         $_SESSION['flash_error'] = "Không thể công bố. Tour phải có lịch trình chi tiết.";
    //         return $this->redirect(route('tour.edit', ['id' => $id]));
    //     }

    //     try {
    //         // Cập nhật state từ DRAFT sang PUBLISHED
    //         $tourModel->update($id, ['state' => 'PUBLISHED']);

    //         $_SESSION['flash_success'] = "Tour <strong>{$tour['name']}</strong> đã được công bố thành công!";
    //         return $this->redirect(route('tour.index'));

    //     } catch (\Throwable $e) {
    //         error_log("[Tour.publish] Lỗi DB: " . $e->getMessage());
    //         $_SESSION['flash_error'] = "Lỗi hệ thống khi công bố Tour.";
    //         return $this->redirect(route('tour.edit', ['id' => $id]));
    //     }
    // }

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
    
}