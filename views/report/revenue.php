<?php include __DIR__ . '/../layout/header.php'; ?>
<?php include __DIR__ . '/../layout/navbar.php'; ?>
<?php include __DIR__ . '/../layout/sidebar.php'; ?>


<div class="content-wrapper">
    <!-- Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-chart-line text-success"></i> Báo cáo Doanh thu - Lợi nhuận</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <button class="btn btn-default btn-sm" onclick="window.print()">
                        <i class="fas fa-print"></i> In báo cáo
                    </button>
                </div>
            </div>
        </div>
    </section>


    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">
           
            <!-- 1. BỘ LỌC THỜI GIAN -->
            <div class="card mb-3 shadow-sm">
                <div class="card-body py-2">
                    <form action="" method="GET" class="form-inline justify-content-center" id="filterForm">
                        <label class="mr-2 font-weight-normal">Từ ngày:</label>
                        <input type="date" name="from_date" id="from_date" class="form-control form-control-sm mr-3" value="<?= htmlspecialchars($fromDate) ?>">
                       
                        <label class="mr-2 font-weight-normal">Đến ngày:</label>
                        <input type="date" name="to_date" id="to_date" class="form-control form-control-sm mr-3" value="<?= htmlspecialchars($toDate) ?>">
                       
                        <button type="submit" class="btn btn-sm btn-primary mr-2">
                            <i class="fas fa-filter"></i> Xem dữ liệu
                        </button>
                       
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-default" onclick="setFilter('this_week')">Tuần này</button>
                            <button type="button" class="btn btn-default" onclick="setFilter('this_month')">Tháng này</button>
                            <button type="button" class="btn btn-default" onclick="setFilter('this_year')">Năm nay</button>
                        </div>
                    </form>
                </div>
            </div>


            <!-- 2. BẢNG DỮ LIỆU -->
            <div class="card card-outline card-success">
                <div class="card-header border-0">
                    <h3 class="card-title text-muted">
                        <i class="far fa-calendar-alt mr-1"></i>
                        Thống kê từ <b><?= date('d/m/Y', strtotime($fromDate)) ?></b> đến <b><?= date('d/m/Y', strtotime($toDate)) ?></b>
                    </h3>
                </div>
               
                <div class="card-body table-responsive p-0">
                    <table class="table table-bordered table-striped table-hover text-nowrap table-valign-middle">
                        <thead class="bg-light">
                            <tr class="text-center">
                                <th style="width: 50px">STT</th>
                                <th>Mã Tour</th>
                                <th>Tên Tour</th>
                                <th>Khởi hành</th>
                                <th>Số khách</th>
                                <th class="text-primary border-left">Tổng Thu (1)</th>
                                <th class="text-danger">Tổng Chi (2)</th>
                                <th class="text-warning">Phát sinh (3)</th>
                                <th class="text-success font-weight-bold border-left" style="background-color: #f0fff4;">Lợi nhuận</th>
                                <th>Tỷ suất %</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($reports)): ?>
                                <?php
                                    // Biến tổng cộng toàn trang
                                    $sumRevenue = 0;
                                    $sumServiceCost = 0;
                                    $sumExpense = 0;
                                    $sumProfit = 0;
                                    $totalPax = 0;
                                ?>
                                <?php foreach ($reports as $key => $row):
                                    $sumRevenue += $row['total_revenue'];
                                    $sumServiceCost += $row['total_service_cost'];
                                    $sumExpense += $row['total_expense'];
                                    $sumProfit += $row['profit'];
                                    $totalPax += $row['total_pax'];
                                ?>
                                    <tr>
                                        <td class="text-center"><?= $key + 1 ?></td>
                                        <td>
                                            <span class="badge badge-info"><?= htmlspecialchars($row['tour_code']) ?></span>
                                        </td>
                                        <td>
                                            <div style="max-width: 250px; white-space: normal; line-height: 1.2;">
                                                <?= htmlspecialchars($row['tour_name']) ?>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <?= date('d/m/Y', strtotime($row['start_date'])) ?>
                                        </td>
                                        <td class="text-center font-weight-bold"><?= $row['total_pax'] ?></td>
                                       
                                        <!-- Doanh thu -->
                                        <td class="text-right text-primary font-weight-bold border-left">
                                            <?= number_format($row['total_revenue']) ?> ₫
                                        </td>
                                       
                                        <!-- Chi phí Tour (Dịch vụ) -->
                                        <td class="text-right text-danger">
                                            <?= number_format($row['total_service_cost']) ?> ₫
                                        </td>


                                        <!-- Chi phí phát sinh -->
                                        <td class="text-right text-warning">
                                            <?= number_format($row['total_expense']) ?> ₫
                                        </td>


                                        <!-- Lợi nhuận (1 - 2 - 3) -->
                                        <td class="text-right font-weight-bold border-left" style="background-color: #f0fff4;">
                                            <span class="<?= $row['profit'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                                <?= number_format($row['profit']) ?> ₫
                                            </span>
                                        </td>


                                        <!-- % Lợi nhuận -->
                                        <td class="text-center">
                                            <?php if($row['profit_margin'] > 0): ?>
                                                <span class="badge badge-success"><?= $row['profit_margin'] ?>%</span>
                                            <?php elseif($row['profit_margin'] < 0): ?>
                                                <span class="badge badge-danger"><?= $row['profit_margin'] ?>%</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">0%</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                               
                                <!-- DÒNG TỔNG KẾT TOÀN BỘ -->
                                <tr class="bg-secondary font-weight-bold" style="font-size: 1.1em;">
                                    <td colspan="4" class="text-right text-uppercase">Tổng cộng:</td>
                                    <td class="text-center"><?= number_format($totalPax) ?></td>
                                   
                                    <td class="text-right border-left"><?= number_format($sumRevenue) ?> ₫</td>
                                    <td class="text-right text-white"><?= number_format($sumServiceCost) ?> ₫</td>
                                    <td class="text-right text-warning"><?= number_format($sumExpense) ?> ₫</td>
                                   
                                    <td class="text-right border-left bg-dark text-success">
                                        <?= number_format($sumProfit) ?> ₫
                                    </td>
                                    <td></td>
                                </tr>


                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-5">
                                        <i class="fas fa-search-dollar fa-3x mb-3 text-gray-300"></i><br>
                                        Không tìm thấy dữ liệu tour nào trong khoảng thời gian này.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>


