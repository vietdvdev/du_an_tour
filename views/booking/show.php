<!-- Cột phải: Tab chức năng -->
<div class="col-md-8">
    <div class="card card-primary card-outline card-outline-tabs">
        <div class="card-header p-0 border-bottom-0">
            <ul class="nav nav-tabs" id="custom-tabs-four-tab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="tab-travelers" data-toggle="pill" href="#travelers" role="tab">Danh sách Khách</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-services" data-toggle="pill" href="#services" role="tab">Dịch vụ & Phụ thu</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-payments" data-toggle="pill" href="#payments" role="tab">Lịch sử Thanh toán</a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="custom-tabs-four-tabContent">
                
                <!-- TAB 1: TRAVELERS (Giữ nguyên code cũ của bạn hoặc dùng code này) -->
                <div class="tab-pane fade show active" id="travelers" role="tabpanel">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style="width: 10px">#</th>
                                <th>Họ và tên</th>
                                <th>Giới tính</th>
                                <th>Ngày sinh</th>
                                <th>Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($travelers)): ?>
                                <tr><td colspan="5" class="text-center">Chưa có thông tin khách hàng</td></tr>
                            <?php else: ?>
                                <?php foreach ($travelers as $idx => $t): ?>
                                <tr>
                                    <td><?= $idx + 1 ?></td>
                                    <td class="font-weight-bold"><?= htmlspecialchars($t['full_name'] ?? '') ?></td>
                                    <td><?= ($t['gender']=='MALE')?'Nam':(($t['gender']=='FEMALE')?'Nữ':'Khác') ?></td>
                                    <td><?= !empty($t['dob']) ? date('d/m/Y', strtotime($t['dob'])) : '-' ?></td>
                                    <td><?= htmlspecialchars($t['note'] ?? '') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- TAB 2: SERVICES -->
                <div class="tab-pane fade" id="services" role="tabpanel">
                    <div class="text-right mb-3">
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-add-service">
                            <i class="fas fa-plus"></i> Thêm Dịch vụ
                        </button>
                    </div>
                    
                    <!-- Cần query lấy services trong controller và truyền sang view -->
                    <?php 
                        // Để code chạy ngay, ta query trực tiếp (hoặc bạn update Controller để truyền $services sang)
                        $services = (new \App\Models\BookingService())->where('booking_id', $booking['id']);
                    ?>
                    
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Dịch vụ</th>
                                <th class="text-center">SL</th>
                                <th class="text-right">Đơn giá</th>
                                <th class="text-right">Thành tiền</th>
                                <th class="text-center">Xóa</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $totalService = 0; ?>
                            <?php foreach ($services as $svc): $totalService += $svc['amount']; ?>
                            <tr>
                                <td>
                                    <span class="badge badge-info"><?= $svc['type'] ?></span> 
                                    <?= htmlspecialchars($svc['name']) ?>
                                </td>
                                <td class="text-center"><?= $svc['qty'] ?></td>
                                <td class="text-right"><?= number_format($svc['unit_price']) ?></td>
                                <td class="text-right font-weight-bold"><?= number_format($svc['amount']) ?></td>
                                <td class="text-center">
                                    <form action="<?= route('booking.service.delete', ['id' => $booking['id']]) ?>" method="POST" onsubmit="return confirm('Xóa dịch vụ này?')">
                                        <input type="hidden" name="service_id" value="<?= $svc['id'] ?>">
                                        <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="bg-light">
                                <td colspan="3" class="text-right font-weight-bold">Tổng cộng:</td>
                                <td class="text-right font-weight-bold text-primary"><?= number_format($totalService) ?> đ</td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- TAB 3: PAYMENTS -->
                <div class="tab-pane fade" id="payments" role="tabpanel">
                    <div class="text-right mb-3">
                        <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#modal-add-payment">
                            <i class="fas fa-money-bill-wave"></i> Tạo Phiếu Thu
                        </button>
                    </div>

                    <?php 
                        $payments = (new \App\Models\Payment())->where('booking_id', $booking['id']);
                    ?>

                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Ngày thu</th>
                                <th>Số phiếu</th>
                                <th>Phương thức</th>
                                <th>Ghi chú</th>
                                <th class="text-right">Số tiền</th>
                                <th class="text-center">Hủy</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $pm): ?>
                            <tr>
                                <td><?= date('d/m/Y H:i', strtotime($pm['paid_at'])) ?></td>
                                <td><?= htmlspecialchars($pm['receipt_no'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($pm['method']) ?></td>
                                <td><?= htmlspecialchars($pm['note'] ?? '') ?></td>
                                <td class="text-right font-weight-bold text-success">+ <?= number_format($pm['amount']) ?></td>
                                <td class="text-center">
                                    <form action="<?= route('booking.payment.delete', ['id' => $booking['id']]) ?>" method="POST" onsubmit="return confirm('Hủy phiếu thu này?')">
                                        <input type="hidden" name="payment_id" value="<?= $pm['id'] ?>">
                                        <button class="btn btn-xs btn-outline-danger"><i class="fas fa-times"></i></button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- MODAL ADD SERVICE -->
<div class="modal fade" id="modal-add-service">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h4 class="modal-title">Thêm Dịch Vụ / Phụ Thu</h4>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form action="<?= route('booking.service.add', ['id' => $booking['id']]) ?>" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Loại dịch vụ</label>
                        <select name="type" class="form-control">
                            <option value="FLIGHT">Vé máy bay</option>
                            <option value="HOTEL">Phòng khách sạn</option>
                            <option value="VISA">Visa / Giấy tờ</option>
                            <option value="OTHER">Khác</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tên dịch vụ / Mô tả</label>
                        <input type="text" name="name" class="form-control" placeholder="VD: Phụ thu phòng đơn..." required>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Số lượng</label>
                                <input type="number" name="qty" class="form-control" value="1" min="1" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Đơn giá bán (VNĐ)</label>
                                <input type="number" name="unit_price" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Đơn giá vốn (Chi phí - Optional)</label>
                        <input type="number" name="unit_cost" class="form-control" value="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Lưu lại</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL ADD PAYMENT -->
<div class="modal fade" id="modal-add-payment">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h4 class="modal-title">Tạo Phiếu Thu Tiền</h4>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form action="<?= route('booking.payment.add', ['id' => $booking['id']]) ?>" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Số tiền thu (VNĐ) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control" required min="1000">
                    </div>
                    <div class="form-group">
                        <label>Phương thức</label>
                        <select name="method" class="form-control">
                            <option value="CASH">Tiền mặt</option>
                            <option value="TRANSFER">Chuyển khoản</option>
                            <option value="CARD">Thẻ tín dụng</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Mã phiếu / Số bút toán</label>
                        <input type="text" name="receipt_no" class="form-control" placeholder="PT001...">
                    </div>
                    <div class="form-group">
                        <label>Ghi chú</label>
                        <textarea name="note" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-success">Xác nhận thu</button>
                </div>
            </form>
        </div>
    </div>
</div>