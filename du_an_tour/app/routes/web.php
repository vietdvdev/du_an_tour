<?php
use App\Core\Router;
use App\Controllers\HomeController;
use App\Controllers\UserController;
use App\Controllers\DanhMucController;
use App\Controllers\SupplierController;
use App\Controllers\TourController;
use App\Middleware\CsrfMiddleware;
use App\Middleware\ExampleMiddleware;

/** @var \App\Core\Router $router */

$router->get('/', [HomeController::class, 'index'])->name('home.index');




// Danh sách admin
$router->get('/list-admin', [UserController::class, 'index'])->name('admin.index');
// Hiển thị form thêm người dùng
$router->get('/admin/create', [UserController::class, 'create'])->name('admin.create');
$router->post('/admin/store', [UserController::class, 'store'])->name('admin.store');
// Form chỉnh sửa người dùng
$router->get('/admin/edit/{id}', [UserController::class, 'edit'])->name('admin.edit');
// Xử lý cập nhật người dùng
$router->post('/admin/update/{id}', [UserController::class, 'update'])->name('admin.update');
// Xóa người dùng (nếu cần)
$router->get('/admin/delete/{id}', [UserController::class, 'delete'])->name('admin.delete');


// Danh sách Danh mục
$router->get('/list-danh-muc', [DanhMucController::class, 'index'])->name('danhMuc.index');
// Hiển thị form thêm Danh mục
$router->get('/danh-muc/create', [DanhMucController::class, 'create'])->name('danhMuc.create');
$router->post('/danh-muc/store', [DanhMucController::class, 'store'])->name('danhMuc.store');
// Form chỉnh sửa Danh mục
$router->get('/danh-muc/edit/{id}', [DanhMucController::class, 'edit'])->name('danhMuc.edit');
// Xử lý cập nhật Danh mục
$router->post('/danh-muc/update/{id}', [DanhMucController::class, 'update'])->name('danhMuc.update');
$router->post('/danh-muc/update/active/{id}', [DanhMucController::class, 'updateActive'])->name('danhMuc.update.active');
// Xóa Danh mục (nếu cần)
$router->get('/danh-muc/delete/{id}', [DanhMucController::class, 'delete'])->name('danhMuc.delete');




/* Danh sách supplier */
$router->get('/supplier', [SupplierController::class, 'index'])->name('supplier.index');
/* Form thêm */
$router->get('/supplier/create', [SupplierController::class, 'create'])->name('supplier.create');
/* Xử lý thêm */
$router->post('/supplier/store', [SupplierController::class, 'store'])->name('supplier.store');
/* Form sửa */
$router->get('/supplier/edit/{id}', [SupplierController::class, 'edit'])->name('supplier.edit');
/* Xử lý sửa */
$router->post('/supplier/update/{id}', [SupplierController::class, 'update'])->name('supplier.update');
/* Xóa */
$router->post('/supplier/delete/{id}', [SupplierController::class, 'delete'])->name('supplier.delete');

/* Toggle active (Hiện / Ẩn) */
$router->post('/supplier/update/active/{id}', [SupplierController::class, 'updateActive'])->name('supplier.update.active');





// ==========================================================
// QUẢN LÝ TOUR (TOUR)
// ==========================================================

// [R] Đọc: Danh sách Tour (Tổng quan)
$router->get('/tour', [TourController::class, 'index'])->name('tour.index');

// [C] Tạo: Hiển thị form tạo Tour cơ bản
$router->get('/tour/create', [TourController::class, 'create'])->name('tour.create');

// [C] Tạo: Xử lý lưu Tour cơ bản và chuyển sang bước tiếp theo
$router->post('/tour/store', [TourController::class, 'store'])->name('tour.store');

// [R] XEM CHI TIẾT: Hiển thị trang chi tiết công khai/chỉ đọc (SHOW)
$router->get('/tour/show/{id}', [TourController::class, 'show'])->name('tour.show'); // <--- ROUTE MỚI

// [U] Sửa/Xem Cấu hình: Hiển thị trang cấu hình chi tiết Tour (EDIT)
$router->get('/tour/edit/{id}', [TourController::class, 'edit'])->name('tour.edit');


// --- Routes Cập nhật Chi tiết (Các Tab trong trang Edit) ---

// [U] Cập nhật: Xử lý cập nhật thông tin chung (code, name, category_id, description)
$router->post('/tour/update/{id}', [TourController::class, 'update'])->name('tour.update');

// [U] Cập nhật: Cấu hình Lịch trình (tour_itinerary)
$router->post('/tour/update/itinerary/{id}', [TourController::class, 'updateItinerary'])->name('tour.update.itinerary');

// [U] Cập nhật: Cấu hình Giá (tour_price)
$router->post('/tour/update/price/{id}', [TourController::class, 'updatePrice'])->name('tour.update.price');

// [U] Cập nhật: Cấu hình Chính sách (tour_policy)
$router->post('/tour/update/policy/{id}', [TourController::class, 'updatePolicy'])->name('tour.update.policy');

// [U] Cập nhật: Gán Nhà cung cấp (tour_supplier)
$router->post('/tour/update/suppliers/{id}', [TourController::class, 'updateSuppliers'])->name('tour.update.suppliers');

// [U] Cập nhật: Quản lý Hình ảnh (tour_image)
$router->post('/tour/update/images/{id}', [TourController::class, 'updateImages'])->name('tour.update.images');


// --- Routes Nghiệp vụ Tour ---

// [U] Công bố Tour: Kiểm tra điều kiện và chuyển trạng thái state = 'PUBLISHED'
$router->post('/tour/publish/{id}', [TourController::class, 'publish'])->name('tour.publish');

// [D] Xóa: Xử lý xóa Tour
$router->post('/tour/delete/{id}', [TourController::class, 'delete'])->name('tour.delete');




// // Group with prefix + middleware
// $router->group(['prefix'=>'/users', 'middleware'=>[ExampleMiddleware::class]], function(Router $r){
//     $r->get('', [UserController::class, 'index']);
//     $r->get('/{id}', [UserController::class, 'show']);
//     $r->post('', [UserController::class, 'store'], [CsrfMiddleware::class]);
// });
