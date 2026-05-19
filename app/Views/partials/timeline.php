<?php /** @var array $timeline */ ?>
<?php if (! empty($timeline)): ?>
    <div class="timeline-list">
        <?php foreach ($timeline as $event): ?>
            <div class="timeline-item <?= $event['type'] ?>">
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                    <div class="timeline-header">
                        <i class="fas <?= $event['icon'] ?> timeline-icon"></i>
                        <span class="timeline-label"><?= $event['label'] ?></span>
                    </div>
                    <small style="color: var(--fu-on-surface-variant);"><?= date('M j, g:i A', strtotime($event['timestamp'])) ?></small>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p class="mb-0" style="color: var(--fu-on-surface-variant);">No timeline updates yet.</p>
<?php endif; ?>
