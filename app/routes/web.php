<?php
use App\Core\Router;
use App\Controllers\HomeController;
use App\Controllers\UserController;
use App\Controllers\DanhMucController;
use App\Controllers\SupplierController;
use App\Controllers\TourController;
use App\Controllers\DepartureController;
use App\Controllers\BookingController;
use App\Controllers\PaymentController;
use App\Controllers\AssignmentController;
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

// 1. DANH SÁCH & XEM CHI TIẾT
// ------------------------------------------------------------------------------
// [GET] Hiển thị danh sách Tour
$router->get('/tour', [TourController::class, 'index'])->name('tour.index');

// [GET] Xem chi tiết Tour (Trang Read-only / Public view)
$router->get('/tour/show/{id}', [TourController::class, 'show'])->name('tour.show');


// 2. TẠO MỚI TOUR (BƯỚC 1)
// ------------------------------------------------------------------------------
// [GET] Hiển thị form tạo mới (chỉ nhập thông tin cơ bản)
$router->get('/tour/create', [TourController::class, 'create'])->name('tour.create');

// [POST] Xử lý lưu Tour mới -> Chuyển hướng sang trang Edit
$router->post('/tour/store', [TourController::class, 'store'])->name('tour.store');

// 3. CHỈNH SỬA TOUR & CÁC TAB CHI TIẾT
// [GET] Hiển thị giao diện chỉnh sửa tổng thể (Chứa các Tabs: Info, Itinerary, Price...)
$router->get('/tour/edit/{id}', [TourController::class, 'edit'])->name('tour.edit');
// --- TAB 1: Thông tin chung ---
// [POST] Cập nhật tên, mã, mô tả, danh mục
$router->post('/tour/update/{id}', [TourController::class, 'update'])->name('tour.update');
/* Toggle trạng thái Tour (Bật/Tắt) */
$router->post('/tour/toggle-status/{id}', [TourController::class, 'toggleStatus'])->name('tour.toggle.status');
// --- TAB 2: Lịch trình (Itinerary) ---
// [POST] Cập nhật danh sách ngày, tiêu đề, nội dung (Xử lý mảng)
$router->post('/tour/update/itinerary/{id}', [TourController::class, 'updateItinerary'])->name('tour.update.itinerary');
// [POST] Xóa một ngày lịch trình cụ thể (Gọi trực tiếp từ View)
$router->post('/tour/itinerary/delete-item/{id}', [TourController::class, 'deleteItineraryItem'])->name('tour.itinerary.delete_item');
// --- TAB 3: Bảng giá (Price) ---
// [POST] Cập nhật bảng giá theo loại khách & ngày hiệu lực
$router->post('/tour/update/price/{id}', [TourController::class, 'updatePrice'])->name('tour.update.price');

// --- TAB 4: Chính sách (Policy) ---
// [POST] Cập nhật chính sách hoàn/hủy
$router->post('/tour/update/policy/{id}', [TourController::class, 'updatePolicy'])->name('tour.update.policy');

//TAB 5: Hình ảnh (Images) 
// [POST] Upload ảnh, Xóa ảnh, Đặt ảnh bìa (Multipart form)
$router->post('/tour/update/images/{id}', [TourController::class, 'updateImages'])->name('tour.update.images');

// --- TAB 6: Nhà cung cấp (Suppliers - Nếu bạn làm chức năng gán NCC) ---
// [POST] Cập nhật danh sách nhà cung cấp cho Tour
$router->post('/tour/update/suppliers/{id}', [TourController::class, 'updateSuppliers'])->name('tour.update.suppliers');


// 4. CÁC HÀNH ĐỘNG NGHIỆP VỤ KHÁC
// ------------------------------------------------------------------------------
// [POST] Công bố Tour (Chuyển state từ DRAFT -> PUBLISHED)
$router->post('/tour/publish/{id}', [TourController::class, 'publish'])->name('tour.publish');

// [POST] Xóa Tour (Xóa mềm hoặc xóa cứng tùy logic Controller)
$router->post('/tour/delete/{id}', [TourController::class, 'delete'])->name('tour.delete');

// [POST] Ẩn/Hiện Tour nhanh (Toggle Active) - Optional
$router->post('/tour/toggle-active/{id}', [TourController::class, 'toggleActive'])->name('tour.toggle.active');

// [POST] Thêm một NCC vào Tour
$router->post('/tour/supplier/add/{id}', [TourController::class, 'addSupplier'])->name('tour.supplier.add');

