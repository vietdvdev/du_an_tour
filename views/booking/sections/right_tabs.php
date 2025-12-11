<?php
// Xác định loại tour để hiển thị giao diện phù hợp (nếu cần)
$isCustom = isset($booking['is_custom']) && $booking['is_custom'] == 1;
?>


<div class="card card-primary card-outline card-outline-tabs h-100">
    <div class="card-header p-0 border-bottom-0">
        <ul class="nav nav-tabs" id="booking-tabs" role="tablist">
            <!-- Tab 1: Danh sách Khách -->
            <li class="nav-item">
                <a class="nav-link active" id="tab-travelers-link" data-toggle="pill" href="#content-travelers" role="tab">Danh sách Khách</a>
            </li>
           
            <!-- Tab 2: Dịch vụ & Phụ thu -->
            <li class="nav-item">
                <a class="nav-link" id="tab-services-link" data-toggle="pill" href="#content-services" role="tab">Dịch vụ & Phụ thu</a>
            </li>
           
            <!-- Tab 3: Lịch sử Thanh toán -->
            <li class="nav-item">
                <a class="nav-link" id="tab-payments-link" data-toggle="pill" href="#content-payments" role="tab">Lịch sử Thanh toán</a>
            </li>
           
            <!-- Tab 4: Lịch sử Thay đổi -->
            <li class="nav-item">
                <a class="nav-link" id="tab-history-link" data-toggle="pill" href="#content-history" role="tab">Lịch sử Thay đổi</a>
            </li>
        </ul>
    </div>
   
    <div class="card-body">
        <div class="tab-content" id="booking-tabs-content">
           
            <!-- ========================================================= -->
            <!-- NỘI DUNG TAB 1: DANH SÁCH KHÁCH                           -->
            <!-- ========================================================= -->
            <div class="tab-pane fade show active" id="content-travelers" role="tabpanel">
                <?php
                    if (file_exists(__DIR__ . '/tab_travelers.php')) {
                        include __DIR__ . '/tab_travelers.php';
                    } else {
                        echo '<div class="alert alert-warning text-center mt-3">File giao diện tab_travelers.php chưa được tạo.</div>';
                    }
                ?>
            </div>


            <!-- ========================================================= -->
            <!-- NỘI DUNG TAB 2: DỊCH VỤ & PHỤ THU                         -->
            <!-- ========================================================= -->
            <div class="tab-pane fade" id="content-services" role="tabpanel">
                <?php
                    if (file_exists(__DIR__ . '/tab_services.php')) {
                        include __DIR__ . '/tab_services.php';
                    } else {
                        echo '<div class="alert alert-warning text-center mt-3">File giao diện tab_services.php chưa được tạo.</div>';
                    }
                ?>
            </div>
           
            <!-- ========================================================= -->
            <!-- NỘI DUNG TAB 3: LỊCH SỬ THANH TOÁN                        -->
            <!-- ========================================================= -->
            <div class="tab-pane fade" id="content-payments" role="tabpanel">
                <?php
                    if (file_exists(__DIR__ . '/tab_payments.php')) {
                        include __DIR__ . '/tab_payments.php';
                    } else {
                         echo '<div class="alert alert-warning text-center mt-3">File giao diện tab_payments.php chưa được tạo.</div>';
                    }
                ?>
            </div>


            <!-- ========================================================= -->
            <!-- NỘI DUNG TAB 4: LỊCH SỬ THAY ĐỔI                          -->
            <!-- ========================================================= -->
            <div class="tab-pane fade" id="content-history" role="tabpanel">
                <?php
                    if (file_exists(__DIR__ . '/tab_history.php')) {
                        include __DIR__ . '/tab_history.php';
                    } else {
                        echo '<div class="text-center text-muted py-5"><i class="fas fa-history fa-2x mb-2"></i><br>Chưa có dữ liệu lịch sử thay đổi.</div>';
                    }
                ?>
            </div>


        </div>
    </div>
</div>

