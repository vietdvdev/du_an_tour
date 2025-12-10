<?php include __DIR__ . '/../layout/header.php'; ?>
<?php include __DIR__ . '/../layout/navbar.php'; ?>
<?php include __DIR__ . '/../layout/sidebar.php'; ?>


<div class="content-wrapper">
    <!-- Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Dashboard Quản trị</h1>
                </div>
            </div>
        </div>
    </div>


    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
           
            <!-- 1. THẺ THỐNG KÊ -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?= $totalBookings ?></h3>
                            <p>Đơn đặt Tour</p>
                        </div>
                        <div class="icon"><i class="fas fa-shopping-cart"></i></div>
                        <a href="<?= route('booking.index') ?>" class="small-box-footer">Chi tiết <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?= number_format($totalRevenue) ?><sup style="font-size: 20px">đ</sup></h3>
                            <p>Doanh thu ước tính</p>
                        </div>
                        <div class="icon"><i class="ion ion-stats-bars"></i></div>
                        <a href="<?= route('report.revenue') ?>" class="small-box-footer">Xem báo cáo <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?= $totalTours ?></h3>
                            <p>Tour đang hoạt động</p>
                        </div>
                        <div class="icon"><i class="fas fa-map-marked-alt"></i></div>
                        <a href="<?= route('tour.index') ?>" class="small-box-footer">Quản lý Tour <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>


            <!-- 2. BIỂU ĐỒ -->
            <div class="row">
                <!-- Cột trái: Biểu đồ doanh thu -->
                <div class="col-md-8">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-chart-bar mr-1"></i> Doanh thu năm nay</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="revenueChart" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>


                <!-- Cột phải: Biểu đồ trạng thái -->
                <div class="col-md-4">
                    <div class="card card-danger card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-chart-pie mr-1"></i> Tình trạng Booking</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="statusChart" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </section>
</div>


<?php include __DIR__ . '/../layout/footer.php'; ?>


<!-- ChartJS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


<script>
$(function () {
    /* * 1. BIỂU ĐỒ DOANH THU (BAR CHART)
     */
    var revenueCtx = document.getElementById('revenueChart').getContext('2d');
    var revenueData = {
        labels: <?= $revenueLabels ?>, // Dữ liệu từ Controller (Tháng 1 -> 12)
        datasets: [
            {
                label: 'Doanh thu (VNĐ)',
                backgroundColor: 'rgba(60,141,188,0.9)',
                borderColor: 'rgba(60,141,188,0.8)',
                pointRadius: false,
                pointColor: '#3b8bba',
                pointStrokeColor: 'rgba(60,141,188,1)',
                pointHighlightFill: '#fff',
                pointHighlightStroke: 'rgba(60,141,188,1)',
                data: <?= $revenueValues ?> // Dữ liệu tiền
            }
        ]
    }


    var revenueChart = new Chart(revenueCtx, {
        type: 'bar',
        data: revenueData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            // Format tiền tệ trục Y (VD: 1.000.000)
                            return new Intl.NumberFormat('vi-VN').format(value) + ' đ';
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('vi-VN').format(context.parsed.y) + ' đ';
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });


    /* * 2. BIỂU ĐỒ TRẠNG THÁI (DONUT CHART)
     */
    var statusCtx = document.getElementById('statusChart').getContext('2d');
    var statusData = {
        labels: <?= $statusLabels ?>,
        datasets: [
            {
                data: <?= $statusValues ?>,
                backgroundColor : ['#f56954', '#00a65a', '#f39c12', '#00c0ef', '#3c8dbc', '#d2d6de'],
            }
        ]
    }


    var statusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: statusData,
        options: {
            maintainAspectRatio: false,
            responsive: true,
        }
    });
});
</script>



