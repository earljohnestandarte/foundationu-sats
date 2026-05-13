<?= $this->extend('layout/main') ?>
<?php /** @var object $ticket */ ?>
<?php /** @var object|null $feedback */ ?>
<?php $this->section('title') ?>Rate Your Experience - SATS<?php $this->endSection() ?>
<?php $this->section('content') ?>
<section class="section-padding">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="mb-4">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <a href="<?= site_url('student/tickets') ?>" class="text-decoration-none">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
                <h3 class="fw-bold mb-1" style="color: var(--fu-primary); font-size: 28px;">Rate Your Experience</h3>
                <p style="color: var(--fu-on-surface-variant);">How was the resolution for your concern?</p>
            </div>

            <div class="card-fu mb-3">
                <div class="p-3" style="background-color: var(--fu-surface-container-low); border-bottom: 1px solid var(--fu-outline-variant);">
                    <strong>#FAU-<?= str_pad(esc((string)$ticket->id), 4, '0', STR_PAD_LEFT) ?>:</strong> <?= esc($ticket->subject) ?>
                </div>
            </div>

            <div class="card-fu">
                <div class="p-4">
                    <?php if ($feedback): ?>
                        <div class="text-center mb-3">
                            <i class="fas fa-check-circle fa-3x mb-2" style="color: var(--fu-success);"></i>
                            <p class="fw-semibold">You've already submitted your feedback.</p>
                        </div>
                        <div class="star-display text-center mb-3">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star fa-2x <?= $i <= $feedback->rating ? 'star-filled' : 'star-empty' ?>" style="margin: 0 2px;"></i>
                            <?php endfor; ?>
                        </div>
                        <?php if ($feedback->comment): ?>
                            <p class="text-center mb-0" style="color: var(--fu-on-surface-variant); font-style: italic;">"<?= esc($feedback->comment) ?>"</p>
                        <?php endif; ?>
                        <div class="text-center mt-3">
                            <a href="<?= site_url('student/tickets') ?>" class="btn btn-fu-primary">Back to My Concerns</a>
                        </div>
                    <?php else: ?>
                        <?= form_open('student/tickets/' . $ticket->id . '/feedback') ?>
                        <?= csrf_field() ?>
                        <div class="text-center mb-4">
                            <p class="fw-semibold mb-3">How would you rate the service?</p>
                            <div class="star-rating">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>" />
                                    <label for="star<?= $i ?>" title="<?= $i ?> star<?= $i > 1 ? 's' : '' ?>">
                                        <i class="fas fa-star"></i>
                                    </label>
                                <?php endfor; ?>
                            </div>
                            <?php if (isset($validation)): ?>
                                <div class="form-text" style="color: var(--fu-error);"><?= $validation->getError('rating') ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="mb-4">
                            <label for="comment" class="form-label fw-semibold">Additional comments (optional)</label>
                            <textarea name="comment" id="comment" class="form-control" rows="3" placeholder="Tell us more about your experience..."><?= old('comment') ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-fu-primary w-100">Submit Feedback</button>
                        <?= form_close() ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="text-center mt-3">
                <a href="<?= site_url('student/tickets') ?>" class="text-decoration-none" style="color: var(--fu-on-surface-variant);">Skip</a>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
