<?php
use App\Core\Router;
use App\Core\Request;
use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\UserController;
use App\Controllers\DanhMucController;
use App\Controllers\SupplierController;
use App\Controllers\TourController;
use App\Controllers\DepartureController;
use App\Controllers\BookingController;
use App\Controllers\PaymentController;
use App\Controllers\AssignmentController;
use App\Controllers\BookingFinanceController;
use App\Controllers\TourLogController;
use App\Controllers\AttendanceController;
use App\Controllers\GuideController;

// Import Middleware
use App\Middleware\AuthMiddleware;
use App\Middleware\AdminMiddleware;
use App\Middleware\GuideMiddleware;

/** @var Router $router */

// ==============================================================================
// 1. PUBLIC ROUTES (AI CŨNG TRUY CẬP ĐƯỢC)
// ==============================================================================

// Trang chủ

$router->get('/', [HomeController::class, 'index'])->name('home.index');
// Đăng nhập / Đăng xuất
$router->get('/login', [AuthController::class, 'showLogin'])->name('login');
$router->post('/login', [AuthController::class, 'login'])->name('login.post');
$router->get('/logout', [AuthController::class, 'logout'])->name('logout');




// ==============================================================================
// 2. NHÓM ADMIN (YÊU CẦU ĐĂNG NHẬP + QUYỀN ADMIN)
// ==============================================================================
$router->group(['middleware' => [AuthMiddleware::class, AdminMiddleware::class]], function (Router $r) {
    
    // --- QUẢN LÝ TÀI KHOẢN (USER & HDV) ---

    $r->get('/list-admin', [UserController::class, 'indexAdmin'])->name('admin.index');
    $r->get('/list-guide', [UserController::class, 'indexGuide'])->name('admin.guide.index');
    
    $r->get('/admin/create', [UserController::class, 'create'])->name('admin.create');
    $r->post('/admin/store', [UserController::class, 'store'])->name('admin.store');
    
    $r->get('/admin/edit/{id}', [UserController::class, 'edit'])->name('admin.edit');
    $r->post('/admin/update/{id}', [UserController::class, 'update'])->name('admin.update');
    $r->get('/admin/delete/{id}', [UserController::class, 'delete'])->name('admin.delete');
    
    $r->get('/admin/toggle-status/{id}', [UserController::class, 'toggleStatus'])->name('admin.toggle_status');
    $r->get('/admin/detail/{id}', [UserController::class, 'detail'])->name('admin.detail');


    // --- QUẢN LÝ DANH MỤC TOUR ---
    $r->get('/list-danh-muc', [DanhMucController::class, 'index'])->name('danhMuc.index');
    $r->get('/danh-muc/create', [DanhMucController::class, 'create'])->name('danhMuc.create');
    $r->post('/danh-muc/store', [DanhMucController::class, 'store'])->name('danhMuc.store');
    
    $r->get('/danh-muc/edit/{id}', [DanhMucController::class, 'edit'])->name('danhMuc.edit');
    $r->post('/danh-muc/update/{id}', [DanhMucController::class, 'update'])->name('danhMuc.update');
    $r->post('/danh-muc/update/active/{id}', [DanhMucController::class, 'updateActive'])->name('danhMuc.update.active');
    
    $r->get('/danh-muc/delete/{id}', [DanhMucController::class, 'delete'])->name('danhMuc.delete');


    // --- QUẢN LÝ NHÀ CUNG CẤP (SUPPLIER) ---
    $r->get('/supplier', [SupplierController::class, 'index'])->name('supplier.index');
    $r->get('/supplier/create', [SupplierController::class, 'create'])->name('supplier.create');
    $r->post('/supplier/store', [SupplierController::class, 'store'])->name('supplier.store');
    
    $r->get('/supplier/edit/{id}', [SupplierController::class, 'edit'])->name('supplier.edit');
    $r->post('/supplier/update/{id}', [SupplierController::class, 'update'])->name('supplier.update');
    $r->post('/supplier/delete/{id}', [SupplierController::class, 'delete'])->name('supplier.delete');
    $r->post('/supplier/update/active/{id}', [SupplierController::class, 'updateActive'])->name('supplier.update.active');


    // --- QUẢN LÝ TOUR (SẢN PHẨM) ---
    $r->get('/tour', [TourController::class, 'index'])->name('tour.index');
    $r->get('/tour/show/{id}', [TourController::class, 'show'])->name('tour.show');
    
    // Tạo & Sửa Tour
    $r->get('/tour/create', [TourController::class, 'create'])->name('tour.create');
    $r->post('/tour/store', [TourController::class, 'store'])->name('tour.store');
    
    $r->get('/tour/edit/{id}', [TourController::class, 'edit'])->name('tour.edit');
    $r->post('/tour/update/{id}', [TourController::class, 'update'])->name('tour.update');
    $r->post('/tour/toggle-status/{id}', [TourController::class, 'toggleStatus'])->name('tour.toggle.status');
    $r->post('/tour/toggle-active/{id}', [TourController::class, 'toggleActive'])->name('tour.toggle.active');
    $r->post('/tour/publish/{id}', [TourController::class, 'publish'])->name('tour.publish');
    $r->post('/tour/delete/{id}', [TourController::class, 'delete'])->name('tour.delete');

    // Các thành phần con của Tour
    $r->post('/tour/update/itinerary/{id}', [TourController::class, 'updateItinerary'])->name('tour.update.itinerary');
    $r->post('/tour/itinerary/delete-item/{id}', [TourController::class, 'deleteItineraryItem'])->name('tour.itinerary.delete_item');
    $r->post('/tour/update/price/{id}', [TourController::class, 'updatePrice'])->name('tour.update.price');
    $r->post('/tour/update/policy/{id}', [TourController::class, 'updatePolicy'])->name('tour.update.policy');
    // Bỏ dòng trùng lặp '/tour/policy/update/{id}' vì đã có '/tour/update/policy/{id}' ở trên (hoặc giữ lại nếu view dùng url khác)
    $r->post('/tour/policy/update/{id}', [TourController::class, 'updatePolicy'])->name('tour.update.policy.alt'); 
    
    $r->post('/tour/update/images/{id}', [TourController::class, 'updateImages'])->name('tour.update.images');
    $r->post('/tour/update/suppliers/{id}', [TourController::class, 'updateSuppliers'])->name('tour.update.suppliers');
    $r->post('/tour/supplier/add/{id}', [TourController::class, 'addSupplier'])->name('tour.supplier.add');
    $r->post('/tour/supplier/delete/{id}', [TourController::class, 'deleteSupplier'])->name('tour.supplier.delete');


    // --- QUẢN LÝ LỊCH KHỞI HÀNH (DEPARTURE) ---
    $r->get('/departure', [DepartureController::class, 'index'])->name('departure.index');
    $r->get('/departure/create', [DepartureController::class, 'create'])->name('departure.create');
    $r->post('/departure/store', [DepartureController::class, 'store'])->name('departure.store');
    
    $r->get('/departure/edit/{id}', [DepartureController::class, 'edit'])->name('departure.edit');
    $r->post('/departure/update/{id}', [DepartureController::class, 'update'])->name('departure.update');
    $r->post('/departure/delete/{id}', [DepartureController::class, 'delete'])->name('departure.delete');


    // --- QUẢN LÝ BOOKING (ĐẶT TOUR) ---
    $r->get('/booking', [BookingController::class, 'index'])->name('booking.index');
    $r->get('/booking/create', [BookingController::class, 'create'])->name('booking.create');
    $r->post('/booking/store', [BookingController::class, 'store'])->name('booking.store');
    
    $r->get('/booking/show/{id}', [BookingController::class, 'show'])->name('booking.show');
    $r->post('/booking/cancel/{id}', [BookingController::class, 'cancel'])->name('booking.cancel');
    $r->post('/booking/update-status/{id}', [BookingController::class, 'updateStatus'])->name('booking.update.status');

    // Tài chính Booking
    $r->post('/booking/service/add/{id}', [BookingFinanceController::class, 'addService'])->name('booking.service.add');
    $r->post('/booking/service/delete/{id}', [BookingFinanceController::class, 'deleteService'])->name('booking.service.delete');
    
    $r->post('/booking/payment/add/{id}', [BookingFinanceController::class, 'addPayment'])->name('booking.payment.add');
    $r->post('/booking/payment/delete/{id}', [BookingFinanceController::class, 'deletePayment'])->name('booking.payment.delete');


    // --- ĐIỀU HÀNH & PHÂN CÔNG (ASSIGNMENT) ---
    $r->get('/assignment', [AssignmentController::class, 'index'])->name('assignment.index');
    $r->post('/assignment/store', [AssignmentController::class, 'store'])->name('assignment.store');
    $r->post('/assignment/delete', [AssignmentController::class, 'delete'])->name('assignment.delete');


    // --- TÀI CHÍNH & KẾ TOÁN (PAYMENT) ---
    $r->get('/payment', [PaymentController::class, 'index'])->name('payment.index');
    $r->get('/payment/create', [PaymentController::class, 'create'])->name('payment.create');
    $r->post('/payment/store', [PaymentController::class, 'store'])->name('payment.store');

}); // <--- ĐÓNG NHÓM ADMIN (QUAN TRỌNG)


