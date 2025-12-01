<div class="timeline timeline-inverse mt-2">
    <?php if(empty($logs)): ?>
        <div class="text-center text-muted p-3">Chưa có lịch sử thay đổi.</div>
    <?php else: ?>
        <?php foreach($logs as $log): ?>
            <div>
                <i class="fas fa-history bg-secondary"></i>
                <div class="timeline-item">
                    <span class="time"><i class="far fa-clock"></i> <?= date('H:i d/m', strtotime($log['created_at'])) ?></span>
                    <h3 class="timeline-header no-border">
                        <b><?= htmlspecialchars($log['changed_by']) ?></b>: 
                        <?= htmlspecialchars($log['old_state'] ?? '...') ?> &rarr; <b><?= htmlspecialchars($log['new_state']) ?></b>
                    </h3>
                    <?php if($log['note']): ?><div class="timeline-body"><?= htmlspecialchars($log['note']) ?></div><?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    <div><i class="far fa-clock bg-gray"></i></div>
</div>