// [POST] Xóa một NCC khỏi Tour
$router->post('/tour/supplier/delete/{id}', [TourController::class, 'deleteSupplier'])->name('tour.supplier.delete');


/* 1. Cập nhật chính sách (Cancel/Refund) */
// Tương ứng View: route('tour.update.policy', ['id' => $tourId])
$router->post('/tour/policy/update/{id}', [TourController::class, 'updatePolicy'])->name('tour.update.policy');

/* 2. Thêm Nhà cung cấp vào Tour */
// Tương ứng View: route('tour.supplier.add', ['id' => $tourId])
$router->post('/tour/supplier/add/{id}', [TourController::class, 'addSupplier'])->name('tour.supplier.add');
/* 3. Xóa Nhà cung cấp khỏi Tour */
// Tương ứng View: route('tour.supplier.delete', ['id' => $tourId])
$router->post('/tour/supplier/delete/{id}', [TourController::class, 'deleteSupplier'])->name('tour.supplier.delete');


// 1. Danh sách & Theo dõi chỗ trống (Monitor)
$router->get('/departure', [DepartureController::class, 'index'])->name('departure.index');

// 2. Tạo đợt mới
$router->get('/departure/create', [DepartureController::class, 'create'])->name('departure.create');
$router->post('/departure/store', [DepartureController::class, 'store'])->name('departure.store');

// 3. Cập nhật thông tin
$router->get('/departure/edit/{id}', [DepartureController::class, 'edit'])->name('departure.edit');
$router->post('/departure/update/{id}', [DepartureController::class, 'update'])->name('departure.update');

// 4. Xóa (Nếu cần)
$router->post('/departure/delete/{id}', [DepartureController::class, 'delete'])->name('departure.delete');


// 1. Danh sách Booking
$router->get('/booking', [BookingController::class, 'index'])->name('booking.index');

// 2. Tạo Booking mới (Form + Xử lý)
$router->get('/booking/create', [BookingController::class, 'create'])->name('booking.create');
$router->post('/booking/store', [BookingController::class, 'store'])->name('booking.store');

// 3. Chi tiết & Cập nhật trạng thái
$router->get('/booking/show/{id}', [BookingController::class, 'show'])->name('booking.show');
/* --- BỔ SUNG ROUTER HỦY BOOKING --- */
$router->post('/booking/cancel/{id}', [App\Controllers\BookingController::class, 'cancel'])->name('booking.cancel');

//  --- ĐIỀU HÀNH & PHÂN CÔNG --- */
// Danh sách lịch khởi hành cần phân công
$router->get('/assignment', [AssignmentController::class, 'index'])->name('assignment.index');

// Xử lý phân công (Gán HDV)
$router->post('/assignment/store', [AssignmentController::class, 'store'])->name('assignment.store');

// Hủy phân công
$router->post('/assignment/delete', [AssignmentController::class, 'delete'])->name('assignment.delete');

/* --- TÀI CHÍNH & KẾ TOÁN --- */
// Danh sách tất cả phiếu thu (Sổ quỹ)
$router->get('/payment', [PaymentController::class, 'index'])->name('payment.index');

// Form tạo phiếu thu (Độc lập, chọn booking từ select box)
$router->get('/payment/create', [PaymentController::class, 'create'])->name('payment.create');
$router->post('/payment/store', [PaymentController::class, 'store'])->name('payment.store');

// // 1. Quản lý Dịch vụ (Services)
// $router->post('/booking/service/add/{id}', [BookingFinanceController::class, 'addService'])->name('booking.service.add');
// $router->post('/booking/service/delete/{id}', [BookingFinanceController::class, 'deleteService'])->name('booking.service.delete');

// // 2. Quản lý Thanh toán (Payments)
// $router->post('/booking/payment/add/{id}', [BookingFinanceController::class, 'addPayment'])->name('booking.payment.add');
// $router->post('/booking/payment/delete/{id}', [BookingFinanceController::class, 'deletePayment'])->name('booking.payment.delete');
// // Group with prefix + middleware
// $router->group(['prefix'=>'/users', 'middleware'=>[ExampleMiddleware::class]], function(Router $r){
//     $r->get('', [UserController::class, 'index']);
//     $r->get('/{id}', [UserController::class, 'show']);
//     $r->post('', [UserController::class, 'store'], [CsrfMiddleware::class]);
// });
