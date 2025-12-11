<?php
// Xác định đây là Tour Custom hay không dựa trên dữ liệu $booking được truyền vào từ view cha (show.php)
// Cần kiểm tra kỹ isset để tránh lỗi nếu biến không tồn tại
$isCustom = isset($booking['is_custom']) && $booking['is_custom'] == 1;
?>


<!-- ============================================================= -->
<!-- 1. MODAL: THÊM KHÁCH VÀO ĐOÀN (ADD TRAVELER)                   -->
<!-- ============================================================= -->
<div class="modal fade" id="modalAddTraveler">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h4 class="modal-title"><i class="fas fa-user-plus mr-1"></i> Thêm thành viên vào đoàn</h4>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
           
            <form action="<?= route('booking.traveler.add', ['id' => $booking['id']]) ?>" method="POST">
                <div class="modal-body">
                    <div class="alert alert-light border">
                        <i class="fas fa-info-circle text-info"></i> Khách mới sẽ được thêm vào danh sách và số lượng (Pax) sẽ tự động tăng.
                    </div>


                    <!-- Thông tin khách -->
                    <div class="form-group">
                        <label>Họ và tên <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" class="form-control" required placeholder="Nhập tên khách...">
                    </div>


                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Giới tính</label>
                                <select name="gender" class="form-control">
                                    <option value="MALE">Nam</option>
                                    <option value="FEMALE">Nữ</option>
                                    <option value="OTHER">Khác</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Ngày sinh</label>
                                <input type="date" name="dob" class="form-control">
                            </div>
                        </div>
                    </div>


                    <div class="form-group">
                        <label>Ghi chú</label>
                        <input type="text" name="note" class="form-control" placeholder="VD: Ăn chay, Dị ứng...">
                    </div>


                    <!-- [QUAN TRỌNG] Phần nhập phí phát sinh chỉ hiện cho Tour Custom -->
                    <?php if ($isCustom): ?>
                        <div class="form-group bg-light p-3 rounded border border-warning mt-3">
                            <label class="text-warning font-weight-bold">
                                <i class="fas fa-dollar-sign"></i> Chi phí phát sinh (Tour Yêu Cầu)
                            </label>
                            <div class="input-group">
                                <input type="text" name="extra_price" class="form-control font-weight-bold text-success money-mask" value="0">
                                <div class="input-group-append">
                                    <span class="input-group-text">đ</span>
                                </div>
                            </div>
                            <small class="text-muted">
                                Nhập số tiền chênh lệch do việc thêm khách này gây ra. Hệ thống sẽ cộng vào <b>Tổng tiền hợp đồng</b>.
                            </small>
                        </div>
                    <?php endif; ?>


                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success font-weight-bold">Lưu & Thêm</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- ============================================================= -->
<!-- 3. MODAL: CẬP NHẬT TRẠNG THÁI BOOKING                          -->
<!-- ============================================================= -->
<div class="modal fade" id="modalUpdateStatus">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Cập nhật Trạng thái</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="<?= route('booking.update.status', ['id' => $booking['id']]) ?>" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Trạng thái mới</label>
                        <select name="state" class="form-control">
                            <option value="PLACED" <?= $booking['state']=='PLACED'?'selected':'' ?>>Mới đặt (Placed)</option>
                            <option value="DEPOSITED" <?= $booking['state']=='DEPOSITED'?'selected':'' ?>>Đã cọc (Deposited)</option>
                            <option value="COMPLETED" <?= $booking['state']=='COMPLETED'?'selected':'' ?>>Hoàn thành (Completed)</option>
                            <option value="CANCELLED" <?= $booking['state']=='CANCELLED'?'selected':'' ?>>Hủy (Cancelled)</option>
                        </select>
                        <small class="text-muted mt-2 d-block">
                            Lưu ý:
                            <br>- <b>Hoàn thành:</b> Khi tour kết thúc và thanh toán đủ.
                            <br>- <b>Hủy:</b> Trả lại chỗ trống cho hệ thống.
                        </small>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- ============================================================= -->
<!-- 4. MODAL: THÊM DỊCH VỤ (GIỮ NGUYÊN)                           -->
<!-- ============================================================= -->
<div class="modal fade" id="modal-add-service">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= route('booking.service.add', ['id' => $booking['id']]) ?>?tab=services" method="POST">
                <div class="modal-header"><h5 class="modal-title">Thêm Dịch Vụ</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
                <div class="modal-body">
                    <input type="text" name="name" class="form-control mb-2" placeholder="Tên dịch vụ (Vé máy bay...)" required>
                    <div class="row">
                        <div class="col-6"><input type="number" name="qty" class="form-control mb-2" value="1" placeholder="SL" required></div>
                        <div class="col-6"><input type="number" name="unit_price" class="form-control mb-2" placeholder="Đơn giá bán" required></div>
                    </div>
                    <div class="form-group">
                        <label>Đơn giá vốn (Cost - Để tính lãi)</label>
                        <input type="number" name="unit_cost" class="form-control" value="0">
                    </div>
                    <select name="type" class="form-control">
                        <option value="OTHER">Khác</option>
                        <option value="FLIGHT">Vé máy bay</option>
                        <option value="HOTEL">Phòng khách sạn</option>
                        <option value="VISA">Visa</option>
                    </select>
                </div>
                <div class="modal-footer"><button class="btn btn-primary">Lưu</button></div>
            </form>
        </div>
    </div>
</div>


<!-- ============================================================= -->
<!-- 5. MODAL: THU TIỀN (GIỮ NGUYÊN)                               -->
<!-- ============================================================= -->
<div class="modal fade" id="modal-add-payment">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= route('booking.payment.add', ['id' => $booking['id']]) ?>?tab=payments" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Thu Tiền / Thanh toán</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Số tiền thu (VNĐ)</label>
                        <input type="number" name="amount" class="form-control font-weight-bold text-success" placeholder="Nhập số tiền" required>
                    </div>
                    <div class="form-group">
                        <label>Mã phiếu / Chứng từ</label>
                        <input type="text" name="receipt_no" class="form-control" placeholder="PT-001">
                    </div>
                    <div class="form-group">
                        <label>Phương thức</label>
                        <select name="method" class="form-control">
                            <option value="TRANSFER">Chuyển khoản</option>
                            <option value="CASH">Tiền mặt</option>
                            <option value="POS">Thẻ</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Ghi chú</label>
                        <textarea name="note" class="form-control" rows="2" placeholder="Ghi chú thu tiền..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success font-weight-bold">
                        <i class="fas fa-check"></i> Xác nhận Thu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

