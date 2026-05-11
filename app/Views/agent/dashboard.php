<?= $this->extend('layout/main') ?>
<?php /** @var object[] $tickets */ ?>
<?php $this->section('title') ?>Agent Dashboard<?php $this->endSection() ?>
<?php $this->section('content') ?>
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
            <div>
                <h3 class="mb-1">Agent Dashboard</h3>
                <p class="text-muted mb-0">Review tickets for your office and take action with a streamlined view.</p>
            </div>
        </div>

        <?php if (empty($tickets)): ?>
            <div class="alert alert-info">No tickets are currently assigned to your office.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Subject</th>
                            <th>Requester</th>
                            <th>Office</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Resolver</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets as $ticket): ?>
                            <tr>
                                <td><?= esc((string)$ticket->id) ?></td>
                                <td><?= esc((string)$ticket->subject) ?></td>
                                <td><?= esc((string)$ticket->requester_name) ?></td>
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
                                <td><?= esc((string)$ticket->resolver_name ?: 'Unassigned') ?></td>
                                <td class="text-nowrap">
                                    <a href="<?= site_url('agent/view/' . $ticket->id) ?>" class="btn btn-sm btn-primary mb-2">View</a>

                                    <?php if (empty($ticket->resolver_id)): ?>
                                        <?= form_open('agent/assign/' . $ticket->id, ['class' => 'd-inline mb-2']) ?>
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">Assign to me</button>
                                        <?= form_close() ?>
                                    <?php endif ?>

                                    <?= form_open('agent/updateStatus/' . $ticket->id, ['class' => 'mb-2']) ?>
                                    <div class="input-group input-group-sm">
                                        <?= form_dropdown('status', [
                                            'Open' => 'Open',
                                            'In Progress' => 'In Progress',
                                            'Waiting on Student' => 'Waiting on Student',
                                            'Resolved' => 'Resolved',
                                            'Closed' => 'Closed',
                                        ], $ticket->status, ['class' => 'form-select']) ?>
                                        <button class="btn btn-sm btn-primary" type="submit">Update</button>
                                    </div>
                                    <?= form_close() ?>

                                    <?= form_open('agent/addReply/' . $ticket->id) ?>
                                    <?= form_textarea('message', '', ['class' => 'form-control form-control-sm mb-2', 'rows' => 2, 'placeholder' => 'Reply...']) ?>
                                    <button type="submit" class="btn btn-sm btn-primary">Reply</button>
                                    <?= form_close() ?>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        <?php endif ?>
    </div>
</div>
<?= $this->endSection() ?>