<?php include __DIR__ . '/../layout/footer.php'; ?>


<!-- Script hỗ trợ chọn nhanh ngày -->
<script>
    function setFilter(type) {
        const today = new Date();
        let fromDate = new Date();
        let toDate = new Date();


        // Helper để format ngày thành YYYY-MM-DD
        const formatDate = (date) => {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        };


        if (type === 'this_week') {
            // Lấy ngày đầu tuần (Thứ 2)
            const day = today.getDay();
            const diff = today.getDate() - day + (day == 0 ? -6 : 1); // Điều chỉnh nếu CN là 0
            fromDate.setDate(diff);
            // Cuối tuần (CN) = Đầu tuần + 6 ngày
            toDate = new Date(fromDate);
            toDate.setDate(fromDate.getDate() + 6);
        } else if (type === 'this_month') {
            fromDate = new Date(today.getFullYear(), today.getMonth(), 1);
            toDate = new Date(today.getFullYear(), today.getMonth() + 1, 0); // Ngày cuối tháng
        } else if (type === 'this_year') {
            fromDate = new Date(today.getFullYear(), 0, 1);
            toDate = new Date(today.getFullYear(), 11, 31);
        }


        document.getElementById('from_date').value = formatDate(fromDate);
        document.getElementById('to_date').value = formatDate(toDate);
       
        // Tự động submit form
        document.getElementById('filterForm').submit();
    }
</script>



