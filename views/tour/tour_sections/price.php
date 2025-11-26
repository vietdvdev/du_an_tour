<form method="POST" action="<?= route('tour.update.price', ['id' => $tourId]) ?>">
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> <b>Lưu ý:</b>
        <ul>
            <li>Giá áp dụng cho 1 khách.</li>
            <li>Không được nhập trùng khoảng thời gian cho cùng một loại khách.</li>
        </ul>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover" id="price-table">
            <thead class="thead-light">
                <tr>
                    <th width="20%">Loại khách</th>
                    <th width="20%">Giá (VNĐ)</th>
                    <th width="25%">Từ ngày</th>
                    <th width="25%">Đến ngày</th>
                    <th width="10%" class="text-center">Xóa</th>
                </tr>
            </thead>
            <tbody id="price-body">
                <?php 
                $displayPrices = !empty($prices) ? $prices : [];
                foreach ($displayPrices as $k => $p): 
                ?>
                <tr>
                    <input type="hidden" name="prices[<?= $k ?>][id]" value="<?= $p['id'] ?? 0 ?>">
                    <td>
                        <!-- ĐÃ SỬA LỖI Ở DÒNG DƯỚI: Xóa dấu > thừa sau style -->
                        <select name="prices[<?= $k ?>][pax_type]" class="form-control">
                            <option value="ADULT" <?= ($p['pax_type'] == 'ADULT') ? 'selected' : '' ?>>Người lớn</option>
                            <option value="CHILD" <?= ($p['pax_type'] == 'CHILD') ? 'selected' : '' ?>>Trẻ em</option>
                            <option value="INFANT" <?= ($p['pax_type'] == 'INFANT') ? 'selected' : '' ?>>Em bé</option>
                        </select>
                    </td>
                    <td>
                        <input type="number" name="prices[<?= $k ?>][base_price]" class="form-control" value="<?= $p['base_price'] ?>" required min="0">
                    </td>
                    <td>
                        <input type="date" name="prices[<?= $k ?>][effective_from]" class="form-control" value="<?= $p['effective_from'] ?>" required>
                    </td>
                    <td>
                        <input type="date" name="prices[<?= $k ?>][effective_to]" class="form-control" value="<?= $p['effective_to'] ?>" required>
                    </td>
                    <td class="text-center align-middle">
                        <button type="button" class="btn btn-danger btn-sm remove-row">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-3 d-flex justify-content-between">
        <button type="button" class="btn btn-success" id="add-price-row">
            <i class="fas fa-plus"></i> Thêm dòng giá
        </button>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Lưu Bảng Giá
        </button>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Hàm thêm dòng
        document.getElementById('add-price-row').addEventListener('click', function() {
            const tbody = document.getElementById('price-body');
            // Sử dụng Date.now() để tạo key duy nhất, tránh trùng lặp index khi xóa/thêm nhiều lần
            const index = Date.now(); 
            
            const html = `
                <tr>
                    <input type="hidden" name="prices[${index}][id]" value="0">
                    <td>
                        <select name="prices[${index}][pax_type]" class="form-control">
                            <option value="ADULT">Người lớn</option>
                            <option value="CHILD">Trẻ em</option>
                            <option value="INFANT">Em bé</option>
                        </select>
                    </td>
                    <td>
                        <input type="number" name="prices[${index}][base_price]" class="form-control" value="0" required min="0">
                    </td>
                    <td>
                        <input type="date" name="prices[${index}][effective_from]" class="form-control" required>
                    </td>
                    <td>
                        <input type="date" name="prices[${index}][effective_to]" class="form-control" required>
                    </td>
                    <td class="text-center align-middle">
                        <button type="button" class="btn btn-danger btn-sm remove-row">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>`;
            
            tbody.insertAdjacentHTML('beforeend', html);
        });

        // Sự kiện ủy quyền (Event Delegation) cho nút xóa
        // Giúp nút xóa hoạt động cả với các dòng mới thêm bằng JS
        document.getElementById('price-body').addEventListener('click', function(e) {
            if (e.target.closest('.remove-row')) {
                if(confirm('Bạn có chắc muốn xóa dòng giá này?')) {
                    e.target.closest('tr').remove();
                }
            }
        });
    });
</script>