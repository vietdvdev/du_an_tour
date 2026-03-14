<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="<?= route('home.index') ?>" class="brand-link">
        <img src="https://adminlte.io/themes/v3/dist/img/AdminLTELogo.png" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light"> Travel Go</span>
    </a>

    <div class="sidebar">
        <!-- User Panel -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <!-- Avatar mặc định nếu chưa có -->
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['user_name'] ?? 'Guest') ?>&background=random" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <?php
                if (session_status() === PHP_SESSION_NONE) session_start();
                $userName = $_SESSION['user_name'] ?? 'Khách';
                $role     = isset($_SESSION['user_role']) ? (int)$_SESSION['user_role'] : -1;
                ?>
                <a href="<?= route('profile.index') ?>" class="d-block">
                    <?= htmlspecialchars($userName) ?>
                </a>
                <span class="badge badge-success" style="font-size: 0.8rem;">
                    <?= $role === 0 ? 'Admin' : ($role === 1 ? 'Guide' : 'Guest') ?>
                </span>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <!-- Thêm nav-child-indent để menu con thụt đầu dòng đẹp hơn -->
            <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent" data-widget="treeview" role="menu" data-accordion="false">
                
                <!-- ================================================= -->
                <!-- 1. MENU DÀNH RIÊNG CHO QUẢN TRỊ VIÊN (ADMIN - 0)  -->
                <!-- ================================================= -->
                <?php if ($role === 0): ?>
                    
                    <li class="nav-item">
                        <a href="<?= route('home.index') ?>" class="nav-link">
                            <i class="nav-icon fas fa-tachometer-alt text-info"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>

                    <li class="nav-header text-uppercase text-secondary font-weight-bold">Quản lý Sản phẩm</li>

                    <li class="nav-item">
                        <a href="<?= route('danhMuc.index') ?>" class="nav-link">
                            <i class="nav-icon fas fa-layer-group text-warning"></i> 
                            <p>Danh mục Tour</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="<?= route('supplier.index') ?>" class="nav-link">
                            <i class="nav-icon fas fa-store text-danger"></i> 
                            <p>Nhà cung cấp</p>
                        </a>
                    </li>

                    <li class="nav-item has-treeview"> 
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-globe-americas text-primary"></i> 
                            <p>
                                Quản lý Tour
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="<?= route('tour.index') ?>" class="nav-link">
                                    <i class="fas fa-map-marked-alt nav-icon"></i> <p>Danh sách Tour</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= route('departure.index') ?>" class="nav-link">
                                    <i class="fas fa-calendar-day nav-icon"></i> <p>Lịch khởi hành</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-header text-uppercase text-secondary font-weight-bold">Kinh doanh & Vận hành</li>

                    <li class="nav-item has-treeview">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-luggage-cart text-success"></i> 
                            <p>
                                Booking & Đơn hàng
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="<?= route('booking.index') ?>" class="nav-link">
                                    <i class="fas fa-file-invoice nav-icon"></i> <p>Quản lý Đặt tour</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= route('payment.index') ?>" class="nav-link">
                                    <i class="fas fa-file-invoice-dollar nav-icon"></i> <p>Quản lý Thanh toán</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a href="<?= route('assignment.index') ?>" class="nav-link">
                            <i class="nav-icon fas fa-user-check text-white"></i> <p>Phân công HDV</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="<?= route('report.revenue') ?>" class="nav-link">
                            <i class="nav-icon fas fa-chart-pie text-pink"></i> 
                            <p>Báo cáo Doanh thu</p>
                        </a>
                    </li>

                    <li class="nav-header text-uppercase text-secondary font-weight-bold">Hệ thống</li>

                    <li class="nav-item has-treeview">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-cogs"></i> 
                            <p>
                                Tài khoản & Phân quyền
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="<?= route('admin.index') ?>" class="nav-link">
                                    <i class="fas fa-user-shield nav-icon"></i> <p>DS Quản trị viên</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= route('admin.guide.index') ?>" class="nav-link">
                                    <i class="fas fa-id-badge nav-icon"></i> <p>DS Hướng dẫn viên</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    
                <?php endif; ?>

                <!-- ================================================= -->
                <!-- 2. MENU DÀNH RIÊNG CHO HƯỚNG DẪN VIÊN (HDV - 1)   -->
                <!-- ================================================= -->
                <?php if ($role === 1): ?>
            
                    <li class="nav-item">
                        <a href="<?= route('guide.dashboard') ?>" class="nav-link active">
                            <i class="nav-icon fas fa-home"></i>
                            <p>Bàn làm việc</p>
                        </a>
                    </li>

                    <li class="nav-header text-uppercase text-secondary font-weight-bold">Công việc</li>

                    <li class="nav-item">
                        <a href="<?= route('guide.my_tours') ?>" class="nav-link">
                            <i class="nav-icon fas fa-route text-success"></i> 
                            <p>Lịch dẫn tour</p>
                        </a>
                    </li>

                <?php endif; ?>

                <!-- ================================================= -->
                <!-- 3. MENU CÁ NHÂN (CHUNG)                           -->
                <!-- ================================================= -->
                
                <li class="nav-header text-uppercase text-secondary font-weight-bold">Cá nhân</li>
                
                <li class="nav-item">
                    <a href="<?= route('profile.index') ?>" class="nav-link">
                        <i class="nav-icon fas fa-user-circle"></i> 
                        <p>Hồ sơ của tôi</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= route('logout') ?>" class="nav-link text-danger">
                        <i class="nav-icon fas fa-sign-out-alt"></i> 
                        <p>Đăng xuất</p>
                    </a>
                </li>

            </ul>
        </nav>
    </div>
</aside>