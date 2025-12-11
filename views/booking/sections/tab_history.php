<h5 class="card-title text-muted mb-3">
    <i class="fas fa-history mr-1"></i> Lịch sử thay đổi (<?= count($logs) ?>)
</h5>

<div class="timeline timeline-inverse mt-2">
    <?php if(empty($logs)): ?>
        <div class="text-center text-muted p-4">
            <i class="fas fa-history fa-2x mb-2 text-gray-300"></i><br>
            <p>Chưa có lịch sử thay đổi nào.</p>
        </div>
    <?php else: ?>
        <?php foreach($logs as $log): ?>
            <div>
                <i class="fas fa-history bg-secondary"></i>
                <div class="timeline-item">
                    <span class="time"><i class="far fa-clock mr-1"></i> <?= date('H:i d/m/Y', strtotime($log['created_at'])) ?></span>
                    <h3 class="timeline-header no-border">
                        <b><?= htmlspecialchars($log['changed_by'] ?? 'Hệ thống') ?></b>: 
                        <span class="badge badge-light"><?= htmlspecialchars($log['old_state'] ?? '---') ?></span>
                        <i class="fas fa-arrow-right text-muted mx-2"></i>
                        <span class="badge badge-info"><?= htmlspecialchars($log['new_state']) ?></span>
                    </h3>
                    <?php if($log['note']): ?>
                        <div class="timeline-body">
                            <i class="fas fa-comment-alt text-muted mr-1"></i>
                            <?= htmlspecialchars($log['note']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    <div><i class="far fa-clock bg-gray"></i></div>
</div>