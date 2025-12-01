<aside class="main-sidebar sidebar-light-success elevation-4">
    <a href="#" class="brand-link">
        <img src="assets/dist/img/logo2.png" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light"> Viet DV</span>
    </a>

    <div class="sidebar">
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="assets/dist/img/logo1.png" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block">Quản Lý Tour</a>
            </div>
        </div>

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                
                <li class="nav-item">
                    <a href="#" class="nav-link active">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Trang chủ</p>
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

                <li class="nav-item has-treeview menu-open"> <a href="javascript:void(0);" class="nav-link">
                        <i class="nav-icon fas fa-plane-departure"></i> <p>
                            Quản lý Tour
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= route('tour.index') ?>" class="nav-link">
                                <i class="far fa-map nav-icon"></i> <p>Danh sách Tour</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= route('departure.index') ?>" class="nav-link">
                                <i class="far fa-calendar-alt nav-icon"></i> <p>Lịch khởi hành</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-header font-weight-bold">KINH DOANH & VẬN HÀNH</li>

                <li class="nav-item has-treeview">
                    <a href="javascript:void(0);" class="nav-link">
                        <i class="nav-icon fas fa-shopping-cart"></i> <p>
                            Booking & Đơn hàng
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= route('booking.index') ?>" class="nav-link">
                                <i class="fas fa-file-contract nav-icon"></i> <p>Quản lý Đặt tour</p>
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
                        <i class="nav-icon fas fa-user-tie"></i> <p>Phân công HDV</p>
                    </a>
                </li>

                <li class="nav-header font-weight-bold">HỆ THỐNG</li>

                <li class="nav-item has-treeview">
                    <a href="javascript:void(0);" class="nav-link">
                        <i class="nav-icon fas fa-users-cog"></i> <p>
                            Tài khoản
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= route('admin.index') ?>" class="nav-link">
                                <i class="fas fa-user-shield nav-icon"></i> <p>Admin quản trị</p>
                            </a>
                        </li>
                          <li class="nav-item">
                            <a href="<?= route('guide.index') ?>" class="nav-link">
                                <i class="fas fa-user-shield nav-icon"></i> <p>Hướng dẫn viên</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="fas fa-user nav-icon"></i> <p>Tài khoản cá nhân</p>
                            </a>
                        </li>
                    </ul>
                </li>

            </ul>
        </nav>
        </div>
    </aside>