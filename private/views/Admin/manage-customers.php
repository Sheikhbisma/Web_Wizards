<?php
validateAdmin();

$query = $pdo->prepare("SELECT id, username, email, status FROM users WHERE role = 'customer' ORDER BY id DESC");
$query->execute();
$customers = $query->fetchAll(PDO::FETCH_ASSOC);

if(isset($_POST['update_status'])){
    verify_csrf();
    $user_id = $_POST['user_id'];
    $status = $_POST['approval_status']; 
    
    if(!empty($user_id)){
        // Seedha users table ka status update kar do
        $updateStatus = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
        $updateStatus->execute([$status, $user_id]);
        
        if($updateStatus){
            set_flash("success", "Customer status updated successfully");
            redirect("manage-customers");
        } else {
            set_flash("error", "Failed to update status");
            redirect("manage-customers");
        }
    } else {
        set_flash("error", "Invalid user ID");
        redirect("manage-customers");
    }
}

include __DIR__ . '/../../../public/components/admin-sidebar.php';
?>

<div class="a-wrap">
    <div class="a-page-head">
        <div>
            <h2><i class="bi bi-person-badge-fill"></i> Manage Customers</h2>
            <p>Activate or deactivate customer accounts.</p>
        </div>
        <div class="a-actions">
            <span class="a-chip"><i class="bi bi-people"></i> <?php echo count($customers); ?> customers</span>
            <span class="a-chip"><i class="bi bi-person-check"></i> <?php echo count(array_filter($customers, function ($c) { return ($c['status'] ?? 'active') === 'active'; })); ?> active</span>
        </div>
    </div>

    <?php echo get_flash(); ?>

    <div class="a-card">
        <div class="a-card-head">
            <h5><i class="bi bi-list-ul"></i> All Customers</h5>
            <a href="index.php?page=admin-dashboard" class="a-btn ghost sm"><i class="bi bi-speedometer2"></i> Dashboard</a>
        </div>
        <div class="a-card-body flush">
            <?php if (empty($customers)): ?>
                <div class="a-empty"><i class="bi bi-people"></i>No customers found.</div>
            <?php else: ?>
                <div class="a-table-wrap">
                    <table class="a-table">
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customers as $row): ?>
                                <tr>
                                    <td><?php echo (int)$row['id']; ?></td>
                                    <td><strong><?php echo sanitize_output($row['username']); ?></strong></td>
                                    <td><?php echo sanitize_output($row['email']); ?></td>
                                    <td><?php echo adminUserBadge($row['status'] ?? 'active'); ?></td>
                                    <td>
                                        <form action="" method="POST" class="d-inline">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="user_id" value="<?php echo (int)$row['id']; ?>">
                                            <?php if (($row['status'] ?? 'active') === 'active'): ?>
                                                <input type="hidden" name="approval_status" value="deactivated">
                                                <button type="submit" name="update_status" class="a-btn danger-soft sm" onclick="return confirm('Are you sure you want to deactivate this customer?');">Deactivate</button>
                                            <?php else: ?>
                                                <input type="hidden" name="approval_status" value="active">
                                                <button type="submit" name="update_status" class="a-btn success-soft sm">Activate</button>
                                            <?php endif; ?>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../../public/components/admin-footer.php'; ?>