// ==============================================================================
// 3. NHÓM HƯỚNG DẪN VIÊN (YÊU CẦU ĐĂNG NHẬP + QUYỀN HDV)
// ==============================================================================
$router->group(['middleware' => [AuthMiddleware::class, GuideMiddleware::class]], function (Router $r) {
    
    // Dashboard
    $r->get('/guide/dashboard', [GuideController::class, 'index'])->name('guide.dashboard');

    // Lịch tour
    $r->get('/guide/my-tours', [GuideController::class, 'myTours'])->name('guide.my_tours');

    // 2. THÊM 2 DÒNG NÀY ĐỂ CHỨC NĂNG ĐIỂM DANH HOẠT ĐỘNG
    // Màn hình danh sách khách
    $r->get('/guide/attendance', [AttendanceController::class, 'index'])->name('guide.attendance');
    
    // Xử lý check-in (Ajax)
    $r->post('/guide/attendance/check', [AttendanceController::class, 'checkIn'])->name('guide.attendance.check');
            // [MỚI] NHẬT KÝ TOUR (Thêm đoạn này)
    // ---------------------------------------------------------
    // Xem danh sách & Form viết nhật ký
    $r->get('/guide/log', [TourLogController::class, 'index'])->name('guide.log.index');
   
    // Xử lý lưu nhật ký
    $r->post('/guide/log/store', [TourLogController::class, 'store'])->name('guide.log.store');




});


$router->group(['middleware' => [AuthMiddleware::class]], function (Router $r) {
    
    // Xem hồ sơ (Link này đã có trong Sidebar)
    $r->get('/profile', [UserController::class, 'profile'])->name('profile.index');

    // Form sửa thông tin cá nhân
    $r->get('/profile/edit', [UserController::class, 'editProfile'])->name('profile.edit');

    // Xử lý cập nhật (Chỉ update bảng users)
    $r->post('/profile/update', [UserController::class, 'updateProfile'])->name('profile.update');
});