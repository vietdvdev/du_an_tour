<form action="<?= route('tour.update.price', ['id' => $tour['id']]) ?>" method="POST">
   
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> <b>Cấu hình giá Tour:</b>
        <br>
        - Nhập giá áp dụng cho 1 khách.
        <br>
        - Hệ thống sẽ tự động áp dụng giá này cho mọi thời điểm.
    </div>
   
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-bordered table-striped mb-0">
                <thead class="bg-light">
                    <tr>
                        <th style="width: 40%">Đối tượng</th>
                        <th style="width: 60%">Giá cơ bản (VNĐ)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        // Tìm giá hiện tại trong DB để điền vào form
                        $priceAdult = null;
                        $priceChild = null;
                        $priceInfant = null;


                        foreach($prices as $p) {
                            if ($p['pax_type'] == 'ADULT') $priceAdult = $p;
                            if ($p['pax_type'] == 'CHILD') $priceChild = $p;
                            if ($p['pax_type'] == 'INFANT') $priceInfant = $p;
                        }
                    ?>


                    <!-- 1. Dòng Người lớn (ADULT) -->
                    <tr>
                        <td class="align-middle">
                            <i class="fas fa-user text-primary mr-2"></i>
                            <strong>Người lớn (Adult)</strong>
                            <div class="text-muted small ml-4">Từ 12 tuổi trở lên</div>
                        </td>
                        <td>
                            <!-- Input ẩn để gửi loại khách và ID -->
                            <input type="hidden" name="prices[0][pax_type]" value="ADULT">
                            <input type="hidden" name="prices[0][id]" value="<?= $priceAdult['id'] ?? '' ?>">
                           
                            <!-- Input nhập giá -->
                            <div class="input-group">
                                <input type="number" name="prices[0][base_price]" class="form-control font-weight-bold text-primary"
                                       placeholder="0" min="0"
                                       value="<?= isset($priceAdult['base_price']) ? (int)$priceAdult['base_price'] : 0 ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text">VNĐ</span>
                                </div>
                            </div>
                        </td>
                    </tr>


                    <!-- 2. Dòng Trẻ em (CHILD) -->
                    <tr>
                        <td class="align-middle">
                            <i class="fas fa-child text-success mr-2"></i>
                            <strong>Trẻ em (Child)</strong>
                            <div class="text-muted small ml-4">Từ 2 đến 11 tuổi</div>
                        </td>
                        <td>
                            <input type="hidden" name="prices[1][pax_type]" value="CHILD">
                            <input type="hidden" name="prices[1][id]" value="<?= $priceChild['id'] ?? '' ?>">
                           
                            <div class="input-group">
                                <input type="number" name="prices[1][base_price]" class="form-control font-weight-bold text-success"
                                       placeholder="0" min="0"
                                       value="<?= isset($priceChild['base_price']) ? (int)$priceChild['base_price'] : 0 ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text">VNĐ</span>
                                </div>
                            </div>
                        </td>
                    </tr>


                    <!-- 3. Dòng Em bé (INFANT) -->
                    <tr>
                        <td class="align-middle">
                            <i class="fas fa-baby text-secondary mr-2"></i>
                            <strong>Em bé (Infant)</strong>
                            <div class="text-muted small ml-4">Dưới 2 tuổi</div>
                        </td>
                        <td>
                            <input type="hidden" name="prices[2][pax_type]" value="INFANT">
                            <input type="hidden" name="prices[2][id]" value="<?= $priceInfant['id'] ?? '' ?>">
                           
                            <div class="input-group">
                                <input type="number" name="prices[2][base_price]" class="form-control font-weight-bold text-secondary"
                                       placeholder="0" min="0"
                                       value="<?= isset($priceInfant['base_price']) ? (int)$priceInfant['base_price'] : 0 ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text">VNĐ</span>
                                </div>
                            </div>
                        </td>
                    </tr>


                </tbody>
            </table>
        </div>
    </div>


    <div class="mt-3 text-right">
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="fas fa-save"></i> Cập nhật Bảng giá
        </button>
    </div>
</form>

