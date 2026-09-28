<?php
$userId = $_SESSION['user_id'];
$isEdit = false;
$profileData = [];

$query = "SELECT u.username, u.email, u.contact, f.*
          FROM users AS u
          LEFT JOIN farmers AS f ON u.id = f.user_id
          WHERE u.id = ?";

$selectFarmerProfile = selectData($pdo, $query, [$userId]);
if (!empty($selectFarmerProfile)) {
    $profileData = $selectFarmerProfile[0];
    if (!empty($profileData['farmer_id'])) {
        $isEdit = true;
    }
}

$old = $_SESSION['old_input'] ?? [];
unset($_SESSION['old_input']);

include __DIR__ . '/../../../public/components/farmer-sidebar.php';
?>

<div class="f-wrap">
    <div class="f-page-head">
        <div>
            <h2><i class="bi bi-person-badge"></i> <?php echo $isEdit ? 'Update Farmer Profile' : 'Create Farmer Profile'; ?></h2>
            <p>Complete your stall details so customers know where to find you.</p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="f-card">
                <div class="f-card-head">
                    <h5><i class="bi bi-person-lines-fill"></i> <?php echo $isEdit ? 'Profile Details' : 'Basic Details'; ?></h5>
                </div>
                <div class="f-card-body">
                    <?php echo get_flash(); ?>

                    <form action="../private/backend-scripting/save-profile.php" method="POST" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="f-field">
                                    <label for="contact">Contact Number</label>
                                    <input type="text" id="contact" name="contact" class="form-control" value="<?php echo sanitize_output($old['contact'] ?? $profileData['contact'] ?? ''); ?>" required placeholder="Enter contact number">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="f-field">
                                    <label for="stall_name">Stall Name</label>
                                    <input type="text" id="stall_name" name="stall_name" class="form-control" value="<?php echo sanitize_output($old['stall_name'] ?? $profileData['stall_name'] ?? ''); ?>" required placeholder="Enter stall name">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="f-field">
                                    <label for="contact_person">Contact Person</label>
                                    <input type="text" id="contact_person" name="contact_person" class="form-control" value="<?php echo sanitize_output($old['contact_person'] ?? $profileData['contact_person'] ?? $profileData['username'] ?? ''); ?>" required placeholder="Contact person name">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="f-field">
                                    <label for="profile_image">Profile Image</label>
                                    <input type="file" id="profile_image" name="profile_image" class="form-control">
                                    <?php if (!empty($profileData['profile_image'])): ?>
                                        <div class="mt-2">
                                            <small class="f-muted d-block mb-1">Current Image:</small>
                                            <img src="<?php echo ML_asset('Uploads/' . basename($profileData['profile_image'])); ?>" width="60" height="60" class="rounded-circle object-fit-cover border" style="border-color:var(--f-line) !important;">
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="f-field">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" class="form-control" rows="3" placeholder="Tell us about your stall or products..."><?php echo sanitize_output($old['description'] ?? $profileData['description'] ?? ''); ?></textarea>
                        </div>

                        <div class="f-field">
                            <label for="address">Address</label>
                            <textarea id="address" name="address" class="form-control" rows="2" placeholder="Enter complete location address..."><?php echo sanitize_output($old['address'] ?? $profileData['address'] ?? ''); ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="f-field">
                                    <label for="latitude">Latitude</label>
                                    <input type="text" id="latitude" name="latitude" class="form-control" value="<?php echo sanitize_output($old['latitude'] ?? $profileData['latitude'] ?? ''); ?>" placeholder="e.g. 24.8607">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="f-field">
                                    <label for="longitude">Longitude</label>
                                    <input type="text" id="longitude" name="longitude" class="form-control" value="<?php echo sanitize_output($old['longitude'] ?? $profileData['longitude'] ?? ''); ?>" placeholder="e.g. 67.0011">
                                </div>
                            </div>
                        </div>

                        <div class="f-card-head px-0 mb-3" style="border-bottom:none;">
                            <h5><i class="bi bi-clock"></i> Order &amp; Pickup Settings</h5>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="f-field">
                                    <label for="pickup_start">Pickup Start Time</label>
                                    <input type="time" id="pickup_start" name="pickup_start" class="form-control" value="<?php echo sanitize_output($old['pickup_start'] ?? $profileData['pickup_start'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="f-field">
                                    <label for="pickup_end">Pickup End Time</label>
                                    <input type="time" id="pickup_end" name="pickup_end" class="form-control" value="<?php echo sanitize_output($old['pickup_end'] ?? $profileData['pickup_end'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="f-field">
                                    <label for="cutoff_time">Order Cut-off Time</label>
                                    <input type="time" id="cutoff_time" name="cutoff_time" class="form-control" value="<?php echo sanitize_output($old['cutoff_time'] ?? $profileData['cutoff_time'] ?? ''); ?>">
                                    <small class="f-hint">Orders after this time shift to the next day.</small>
                                </div>
                            </div>
                        </div>

                        <button type="submit" name="save_profile" class="f-btn primary block mt-2">
                            <i class="bi bi-check-circle"></i>
                            <?php echo $isEdit ? 'Update Profile' : 'Save Profile'; ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../../public/components/farmer-footer.php'; ?>