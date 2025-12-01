<div class="modal fade" id="modal-add-service">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= route('booking.service.add', ['id' => $booking['id']]) ?>?tab=services" method="POST">
                <div class="modal-header"><h5 class="modal-title">Thêm Dịch Vụ</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
                <div class="modal-body">
                    <input type="text" name="name" class="form-control mb-2" placeholder="Tên dịch vụ (Vé máy bay...)" required>
                    <div class="row">
                        <div class="col-6"><input type="number" name="qty" class="form-control mb-2" value="1" placeholder="SL" required></div>
                        <div class="col-6"><input type="number" name="unit_price" class="form-control mb-2" placeholder="Đơn giá" required></div>
                    </div>
                    <div class="form-group">
                        <label>Đơn giá vốn (Để tính lãi)</label>
                        <input type="number" name="unit_cost" class="form-control" value="0">
                    </div>
                    <select name="type" class="form-control"><option value="OTHER">Khác</option><option value="FLIGHT">Vé máy bay</option><option value="HOTEL">Phòng</option></select>
                </div>
                <div class="modal-footer"><button class="btn btn-primary">Lưu</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-add-payment">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= route('booking.payment.add', ['id' => $booking['id']]) ?>?tab=payments" method="POST">
                <div class="modal-header"><h5 class="modal-title">Thu Tiền</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
                <div class="modal-body">
                    <input type="number" name="amount" class="form-control mb-2" placeholder="Số tiền thu" required>
                    <input type="text" name="receipt_no" class="form-control mb-2" placeholder="Mã phiếu / Chứng từ">
                    <select name="method" class="form-control mb-2"><option value="TRANSFER">Chuyển khoản</option><option value="CASH">Tiền mặt</option></select>
                    <textarea name="note" class="form-control" placeholder="Ghi chú"></textarea>
                </div>
                <div class="modal-footer"><button class="btn btn-success">Xác nhận</button></div>
            </form>
        </div>
    </div>
</div>