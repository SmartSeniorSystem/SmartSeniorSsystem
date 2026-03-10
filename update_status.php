<?php
require 'config.php';
if ($_SESSION['role'] !== 'professor') { header("Location: login.php"); exit(); }

if (isset($_POST['proposal_id']) && isset($_POST['new_status'])) {
    $stmt = $pdo->prepare("UPDATE Proposals SET status = ? WHERE proposal_id = ?");
    $stmt->execute([$_POST['new_status'], $_POST['proposal_id']]);
}

header("Location: prof_dashboard.php?msg=Status Updated");
?>