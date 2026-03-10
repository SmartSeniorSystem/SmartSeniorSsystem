<?php
require 'config.php';
checkAuth('professor'); // Security check

// 1. Get Proposal ID from URL
$proposal_id = $_GET['id'] ?? null;
if (!$proposal_id) { header("Location: prof_dashboard.php"); exit(); }

// 2. Handle Status/Feedback Update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_status = $_POST['status'];
    $feedback = $_POST['prof_feedback'] ?? '';
    $update_stmt = $pdo->prepare("UPDATE Proposals SET status = ?, prof_feedback = ? WHERE proposal_id = ?");
    $update_stmt->execute([$new_status, $feedback, $proposal_id]);
    header("Location: prof_dashboard.php?success=1");
    exit();
}

// 3. Fetch Proposal & First Student Details
// Fetch proposal
$stmt = $pdo->prepare("SELECT * FROM Proposals WHERE proposal_id = ?");
$stmt->execute([$proposal_id]);
$proposal = $stmt->fetch();
if (!$proposal) { die("Proposal not found."); }
// Fetch all students for this proposal
$students_stmt = $pdo->prepare("SELECT student_name, student_id, student_email FROM Proposal_Students WHERE proposal_id = ?");
$students_stmt->execute([$proposal_id]);
$students = $students_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $dir ?>">
<head>
    <script src="https://cdn.tailwindcss.com"></script>
    <title><?= $lang == 'en' ? 'Review Proposal' : 'مراجعة المقترح' ?></title>
