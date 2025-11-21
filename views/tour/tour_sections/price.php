<form method="POST" action="<?= route('tour.update.price', ['id' => $tourId]) ?>">
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> Lưu ý: Không được nhập trùng khoảng thời gian cho cùng một loại khách.
    </div>

    <table class="table table-bordered" id="price-table">
        <thead>
            <tr>
                <th>Loại khách</th>
                <th>Giá (VNĐ)</th>
                <th>Từ ngày</th>
                <th>Đến ngày</th>
                <th>Hành động</th>
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
                    <select style="height: auto; padding: 8px; border-radius: 10px;"> name="prices[<?= $k ?>][pax_type]" class="form-control">
                        <option value="ADULT" <?= ($p['pax_type'] == 'ADULT') ? 'selected' : '' ?>>Người lớn</option>
                        <option value="CHILD" <?= ($p['pax_type'] == 'CHILD') ? 'selected' : '' ?>>Trẻ em</option>
                        <option value="INFANT" <?= ($p['pax_type'] == 'INFANT') ? 'selected' : '' ?>>Em bé</option>
                    </select>
                </td>
                <td>
                    <input type="number" name="prices[<?= $k ?>][base_price]" class="form-control" value="<?= $p['base_price'] ?>" required>
                </td>
                <td>
                    <input type="date" name="prices[<?= $k ?>][effective_from]" class="form-control" value="<?= $p['effective_from'] ?>" required>
                </td>
                <td>
                    <input type="date" name="prices[<?= $k ?>][effective_to]" class="form-control" value="<?= $p['effective_to'] ?>" required>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove()"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <button type="button" class="btn btn-secondary" id="add-price-row">+ Thêm dòng giá</button>
    <button type="submit" class="btn btn-primary">Lưu Bảng Giá</button>
</form>

<script>
    document.getElementById('add-price-row').addEventListener('click', function() {
        const tbody = document.getElementById('price-body');
        const index = tbody.children.length + Math.floor(Math.random() * 1000); // Random ID để tránh trùng key JS
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
                <td><input type="number" name="prices[${index}][base_price]" class="form-control" value="0" required></td>
                <td><input type="date" name="prices[${index}][effective_from]" class="form-control" required></td>
                <td><input type="date" name="prices[${index}][effective_to]" class="form-control" required></td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove()"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`;
        tbody.insertAdjacentHTML('beforeend', html);
    });
</script>