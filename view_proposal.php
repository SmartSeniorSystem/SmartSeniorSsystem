<?php
require 'config.php';
if ($_SESSION['role'] !== 'professor') { header("Location: login.php"); exit(); }

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT p.*, u.full_name_en FROM Proposals p JOIN Users u ON p.student_id = u.user_id WHERE proposal_id = ?");
$stmt->execute([$id]);
$p = $stmt->fetch();
?>

<!DOCTYPE html>
<html>
<head><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-slate-100 p-10">
    <div class="max-w-4xl mx-auto bg-white p-12 rounded-2xl shadow-lg border">
        <div class="flex justify-between border-b pb-6 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-slate-800"><?= $p['title_en'] ?></h1>
                <p class="text-blue-600">Student: <?= $p['full_name_en'] ?></p>
            </div>
            <a href="prof_dashboard.php" class="text-gray-400 hover:text-gray-600">← Back</a>
        </div>

        <div class="space-y-8">
            <section>
                <h3 class="font-bold text-slate-800 uppercase text-sm tracking-widest border-l-4 border-blue-600 pl-3 mb-3">1. Problem Statement</h3>
                <p class="text-slate-600 leading-relaxed"><?= nl2br($p['problem_statement']) ?></p>
            </section>

            <section>
                <h3 class="font-bold text-slate-800 uppercase text-sm tracking-widest border-l-4 border-blue-600 pl-3 mb-3">2. Project Objectives</h3>
                <p class="text-slate-600 leading-relaxed"><?= nl2br($p['project_objectives']) ?></p>
            </section>

            <section>
                <h3 class="font-bold text-slate-800 uppercase text-sm tracking-widest border-l-4 border-blue-600 pl-3 mb-3">3. Project Significance</h3>
                <p class="text-slate-600 leading-relaxed"><?= nl2br($p['project_significance']) ?></p>
            </section>

            <section class="bg-slate-50 p-6 rounded-xl italic">
                <h3 class="font-bold text-slate-800 not-italic mb-2">Literature & References</h3>
                <p class="text-sm text-slate-500"><?= nl2br($p['references_list']) ?></p>
            </section>

            <section class="bg-slate-50 p-6 rounded-xl">
                <h3 class="font-bold text-slate-800 mb-2">Supervision</h3>
                <p class="text-sm text-slate-600">
                    <strong>Supervisor:</strong> <?= htmlspecialchars($p['supervisor_name']) ?> (<?= htmlspecialchars($p['supervisor_email']) ?>)
                </p>
                <?php if (!empty($p['cosupervisor_name'])): ?>
                <p class="text-sm text-slate-600 mt-2">
                    <strong>Co-Supervisor:</strong> <?= htmlspecialchars($p['cosupervisor_name']) ?> (<?= htmlspecialchars($p['cosupervisor_email']) ?>)
                </p>
                <?php endif; ?>
            </section>
        </div>
    </div>
</body>
</html>