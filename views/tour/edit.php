<?php include __DIR__ . '/../layout/header.php'; ?>
<?php include __DIR__ . '/../layout/navbar.php'; ?>
<?php include __DIR__ . '/../layout/sidebar.php'; ?>

<?php
// 1. Xác định tab hiện tại từ URL (Mặc định là 'info')
$currentTab = $_GET['tab'] ?? 'info';

// 2. Định nghĩa danh sách Tab và File tương ứng
$tabs = [
    'info' => [
        'label' => 'Thông tin chung',
        'icon'  => 'fas fa-info-circle',
        'file'  => 'basic_info.php'
    ],
    'itinerary' => [
        'label' => 'Lịch trình & Ảnh',
        'icon'  => 'fas fa-map-marked-alt',
        'file'  => 'itinerary_images.php'
    ],
    'price' => [
        'label' => 'Bảng giá',
        'icon'  => 'fas fa-tags',
        'file'  => 'price.php'
    ],
    'policy' => [
        'label' => 'Chính sách & NCC',
        'icon'  => 'fas fa-shield-alt',
        'file'  => 'policy_suppliers.php'
    ]
];

if (!array_key_exists($currentTab, $tabs)) {
    $currentTab = 'info';
}
?>

<div class="content-wrapper">
    <!-- Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>
                        Chỉnh sửa Tour: <span class="text-primary font-weight-bold"><?= htmlspecialchars($tour['code'] ?? 'N/A') ?></span>
                        <!-- Hiển thị Badge trạng thái -->
                        <?php if(($tour['state'] ?? '') == 'PUBLISHED'): ?>
                            <span class="badge badge-success" style="font-size: 0.5em; vertical-align: middle;">Đang công bố</span>
                        <?php else: ?>
                            <span class="badge badge-secondary" style="font-size: 0.5em; vertical-align: middle;">Bản nháp</span>
                        <?php endif; ?>
                    </h1>
                    <small class="text-muted"><?= htmlspecialchars($tour['name'] ?? '') ?></small>
                </div>
                <div class="col-sm-6 text-right">
                    
                    <!-- [MỚI] NÚT CÔNG BỐ TOUR (Chỉ hiện khi đang là DRAFT) -->
                    <?php if (($tour['state'] ?? 'DRAFT') === 'DRAFT'): ?>
                        <form action="<?= route('tour.publish', ['id' => $tour['id']]) ?>" method="POST" style="display:inline;" onsubmit="return confirm('Bạn có chắc muốn công bố Tour này không? Tour sẽ hiển thị công khai.');">
                            <button type="submit" class="btn btn-success mr-2">
                                <i class="fas fa-rocket"></i> Công bố
                            </button>
                        </form>
                    <?php endif; ?>

                    <a href="<?= route('tour.index') ?>" class="btn btn-default mr-2">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                    
                    <a href="<?= route('tour.show', ['id' => $tour['id']]) ?>" class="btn btn-info" target="_blank">
                        <i class="fas fa-eye"></i> Xem chi tiết
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">
            
            <?php if (!empty($_SESSION['flash_success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
                </div>
            <?php endif; ?>

            <div class="card card-primary card-outline card-outline-tabs">
                
                <div class="card-header p-0 border-bottom-0">
                    <ul class="nav nav-tabs">
                        <?php foreach ($tabs as $key => $tabInfo): ?>
                            <li class="nav-item">
                                <a class="nav-link <?= $currentTab == $key ? 'active' : '' ?>" 
                                   href="<?= route('tour.edit', ['id' => $tour['id']]) ?>?tab=<?= $key ?>">
                                    <i class="<?= $tabInfo['icon'] ?>"></i> <?= $tabInfo['label'] ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <div class="card-body">
                    <div class="tab-content">
                        <div class="tab-pane fade show active">
                            <?php 
                                $fileToInclude = __DIR__ . '/tour_sections/' . $tabs[$currentTab]['file'];
                                if (file_exists($fileToInclude)) {
                                    include $fileToInclude;
                                } else {
                                    echo '<div class="alert alert-danger">Không tìm thấy file giao diện: <strong>' . htmlspecialchars($tabs[$currentTab]['file']) . '</strong></div>';
                                }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>