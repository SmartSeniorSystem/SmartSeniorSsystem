<?php
require 'config.php';
checkAuth('student');

$id = $_GET['id'] ?? 0;

// 1. جلب جميع مقترحات الطالب للتنقل بينها
$all_proposals_stmt = $pdo->prepare("
    SELECT p.proposal_id, p.title 
    FROM Proposals p
    JOIN Proposal_Students ps ON p.proposal_id = ps.proposal_id
    WHERE ps.student_email = ?
    ORDER BY p.proposal_id ASC
");
$all_proposals_stmt->execute([$_SESSION['email']]);
$all_proposals = $all_proposals_stmt->fetchAll();

// تحديد موقع المقترح الحالي في القائمة
$current_index = -1;
foreach ($all_proposals as $index => $prop) {
    if ($prop['proposal_id'] == $id) {
        $current_index = $index;
        break;
    }
}

if ($current_index === -1) {
    die("<div style='padding:50px; text-align:center; font-family:sans-serif;'><h2>Proposal Not Found</h2><a href='student_dashboard.php'>Return to Dashboard</a></div>");
}

// 2. جلب بيانات المشروع الأساسية
$stmt = $pdo->prepare("
    SELECT p.* FROM Proposals p
    JOIN Proposal_Students ps ON p.proposal_id = ps.proposal_id
    WHERE p.proposal_id = ? AND ps.student_email = ?
");
$stmt->execute([$id, $_SESSION['email']]);
$proposal = $stmt->fetch();

// 3. جلب قائمة الطلاب المشاركين
$st_stmt = $pdo->prepare("SELECT * FROM Proposal_Students WHERE proposal_id = ?");
$st_stmt->execute([$id]);
$all_students = $st_stmt->fetchAll();

// 4. معالجة بيانات التايم لاين
$timeline_data = json_decode($proposal['timeline'] ?? '[]', true);
$phases = [
    'Requirement Collection',
    'Literature Review',
    'Design',
    'Implementation',
    'Testing',
    'Report Writing'
];
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $dir ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <title><?= $lang == 'en' ? 'Proposal Details' : 'تفاصيل المقترح' ?></title>
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-50 min-h-screen pb-12">

    <?php include 'navbar.php'; ?>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="mb-10">
            <a href="student_dashboard.php" class="text-slate-400 hover:text-blue-600 font-medium text-sm mb-4 inline-block">
                ← <?= $lang == 'en' ? 'Back to Dashboard' : 'العودة للوحة التحكم' ?>
            </a>
            <div class="flex items-center gap-4 mb-4">
                <h1 class="text-4xl font-black text-slate-800 tracking-tighter uppercase">
                    <?= htmlspecialchars($proposal['title']) ?>
                </h1>
                <span class="px-4 py-1 rounded-full text-xs font-black uppercase tracking-wider
                    <?= $proposal['status'] == 'Approved' ? 'bg-green-100 text-green-700' : 
                       ($proposal['status'] == 'Rejected' ? 'bg-red-100 text-red-700' : 
                       ($proposal['status'] == 'Under Review' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700')) ?>">
                    <?= $proposal['status'] ?>
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-8">
                <!-- Project Team -->
                <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100">
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-6">
                        <?= $lang == 'en' ? 'Project Team' : 'فريق المشروع' ?>
                    </h3>
                    <div class="space-y-4">
                        <?php foreach($all_students as $s): ?>
                            <div class="border border-slate-100 bg-slate-50 rounded-2xl p-6">
                                <div class="font-bold text-slate-800 text-lg mb-2"><?= htmlspecialchars($s['student_name']) ?></div>
                                <div class="text-sm text-slate-500">
                                    ID: <?= htmlspecialchars($s['student_id']) ?> | <?= htmlspecialchars($s['student_email']) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Technical Description -->
                <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100">
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-6">
                        <?= $lang == 'en' ? 'Technical Description' : 'الوصف التقني' ?>
                    </h3>

                    <div class="mb-6">
                        <h4 class="font-bold text-slate-800 text-lg mb-3"><?= $lang == 'en' ? 'Problem Statement' : 'بيان المشكلة' ?></h4>
                        <p class="text-slate-600 leading-relaxed"><?= nl2br(htmlspecialchars($proposal['problem'])) ?></p>
                    </div>

                    <div class="mb-6">
                        <h4 class="font-bold text-slate-800 text-lg mb-3"><?= $lang == 'en' ? 'Objectives' : 'الأهداف' ?></h4>
                        <p class="text-slate-600 leading-relaxed"><?= nl2br(htmlspecialchars($proposal['objectives'])) ?></p>
                    </div>

                        <div class="mb-6">
                        <h4 class="font-bold text-slate-800 text-lg mb-3"><?= $lang == 'en' ? 'Project Significance' : 'أهمية المشروع' ?></h4>
                        <p class="text-slate-600 leading-relaxed"><?= nl2br(htmlspecialchars($proposal['significance'])) ?></p>
                    </div>


                    <div>
                        <h4 class="font-bold text-slate-800 text-lg mb-3"><?= $lang == 'en' ? 'Literature Review' : 'مراجعة الأدبيات' ?></h4>
                        <p class="text-slate-600 leading-relaxed"><?= nl2br(htmlspecialchars($proposal['literature_review'])) ?></p>
                    </div>

                    
                    <div>
                        <h4 class="font-bold text-slate-800 text-lg mb-3"><?= $lang == 'en' ? 'References' : 'المراجع' ?></h4>
                        <p class="text-slate-600 leading-relaxed"><?= nl2br(htmlspecialchars($proposal['references_list'])) ?></p>
                    </div>
                </div>

                <!-- Project Timeline -->
                <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100">
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-6">
                        <?= $lang == 'en' ? 'Project Timeline' : 'الجدول الزمني للمشروع' ?>
                    </h3>
                    <div class="gantt-wrapper overflow-x-auto">
                        <div class="gantt-chart min-w-[850px] grid" style="grid-template-columns: 200px repeat(16, 1fr);">
                            <div class="gantt-header font-bold text-[10px] text-slate-400 text-center py-3 border-b border-slate-100" style="text-align:left; padding-left:12px">Phase</div>
                            <?php for($i=1; $i<=16; $i++) echo "<div class='gantt-header font-bold text-[10px] text-slate-400 text-center py-3 border-b border-slate-100'>W$i</div>"; ?>

                            <?php foreach($phases as $phase): ?>
                                <div class="gantt-row contents">
                                    <div class="gantt-label py-3 px-4 text-[12px] border-b border-slate-50 bg-white font-medium border-r border-slate-100"><?= $phase ?></div>
                                    <?php for($i=1; $i<=16; $i++): 
                                        $isActive = (isset($timeline_data[$phase]) && in_array($i, $timeline_data[$phase]));
                                    ?>
                                        <div class="gantt-cell border-b border-slate-50 border-r border-slate-50 relative h-11">
                                            <?php if($isActive): ?><div class="bar absolute top-[20%] left-0 w-full h-[60%] bg-blue-500 rounded-[4px] shadow-sm"></div><?php endif; ?>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <?php if($proposal['prof_feedback']): ?>
                <!-- Feedback -->
                <div class="bg-blue-900 p-8 rounded-[2rem] text-white shadow-xl relative overflow-hidden">
                    <div class="relative z-10">
                        <h4 class="text-blue-300 text-xs font-black uppercase tracking-widest mb-2"><?= $lang == 'en' ? 'Dr. Feedback' : 'ملاحظات الدكتور' ?></h4>
                        <p class="italic text-lg">"<?= nl2br(htmlspecialchars($proposal['prof_feedback'])) ?>"</p>
                    </div>
                    <div class="absolute -right-4 -bottom-4 text-9xl text-white/5 font-black">"</div>
                </div>
                <?php endif; ?>
            </div>

            <div class="space-y-6">
                <!-- Supervision -->
                <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100">
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-6">
                        <?= $lang == 'en' ? 'Supervision' : 'الإشراف' ?>
                    </h3>
                    <div class="space-y-4">
                        <div class="border-l-4 border-amber-500 pl-4">
                            <div class="text-[10px] font-black text-amber-500 uppercase mb-1"><?= $lang == 'en' ? 'Primary Supervisor' : 'المشرف الرئيسي' ?></div>
                            <div class="font-bold text-slate-800"><?= htmlspecialchars($proposal['supervisor_name']) ?></div>
                            <div class="text-sm text-slate-500"><?= htmlspecialchars($proposal['supervisor_email']) ?></div>
                        </div>
                        <?php if($proposal['cosupervisor_name']): ?>
                        <div class="pl-4">
                            <div class="text-[10px] font-black text-slate-400 uppercase mb-1"><?= $lang == 'en' ? 'Co-Supervisor' : 'المشرف المساعد' ?></div>
                            <div class="font-bold text-slate-800"><?= htmlspecialchars($proposal['cosupervisor_name']) ?></div>
                            <div class="text-sm text-slate-500"><?= htmlspecialchars($proposal['cosupervisor_email']) ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

              
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>