</head>
<body class="bg-slate-50 min-h-screen pb-20">
    <?php include 'navbar.php'; ?>

    <main class="max-w-4xl mx-auto p-8">
        <div class="mb-8 flex justify-between items-end">
            <div>
                <span class="text-[10px] font-black uppercase text-blue-600 tracking-widest">Students</span>
                <div class="flex flex-wrap gap-2 mt-1">
                <?php foreach ($students as $stu): ?>
                    <span class="inline-block bg-blue-50 text-blue-900 px-3 py-1 rounded-xl text-xs font-bold">
                        <?= htmlspecialchars($stu['student_name']) ?> (<?= htmlspecialchars($stu['student_id']) ?>)
                    </span>
                <?php endforeach; ?>
                </div>
            </div>
            <a href="prof_dashboard.php" class="text-slate-400 font-bold text-sm hover:text-slate-600">← <?= $lang == 'en' ? 'Back' : 'العودة' ?></a>
        </div>

        <div class="bg-white rounded-[2.5rem] shadow-xl border border-slate-100 overflow-hidden mb-8">
            <div class="p-10 space-y-10">
                <section>
                    <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest block mb-2">Project Title</label>
                    <h2 class="text-xl font-bold text-slate-800"><?= htmlspecialchars($proposal['title'] ?? '-') ?></h2>
                </section>
                <section>
                    <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest block mb-2">Problem Statement</label>
                    <div class="bg-slate-50 rounded-xl p-3 max-h-32 overflow-auto flex items-start">
                        <p class="text-slate-600 leading-relaxed"><?= nl2br(htmlspecialchars($proposal['problem'] ?? '-')) ?></p>
                    </div>
                </section>
                <section>
                    <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest block mb-2">Objectives</label>
                    <div class="bg-slate-50 rounded-xl p-3 max-h-32 overflow-auto flex items-start">
                        <p class="text-slate-600 text-sm italic"><?= nl2br(htmlspecialchars($proposal['objectives'] ?? '-')) ?></p>
                    </div>
                </section>
                <section>
                    <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest block mb-2">Significance</label>
                    <div class="bg-slate-50 rounded-xl p-3 max-h-32 overflow-auto flex items-start">
                        <p class="text-slate-600 text-sm italic"><?= nl2br(htmlspecialchars($proposal['significance'] ?? '-')) ?></p>
                    </div>
                </section>
                <section>
                    <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest block mb-2">Literature Review</label>
                    <div class="bg-slate-50 rounded-xl p-3 max-h-32 overflow-auto flex items-start">
                        <p class="text-slate-600 text-sm italic"><?= nl2br(htmlspecialchars($proposal['literature_review'] ?? '')) ?></p>
                    </div>
                </section>
                <section>
                    <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest block mb-2">References</label>
                    <div class="bg-slate-50 rounded-xl p-3 max-h-32 overflow-auto flex items-start">
                        <p class="text-slate-600 text-sm"><?= nl2br(htmlspecialchars($proposal['references_list'])) ?></p>
                    </div>
                </section>
                <section>
                    <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest block mb-2">Timeline</label>
                    <div class="overflow-x-auto">
                        <?php
                        // Render timeline as a Gantt-style table if possible
                        $phases = [
                            'Requirement Collection',
                            'Literature Review',
                            'Design',
                            'Implementation',
                            'Testing',
                            'Report Writing',
                        ];
                        $timeline = json_decode($proposal['timeline'] ?? '{}', true);
                        if (is_array($timeline) && count($timeline)) {
                        ?>
                        <table class="w-full border-collapse text-[11px]">
                            <thead>
                                <tr class="bg-slate-800 text-white">
                                    <th class="p-2 border">Phase</th>
                                    <?php for($i=1; $i<=16; $i++): ?>
                                        <th class="p-1 border">W<?= $i ?></th>
                                    <?php endfor; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($phases as $phase): ?>
                                <tr>
                                    <td class="p-2 border font-bold"><?= htmlspecialchars($phase) ?></td>
                                    <?php for($i=1; $i<=16; $i++): ?>
                                        <td class="border text-center">
                                            <?php if(isset($timeline[$phase]) && in_array($i, $timeline[$phase])): ?>
                                                <span class="inline-block w-3 h-3 bg-blue-500 rounded-full"></span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endfor; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php } else { ?>
                            <pre class="bg-slate-100 p-3 rounded-xl text-xs"><?= htmlspecialchars($proposal['timeline'] ?? '') ?></pre>
                        <?php } ?>
                    </div>
                </section>
                <section>
                    <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest block mb-2">Supervisor</label>
                    <p class="text-slate-600 text-sm"><?= htmlspecialchars($proposal['supervisor_name'] ?? '') ?> (<?= htmlspecialchars($proposal['supervisor_email'] ?? '') ?>)</p>
                    <?php if (!empty($proposal['cosupervisor_name'])): ?>
                    <p class="text-slate-600 text-sm">Co-supervisor: <?= htmlspecialchars($proposal['cosupervisor_name']) ?> (<?= htmlspecialchars($proposal['cosupervisor_email'] ?? '') ?>)</p>
                    <?php endif; ?>
                </section>
            </div>
            <form method="POST" class="bg-slate-900 p-8 flex flex-col gap-6">
                <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="text-white">
                        <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Current Status</p>
                        <span class="font-bold text-blue-400"><?= $proposal['status'] ?></span>
                    </div>
                    <div class="flex items-center gap-4 w-full md:w-auto">
                        <select name="status" class="bg-slate-800 text-white border-none rounded-xl px-6 py-4 outline-none focus:ring-2 focus:ring-blue-500 flex-1 md:flex-none">
                            <option value="Pending" <?= $proposal['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="Approved" <?= $proposal['status'] == 'Approved' ? 'selected' : '' ?>>Approve</option>
                            <option value="Rejected" <?= $proposal['status'] == 'Rejected' ? 'selected' : '' ?>>Reject</option>
                            <option value="Modifications Required" <?= $proposal['status'] == 'Modifications Required' ? 'selected' : '' ?>>Needs Mod</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2">Feedback / Comments to Student</label>
                    <textarea name="prof_feedback" class="w-full bg-slate-50 border-none p-5 rounded-2xl h-32 focus:ring-2 focus:ring-blue-500 outline-none shadow-sm" placeholder="Write feedback for the student..."><?= htmlspecialchars($proposal['prof_feedback'] ?? '') ?></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white px-8 py-4 rounded-xl font-black uppercase text-xs tracking-widest transition-all">
                        <?= $lang == 'en' ? 'Update Decision' : 'تحديث القرار' ?>
                    </button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>