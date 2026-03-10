<?php
require 'config.php';
if ($_SESSION['role'] !== 'professor') { header("Location: login.php"); exit(); }

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT p.*, u.full_name_en, u.email FROM Proposals p JOIN Users u ON p.student_id = u.user_id WHERE p.proposal_id = ?");
$stmt->execute([$id]);
$p = $stmt->fetch();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $upd = $pdo->prepare("UPDATE Proposals SET status = ? WHERE proposal_id = ?");
    $upd->execute([$_POST['status'], $id]);
    header("Location: prof_dashboard.php");
}
?>
<!DOCTYPE html>
<html>
<head><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-slate-50 p-10">
    <div class="max-w-4xl mx-auto bg-white p-12 rounded-[2.5rem] shadow-2xl border border-slate-100">
        <h1 class="text-3xl font-black text-slate-800 mb-2"><?= $p['title_en'] ?></h1>
        <p class="text-blue-600 font-bold mb-8">Submitted by: <?= $p['full_name_en'] ?> (<?= $p['email'] ?>)</p>

        <div class="space-y-10">
            <section>
                <h4 class="text-xs font-black uppercase text-slate-400 mb-3 tracking-widest border-l-4 border-blue-600 pl-3">Problem Statement</h4>
                <div class="text-slate-600 leading-relaxed bg-slate-50 p-6 rounded-2xl"><?= nl2br($p['problem_statement']) ?></div>
            </section>

            <section>
                <h4 class="text-xs font-black uppercase text-slate-400 mb-3 tracking-widest border-l-4 border-blue-600 pl-3">Research Objectives</h4>
                <div class="text-slate-600 leading-relaxed bg-slate-50 p-6 rounded-2xl"><?= nl2br($p['project_objectives']) ?></div>
            </section>

            <form method="POST" class="bg-[#1e293b] p-8 rounded-[2rem] text-white flex flex-col md:flex-row items-center gap-6">
                <div class="flex-1">
                    <h3 class="font-bold text-lg mb-1">Set Decision</h3>
                    <p class="text-slate-400 text-xs">Update the student's project status.</p>
                </div>
                <select name="status" class="bg-slate-700 border-none p-4 rounded-xl text-white outline-none w-full md:w-auto">
                    <option value="Pending" <?= $p['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="Under Review" <?= $p['status'] == 'Under Review' ? 'selected' : '' ?>>Under Review</option>
                    <option value="Modifications Required" <?= $p['status'] == 'Modifications Required' ? 'selected' : '' ?>>Request Mods</option>
                    <option value="Approved" <?= $p['status'] == 'Approved' ? 'selected' : '' ?>>Approve Project</option>
                </select>
                <button class="bg-blue-600 px-10 py-4 rounded-xl font-bold hover:bg-blue-500 transition-all">Update Status</button>
            </form>
        </div>
    </div>
</body>
</html>