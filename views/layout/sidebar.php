<aside class="main-sidebar sidebar-light-success elevation-4">
    <a href="#" class="brand-link">
        <img src="/assets/dist/img/logo2.png" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light"> Viet DV</span>
    </a>

    <div class="sidebar">
        <!-- User Panel -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="/assets/dist/img/logo1.png" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <?php
                if (session_status() === PHP_SESSION_NONE) session_start();
                $userName = $_SESSION['user_name'] ?? 'Khách';
                $role     = isset($_SESSION['user_role']) ? (int)$_SESSION['user_role'] : -1;
                ?>
                <a href="#" class="d-block">
                    <?= htmlspecialchars($userName) ?> <br>
                    <small>
                        (<?= $role === 0 ? 'Admin' : ($role === 1 ? 'Hướng dẫn viên' : 'Khách') ?>)
                    </small>
                </a>
            </div>
        </div>

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                
                <!-- ================================================= -->
                <!-- 1. MENU DÀNH RIÊNG CHO QUẢN TRỊ VIÊN (ADMIN - 0)  -->
                <!-- ================================================= -->
                <?php if ($role === 0): ?>
                    
                    <li class="nav-item">
                        <a href="<?= route('home.index') ?>" class="nav-link active">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard Admin</p>
                        </a>
                    </li>

                    <li class="nav-header font-weight-bold">QUẢN LÝ SẢN PHẨM</li>

                    <li class="nav-item">
                        <a href="<?= route('danhMuc.index') ?>" class="nav-link">
                            <i class="nav-icon fas fa-tags"></i> <p>Danh mục Tour</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="<?= route('supplier.index') ?>" class="nav-link">
                            <i class="nav-icon fas fa-handshake"></i> <p>Nhà cung cấp</p>
                        </a>
                    </li>

                    <li class="nav-item has-treeview"> 
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-plane-departure"></i> 
                            <p>
                                Quản lý Tour
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="<?= route('tour.index') ?>" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i> <p>Danh sách Tour</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= route('departure.index') ?>" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i> <p>Lịch khởi hành</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-header font-weight-bold">KINH DOANH & VẬN HÀNH</li>

                    <li class="nav-item has-treeview">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-shopping-cart"></i> 
                            <p>
                                Booking & Đơn hàng
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="<?= route('booking.index') ?>" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i> <p>Quản lý Đặt tour</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= route('payment.index') ?>" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i> <p>Quản lý Thanh toán</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a href="<?= route('assignment.index') ?>" class="nav-link">
                            <i class="nav-icon fas fa-user-tie"></i> <p>Phân công HDV</p>
                        </a>
                    </li>

                    <li class="nav-header font-weight-bold">HỆ THỐNG</li>

                    <li class="nav-item has-treeview">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-users-cog"></i> 
                            <p>
                                Tài khoản
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="<?= route('admin.index') ?>" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i> <p>Admin quản trị</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= route('admin.guide.index') ?>" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i> <p>Hướng dẫn viên</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    
                <!-- ================================================= -->
                <!-- 3. MENU CHUNG (AI CŨNG THẤY)                      -->
                <!-- ================================================= -->
                
                <li class="nav-header font-weight-bold">CÁ NHÂN</li>
                
                <li class="nav-item">
                    <!-- Ví dụ route xem hồ sơ cá nhân -->
                    <a href="<?= route('profile.index') ?>" class="nav-link">
                        <i class="nav-icon fas fa-id-card"></i> 
                        <p>Hồ sơ của tôi</p>
                    </a>
                </li>


                <?php endif; ?>


                <!-- ================================================= -->
                <!-- 2. MENU DÀNH RIÊNG CHO HƯỚNG DẪN VIÊN (HDV - 1)   -->
                <!-- ================================================= -->
                    <?php if ($role === 1): ?>
            
                        <li class="nav-item">
                            <a href="<?= route('guide.dashboard') ?>" class="nav-link">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Bàn làm việc</p>
                            </a>
                        </li>

                        <li class="nav-header font-weight-bold">CÔNG VIỆC</li>

                        <li class="nav-item">
                            <!-- CẬP NHẬT LINK Ở ĐÂY -->
                            <a href="<?= route('guide.my_tours') ?>" class="nav-link">
                                <i class="nav-icon fas fa-calendar-check"></i> 
                                <p>Lịch dẫn tour</p>
                            </a>
                        </li>

                    <?php endif; ?>


            </ul>
        </nav>
    </div>
</aside>