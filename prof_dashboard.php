<?php 
require 'config.php'; 
checkAuth('professor'); 

$status_filter = $_GET['status'] ?? null;
$view_all = isset($_GET['view']) && $_GET['view'] === 'all';
$search_term = $_GET['search'] ?? null;

// Show sidebar ONLY on the default home view
$show_sidebar = (!$status_filter && !$view_all && !$search_term);

// Fetch Statistics for Colored Cards
$c_pending = $pdo->query("SELECT COUNT(*) FROM Proposals WHERE status='Pending'")->fetchColumn();
$c_approved = $pdo->query("SELECT COUNT(*) FROM Proposals WHERE status='Approved'")->fetchColumn();
$c_mods = $pdo->query("SELECT COUNT(*) FROM Proposals WHERE status='Modifications Required'")->fetchColumn();
$c_rejected = $pdo->query("SELECT COUNT(*) FROM Proposals WHERE status='Rejected'")->fetchColumn();

// Proposal Query Logic
$sql = "SELECT p.*, ps.student_name, ps.student_id FROM Proposals p
        LEFT JOIN Proposal_Students ps ON ps.proposal_id = p.proposal_id
        AND ps.id = (SELECT MIN(id) FROM Proposal_Students WHERE proposal_id = p.proposal_id)";

$params = [];
$where_clauses = [];

if ($status_filter) {
    $where_clauses[] = "p.status = ?";
    $params[] = $status_filter;
}

if ($search_term) {
    $where_clauses[] = "(ps.student_name LIKE ? OR ps.student_id LIKE ? OR p.title LIKE ?)";
    $search_param = "%$search_term%";
    $params = array_merge($params, [$search_param, $search_param, $search_param]);
}

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql .= " ORDER BY p.created_at DESC";

if (!$view_all && !$search_term) {
    $sql .= " LIMIT 3";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$proposals = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $dir ?>">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>@import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;900&display=swap'); body { font-family: 'Cairo', sans-serif; }</style>
    <title>Faculty Dashboard</title>
</head>
<body class="bg-slate-100 min-h-screen">
    <?php include 'navbar.php'; ?>

    <main class="max-w-7xl mx-auto p-6">
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-10">
            <a href="?status=Pending" class="block bg-[#002080] text-white p-8 rounded-2xl shadow-lg hover:opacity-90 transition-all text-center">
                <h2 class="text-6xl font-black mb-2"><?= $c_pending ?></h2>
                <p class="font-bold text-sm uppercase tracking-widest"><?= $lang == 'en' ? 'New Requests' : 'طلبات جديدة' ?></p>
            </a>
            <a href="?status=Approved" class="block bg-[#4CAF50] text-white p-8 rounded-2xl shadow-lg hover:opacity-90 transition-all text-center">
                <h2 class="text-6xl font-black mb-2"><?= $c_approved ?></h2>
                <p class="font-bold text-sm uppercase tracking-widest"><?= $lang == 'en' ? 'Approved' : 'مقبولة' ?></p>
            </a>
            <a href="?status=Modifications Required" class="block bg-[#FF9800] text-white p-8 rounded-2xl shadow-lg hover:opacity-90 transition-all text-center">
                <h2 class="text-6xl font-black mb-2"><?= $c_mods ?></h2>
                <p class="font-bold text-sm uppercase tracking-widest"><?= $lang == 'en' ? 'Needs Modification' : 'تحتاج مراجعة' ?></p>
            </a>
            <a href="?status=Rejected" class="block bg-[#F44336] text-white p-8 rounded-2xl shadow-lg hover:opacity-90 transition-all text-center">
                <h2 class="text-6xl font-black mb-2"><?= $c_rejected ?></h2>
                <p class="font-bold text-sm uppercase tracking-widest"><?= $lang == 'en' ? 'Rejected' : 'مرفوضة' ?></p>
            </a>
        </div>

        <div class="grid grid-cols-1 <?= $show_sidebar ? 'lg:grid-cols-3' : 'lg:grid-cols-1' ?> gap-8">
            
            <div class="<?= $show_sidebar ? 'lg:col-span-2' : '' ?>">
                <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 overflow-hidden">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                        <h3 class="font-black text-slate-800 uppercase tracking-tighter">
                            <?= $status_filter ? "$status_filter List" : ($lang == 'en' ? 'Proposals' : 'المقترحات') ?>
                        </h3>
                        <?php if(!$view_all && !$status_filter): ?>
                            <a href="?view=all" class="text-blue-600 text-xs font-bold underline">View All</a>
                        <?php elseif($status_filter || $view_all): ?>
                            <a href="prof_dashboard.php" class="text-slate-400 text-xs font-bold underline">Back to Home</a>
                        <?php endif; ?>
                    </div>
                    <div class="p-6">
                        <form action="" method="get" class="flex items-center gap-4">
                            <input type="text" name="search" class="w-full bg-slate-100 border-none rounded-xl px-6 py-3 focus:ring-2 focus:ring-blue-500 outline-none shadow-sm" placeholder="Search by student name, ID, or proposal title">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white px-8 py-3 rounded-xl font-black uppercase text-xs tracking-widest transition-all">Search</button>
                        </form>
                    </div>
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50">
                                <th class="p-4 text-xs font-bold uppercase">Student Name</th>
                                <th class="p-4 text-xs font-bold uppercase">Student ID</th>
                                <th class="p-4 text-xs font-bold uppercase">Proposal Title</th>
                                <th class="p-4"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($proposals)): ?>
                                <tr>
                                    <td colspan="4" class="p-6 text-center text-slate-500">No proposals found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($proposals as $p): ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="p-6 font-bold text-slate-800"><?= htmlspecialchars($p['student_name'] ?? '-') ?></td>
                                    <td class="p-6 text-slate-500"><?= htmlspecialchars($p['student_id'] ?? '-') ?></td>
                                    <td class="p-6 text-slate-400"><?= htmlspecialchars($p['title']) ?></td>
                                    <td class="p-6 text-right">
                                        <a href="review.php?id=<?= $p['proposal_id'] ?>" class="bg-slate-900 text-white px-6 py-2 rounded-xl text-[10px] font-black hover:bg-blue-600 transition-all">REVIEW</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if($show_sidebar): ?>
            <div class="space-y-6">
                <h3 class="font-black text-slate-800 uppercase tracking-tighter px-2">
                    <?= $lang == 'en' ? 'Announcements' : 'الإعلانات' ?>
                </h3>
                <?php 
                $news = $pdo->query("SELECT * FROM Announcements ORDER BY created_at DESC LIMIT 3")->fetchAll();
                foreach($news as $n):
                ?>
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-1 h-full bg-blue-600"></div>
                    <h4 class="font-bold text-sm text-slate-800"><?= $lang == 'en' ? $n['title_en'] : $n['title_ar'] ?></h4>
                    <p class="text-xs text-slate-500 mt-2 line-clamp-2"><?= $lang == 'en' ? $n['content_en'] : $n['content_ar'] ?></p>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        </div>
    </main>
</body>
</html>