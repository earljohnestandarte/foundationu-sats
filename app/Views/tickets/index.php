<?= $this->extend('layout/main') ?>
<?php /** @var object[] $tickets */ ?>
<?php $this->section('title') ?>My Tickets<?php $this->endSection() ?>
<?php $this->section('content') ?>
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            <div>
                <h3 class="mb-1">My Tickets</h3>
                <p class="text-muted mb-0">Track your current tickets and view details in a modern workspace.</p>
            </div>
            <a href="<?= site_url('student/tickets/create') ?>" class="btn btn-primary">New Ticket</a>
        </div>

        <?php if (empty($tickets)): ?>
            <div class="alert alert-info">You have not submitted any tickets yet.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Subject</th>
                            <th>Office</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets as $ticket): ?>
                            <tr>
                                <td><?= esc((string)$ticket->id) ?></td>
                                <td><?= esc((string)$ticket->subject) ?></td>
                                <td><?= esc((string)$ticket->office_name) ?></td>
                                <td>
                                    <?php if ($ticket->status === 'Open'): ?>
                                        <span class="badge rounded-pill bg-primary">Open</span>
                                    <?php elseif ($ticket->status === 'In Progress'): ?>
                                        <span class="badge rounded-pill bg-warning text-dark">In Progress</span>
                                    <?php elseif ($ticket->status === 'Waiting on Student'): ?>
                                        <span class="badge rounded-pill bg-info text-dark">Waiting on Student</span>
                                    <?php elseif ($ticket->status === 'Resolved'): ?>
                                        <span class="badge rounded-pill bg-success">Resolved</span>
                                    <?php else: ?>
                                        <span class="badge rounded-pill bg-secondary">Closed</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($ticket->priority === 'Low'): ?>
                                        <span class="badge rounded-pill bg-light text-success">Low</span>
                                    <?php elseif ($ticket->priority === 'Medium'): ?>
                                        <span class="badge rounded-pill bg-light text-primary">Medium</span>
                                    <?php elseif ($ticket->priority === 'High'): ?>
                                        <span class="badge rounded-pill bg-light text-warning">High</span>
                                    <?php else: ?>
                                        <span class="badge rounded-pill bg-light text-danger"><i class="fas fa-fire me-1"></i>Urgent</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc((string) ($ticket->updated_at ?? $ticket->created_at)) ?></td>
                                <td><a href="<?= site_url('student/tickets/' . $ticket->id) ?>" class="btn btn-sm btn-primary">View</a></td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        <?php endif ?>
    </div>
</div>
<?= $this->endSection() ?>