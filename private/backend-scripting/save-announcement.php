<?php
session_start();
require_once "../config/dbconnect.php";
require_once "../config/functions.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect("admin-announcements");
}

verify_csrf();
$admin = validateAdmin();
$adminId = $admin['id'];

$announcementId = (int)($_POST['announcement_id'] ?? 0);

// ---- ADD ----
if (isset($_POST['publish_announcement'])) {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if (empty($title) || empty($content)) {
        set_flash("error", "Title and content are required.");
        redirect("admin-announcements");
    }

    try {
        insertData($pdo, "announcements", [
            "admin_id"  => $adminId,
            "title"     => $title,
            "content"   => $content,
            "is_active" => 1
        ]);

        /* No fan-out copy into notifications. It used to INSERT one
           'announcement' row per user here, which had two problems: those
           copies are separate rows, so deactivating or deleting the
           announcement later left the copies sitting in every customer's
           notification list, and the customer saw the same text twice once
           announcements were also rendered from this table. Customers read
           announcements straight from announcements WHERE is_active = 1 on
           the notification page, so the admin's toggle and delete now actually
           withdraw the notice. */
        set_flash("success", "Announcement published to all users.");
    } catch (Exception $e) {
        set_flash("error", "Could not publish announcement. " . $e->getMessage());
    }

    redirect("admin-announcements");
}

// ---- TOGGLE ----
if (isset($_POST['toggle_announcement'])) {
    $row = selectData($pdo, "SELECT is_active FROM announcements WHERE announcement_id = ?", [$announcementId]);
    if (!empty($row)) {
        $newVal = $row[0]['is_active'] ? 0 : 1;
        $stmt = $pdo->prepare("UPDATE announcements SET is_active = ? WHERE announcement_id = ?");
        $stmt->execute([$newVal, $announcementId]);
        set_flash("success", $newVal ? "Announcement activated." : "Announcement deactivated.");
    }
    redirect("admin-announcements");
}

// ---- DELETE ----
if (isset($_POST['delete_announcement'])) {
    $stmt = $pdo->prepare("DELETE FROM announcements WHERE announcement_id = ?");
    if ($stmt->execute([$announcementId])) {
        set_flash("success", "Announcement deleted.");
    } else {
        set_flash("error", "Could not delete announcement.");
    }
    redirect("admin-announcements");
}