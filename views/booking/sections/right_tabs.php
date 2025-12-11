<!-- <?php
// Xác định loại tour để hiển thị giao diện phù hợp (nếu cần)
$isCustom = isset($booking['is_custom']) && $booking['is_custom'] == 1;


?> -->


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
               
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title text-muted m-0">
                        <i class="fas fa-users mr-1"></i> Danh sách đoàn (<?= count($travelers) ?>)
                    </h5>
                   
                    <!-- Nút Thêm Khách (Luôn hiện cho cả Tour Hệ thống và Tour Custom) -->
                    <button type="button" class="btn btn-sm btn-success shadow-sm" data-toggle="modal" data-target="#modalAddTraveler">
                        <i class="fas fa-user-plus mr-1"></i>
                        <?= $isCustom ? 'Thêm khách & Tính phí' : 'Thêm khách vào đoàn' ?>
                    </button>
                </div>


                <div class="table-responsive">
                    <table class="table table-bordered table-hover text-nowrap">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 50px;" class="text-center">#</th>
                                <th>Họ tên</th>
                                <th>Giới tính</th>
                                <th>Ngày sinh</th>
                                <th>Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($travelers)): ?>
                                <?php foreach ($travelers as $key => $t): ?>
                                <tr>
                                    <td class="text-center align-middle"><?= $key + 1 ?></td>
                                    <td class="align-middle">
                                        <span class="font-weight-bold text-primary"><?= htmlspecialchars($t['full_name']) ?></span>
                                    </td>
                                    <td class="align-middle">
                                        <?php if($t['gender'] == 'MALE'): ?>
                                            <span class="badge badge-light text-primary"><i class="fas fa-mars"></i> Nam</span>
                                        <?php elseif($t['gender'] == 'FEMALE'): ?>
                                            <span class="badge badge-light text-pink"><i class="fas fa-venus"></i> Nữ</span>
                                        <?php else: ?>
                                            <span class="badge badge-light">Khác</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle">
                                        <?php if(!empty($t['dob'])): ?>
                                            <?= date('d/m/Y', strtotime($t['dob'])) ?>
                                            <small class="text-muted">(<?= date('Y') - date('Y', strtotime($t['dob'])) ?> tuổi)</small>
                                        <?php else: ?>
                                            ---
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle text-muted small">
                                        <?= htmlspecialchars($t['note']) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="fas fa-user-slash fa-2x mb-2 text-gray-300"></i><br>
                                        Chưa có thông tin khách hàng.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
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

