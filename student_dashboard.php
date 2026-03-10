<?php 
require 'config.php'; 
checkAuth('student'); 

// 1. جلب بيانات الطالب والمقترحات
$student_email = $_SESSION['email'] ?? '';

// إذا لم يكن البريد في الجلسة، نجلبة من قاعدة البيانات باستخدام user_id
if (empty($student_email)) {
    $u_stmt = $pdo->prepare("SELECT email FROM Users WHERE user_id = ?");
    $u_stmt->execute([$_SESSION['user_id']]);
    $student_email = $u_stmt->fetchColumn();
}

// جلب المقترحات التي يشارك فيها هذا الطالب
$proposals_stmt = $pdo->prepare("
    SELECT p.*, GROUP_CONCAT(ps.student_name SEPARATOR ', ') as student_names 
    FROM Proposals p
    JOIN Proposal_Students ps ON ps.proposal_id = p.proposal_id
    WHERE ps.student_email = ? 
    GROUP BY p.proposal_id 
    ORDER BY p.created_at DESC
");
$proposals_stmt->execute([$student_email]);
$proposals = $proposals_stmt->fetchAll();

// تحديد أحدث مقترح لعرض شريط التقدم له
$latest = $proposals[0] ?? null;
$status = $latest['status'] ?? 'No Submission';
$feedback = $latest['prof_feedback'] ?? '';

// 2. منطق شريط التقدم
$steps = [
    'Pending' => 1, 
    'Under Review' => 2, 
    'Modifications Required' => 3, 
    'Approved' => 4
];
$current_step = $steps[$status] ?? 0;
// إذا كان مرفوضاً، نعتبره في المرحلة الأولى بلون مختلف
if ($status == 'Rejected') $current_step = 1; 

$progress_w = ($current_step > 0) ? (($current_step) / 4) * 100 : 0;

// 3. جلب الإعلانات
$ann_stmt = $pdo->query("SELECT * FROM Announcements ORDER BY created_at DESC LIMIT 3");
$announcements = $ann_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $dir ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <title><?= $lang == 'en' ? 'Student Dashboard' : 'لوحة تحكم الطالب' ?></title>
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-50 min-h-screen pb-12">
    
    <?php include 'navbar.php'; ?>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <h1 class="text-4xl font-black text-slate-800 tracking-tighter uppercase">
                    <?= $lang == 'en' ? 'My Project Hub' : 'مركز مشاريعي' ?>
                </h1>
                <p class="text-slate-500 font-medium">
                    <?= $lang == 'en' ? 'Welcome back, ' . $_SESSION['name'] : 'مرحباً بك، ' . $_SESSION['name'] ?>
                </p>
            </div>
            <a href="proposal_form.php" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-2xl font-bold transition-all shadow-lg shadow-blue-100 flex items-center gap-2">
                <span>+</span>
                <?= $lang == 'en' ? 'New Proposal' : 'مقترح جديد' ?>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-8">
                
                <?php if ($latest): ?>
                <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="font-bold text-slate-800"><?= $lang == 'en' ? 'Current Status' : 'الحالة الحالية' ?></h3>
                        <span class="px-4 py-1 rounded-full text-xs font-black uppercase tracking-wider 
                            <?= $status == 'Approved' ? 'bg-green-100 text-green-700' : ($status == 'Rejected' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700') ?>">
                            <?= $status ?>
                        </span>
                    </div>
                    
                    <div class="relative h-4 bg-slate-100 rounded-full overflow-hidden">
                        <div class="absolute top-0 left-0 h-full transition-all duration-1000 
                            <?= $status == 'Rejected' ? 'bg-red-500' : 'bg-blue-600' ?>" 
                            style="width: <?= $progress_w ?>%">
                        </div>
                    </div>
                    <div class="grid grid-cols-4 mt-4 text-[10px] font-bold text-slate-400 uppercase tracking-tighter">
                        <div class="text-left">Pending</div>
                        <div class="text-center">Review</div>
                        <div class="text-center">Modif.</div>
                        <div class="text-right">Final</div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100">
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-8">
                        <?= $lang == 'en' ? 'Your Submissions' : 'طلباتك المقدمة' ?>
                    </h3>

                    <?php if (empty($proposals)): ?>
                        <div class="text-center py-12">
                            <div class="text-5xl mb-4">📄</div>
                            <p class="text-slate-400 font-medium"><?= $lang == 'en' ? 'No proposals found.' : 'لا توجد مقترحات.' ?></p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach($proposals as $p): ?>
                            <div class="group border border-slate-100 bg-slate-50 hover:bg-white hover:shadow-md rounded-2xl p-6 transition-all">
                                <div class="flex flex-col md:flex-row justify-between gap-4">
                                    <div>
                                        <h4 class="font-bold text-slate-800 text-lg group-hover:text-blue-600 transition-colors">
                                            <?= htmlspecialchars($p['title']) ?>
                                        </h4>
                                        <p class="text-sm text-slate-500 mt-1">
                                            <?= $lang == 'en' ? 'Team:' : 'الفريق:' ?> <?= htmlspecialchars($p['student_names']) ?>
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <a href="proposal_details.php?id=<?= $p['proposal_id'] ?>" class="text-xs font-bold text-slate-400 hover:text-blue-600 uppercase tracking-widest">
                                            <?= $lang == 'en' ? 'Details' : 'التفاصيل' ?>
                                        </a>
                                        <?php if ($p['status'] == 'Modifications Required' || $p['status'] == 'Rejected'): ?>
                                        <a href="proposal_form.php?edit=<?= $p['proposal_id'] ?>" class="bg-amber-100 text-amber-700 px-4 py-2 rounded-xl text-xs font-bold hover:bg-amber-200">
                                            <?= $lang == 'en' ? 'Edit' : 'تعديل' ?>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($feedback): ?>
                <div class="bg-blue-900 p-8 rounded-[2rem] text-white shadow-xl relative overflow-hidden">
                    <div class="relative z-10">
                        <h4 class="text-blue-300 text-xs font-black uppercase tracking-widest mb-2">Latest Feedback</h4>
                        <p class="italic text-lg">"<?= nl2br(htmlspecialchars($feedback)) ?>"</p>
                    </div>
                    <div class="absolute -right-4 -bottom-4 text-9xl text-white/5 font-black">”</div>
                </div>
                <?php endif; ?>
            </div>

            <div class="space-y-6">
                <h2 class="font-black text-slate-400 text-xs uppercase tracking-widest pl-2">
                    <?= $lang == 'en' ? 'Announcements' : 'الإعلانات' ?>
                </h2>
                
                <?php foreach($announcements as $a): ?>
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 hover:border-blue-200 transition-colors">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="px-2 py-1 rounded text-xs font-semibold <?= $a['type'] === 'deadline' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' ?>">
                            <?= $a['type'] === 'deadline' ? ($lang == 'en' ? 'Deadline' : 'موعد نهائي') : ($lang == 'en' ? 'Post' : 'مشاركة') ?>
                        </span>
                        <span class="text-xs font-bold text-slate-400">
                            <?= date('M d, Y', strtotime($a['created_at'])) ?>
                        </span>
                    </div>
                    <h4 class="font-bold text-slate-800"><?= $lang == 'en' ? $a['title_en'] : $a['title_ar'] ?></h4>
                    <p class="text-sm text-slate-500 mt-2 leading-relaxed">
                        <?= $lang == 'en' ? $a['content_en'] : $a['content_ar'] ?>
                    </p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>
    <?php include 'chatbot_button.php'; ?>
</body>
</html>