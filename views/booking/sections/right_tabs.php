<div class="card card-primary card-outline card-outline-tabs">
    <div class="card-header p-0 border-bottom-0">
        <ul class="nav nav-tabs" id="booking-tabs" role="tablist">
            <!-- Nếu bạn muốn ẩn tab Khách thì comment dòng dưới lại -->
            <li class="nav-item">
                <a class="nav-link" id="tab-travelers-link" data-toggle="tab" href="#travelers" role="tab">Danh sách Khách</a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link active" id="tab-services-link" data-toggle="tab" href="#services" role="tab">Dịch vụ & Phụ thu</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-payments-link" data-toggle="tab" href="#payments" role="tab">Lịch sử Thanh toán</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-history-link" data-toggle="tab" href="#history" role="tab">Lịch sử Thay đổi</a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content" id="booking-tabs-content">
            
            <!-- TAB 1: KHÁCH (Nếu ẩn ở trên thì cũng ẩn/không include ở đây) -->
            <div class="tab-pane fade" id="travelers" role="tabpanel">
                <?php include __DIR__ . '/tab_travelers.php'; ?>
            </div>

            <!-- TAB 2: DỊCH VỤ -->
            <div class="tab-pane fade show active" id="services" role="tabpanel">
                <?php include __DIR__ . '/tab_services.php'; ?>
            </div>

            <!-- TAB 3: THANH TOÁN -->
            <div class="tab-pane fade" id="payments" role="tabpanel">
                <?php include __DIR__ . '/tab_payments.php'; ?>
            </div>

            <!-- TAB 4: LỊCH SỬ -->
            <div class="tab-pane fade" id="history" role="tabpanel">
                <?php include __DIR__ . '/tab_history.php'; ?>
            </div>

        </div>
    </div>
</div>