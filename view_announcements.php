<?php
require 'config.php';
checkAuth('student');

// Pagination and filtering
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

$search = $_GET['search'] ?? '';
$typeFilter = $_GET['type'] ?? 'all';

// Build query
$where = [];
$params = [];

if (!empty($search)) {
    $where[] = "(title_en LIKE ? OR content_en LIKE ? OR title_ar LIKE ? OR content_ar LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($typeFilter !== 'all') {
    $where[] = "type = ?";
    $params[] = $typeFilter;
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Get total count for pagination
$countQuery = "SELECT COUNT(*) FROM Announcements $whereClause";
$countStmt = $pdo->prepare($countQuery);
$countStmt->execute($params);
$totalAnnouncements = $countStmt->fetchColumn();
$totalPages = ceil($totalAnnouncements / $perPage);

// Fetch announcements with pagination
$query = "SELECT * FROM Announcements $whereClause ORDER BY created_at DESC LIMIT $perPage OFFSET $offset";
$ann_stmt = $pdo->prepare($query);
$ann_stmt->execute($params);
$announcements = $ann_stmt->fetchAll();

$lang = $_SESSION['user_lang'] ?? 'en';
$dir = ($lang == 'ar') ? 'rtl' : 'ltr';
?>
<?php include 'header.php'; ?>
<body class="bg-slate-50 min-h-screen">
    <?php include 'navbar.php'; ?>
    <main class="max-w-6xl mx-auto p-8">
        <h1 class="text-3xl font-black text-slate-800 mb-8">
            <?= $lang == 'en' ? 'All Announcements' : 'كل الاعلانات' ?>
        </h1>

        <!-- Search and Filter Section -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-6">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <input type="text" name="search" id="searchInput"
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="<?= $lang == 'en' ? 'Search announcements...' : 'البحث في الإعلانات...' ?>"
                        value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="md:w-48">
                    <select name="type" id="typeFilter"
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="all" <?= $typeFilter === 'all' ? 'selected' : '' ?>>
                            <?= $lang == 'en' ? 'All Types' : 'جميع الأنواع' ?>
                        </option>
                        <option value="post" <?= $typeFilter === 'post' ? 'selected' : '' ?>>
                            <?= $lang == 'en' ? 'Posts' : 'المشاركات' ?>
                        </option>
                        <option value="deadline" <?= $typeFilter === 'deadline' ? 'selected' : '' ?>>
                            <?= $lang == 'en' ? 'Deadlines' : 'المواعيد النهائية' ?>
                        </option>
                    </select>
                </div>
                <button onclick="applyFilters()"
                    class="bg-slate-600 text-white px-6 py-2 rounded-lg hover:bg-slate-700 transition-colors">
                    <?= $lang == 'en' ? 'Search' : 'بحث' ?>
                </button>
            </div>
        </div>

        <!-- Announcements List -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
            <?php if (empty($announcements)): ?>
                <div class="text-center text-slate-300 italic py-12">
                    <?= $lang == 'en' ? 'No announcements found.' : 'لم يتم العثور على إعلانات.' ?>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach($announcements as $a): ?>
                    <div class="border border-slate-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="px-2 py-1 rounded text-xs font-semibold <?= $a['type'] === 'deadline' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' ?>">
                                <?= $a['type'] === 'deadline' ? ($lang == 'en' ? 'Deadline' : 'موعد نهائي') : ($lang == 'en' ? 'Post' : 'مشاركة') ?>
                            </span>
                            <span class="text-sm text-slate-500">
                                <?= date('M d, Y', strtotime($a['created_at'])) ?>
                            </span>
                        </div>
                        <h3 class="font-bold text-slate-800 text-lg mb-2">
                            <?= $lang == 'en' ? htmlspecialchars($a['title_en']) : htmlspecialchars($a['title_ar']) ?>
                        </h3>
                        <p class="text-slate-600">
                            <?= $lang == 'en' ? htmlspecialchars($a['content_en']) : htmlspecialchars($a['content_ar']) ?>
                        </p>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <div class="flex justify-center items-center gap-2 mt-6">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&type=<?= $typeFilter ?>"
                            class="px-4 py-2 bg-slate-200 rounded-lg hover:bg-slate-300 transition-colors">
                            <?= $lang == 'en' ? 'Previous' : 'السابق' ?>
                        </a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="px-4 py-2 bg-blue-600 text-white rounded-lg">
                                <?= $i ?>
                            </span>
                        <?php else: ?>
                            <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&type=<?= $typeFilter ?>"
                                class="px-4 py-2 bg-slate-200 rounded-lg hover:bg-slate-300 transition-colors">
                                <?= $i ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&type=<?= $typeFilter ?>"
                            class="px-4 py-2 bg-slate-200 rounded-lg hover:bg-slate-300 transition-colors">
                            <?= $lang == 'en' ? 'Next' : 'التالي' ?>
                        </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>

    <script>
        function applyFilters() {
            const search = document.getElementById('searchInput').value;
            const type = document.getElementById('typeFilter').value;
            window.location.href = `?search=${encodeURIComponent(search)}&type=${type}`;
        }

        // Handle Enter key in search input
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                applyFilters();
            }
        });
    </script>
    <?php include 'footer.php'; ?>
</body>
</html>
