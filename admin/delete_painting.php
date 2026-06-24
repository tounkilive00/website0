<?php
session_start();
if (empty($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit; }
require_once '../db.php';

$id = intval($_GET['id'] ?? 0);
$painting = getPainting($id);

if ($painting) {
    // Delete the uploaded media file if it's in the uploads folder
    $uploadedPath = '../uploads/' . $painting['media_file'];
    if (file_exists($uploadedPath)) {
        unlink($uploadedPath);
    }
    $stmt = getDB()->prepare("DELETE FROM paintings WHERE id = ?");
    $stmt->execute([$id]);
}

header('Location: dashboard.php?msg=' . urlencode('🗑 Œuvre supprimée.'));
exit;
