<?php
require 'config.php';
checkAuth('professor'); // Only professors can manage announcements

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $title = $_POST['title'] ?? '';
        $date = $_POST['date'] ?? date('Y-m-d');
        $description = $_POST['description'] ?? '';
        $type = $_POST['type'] ?? 'post';

        if (!empty($title) && !empty($description)) {
            $stmt = $pdo->prepare("INSERT INTO Announcements (title_en, title_ar, content_en, content_ar, created_at, type) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $title, $description, $description, $date, $type]);
            $success = "Announcement added successfully!";
        } else {
            $error = "Please fill in all required fields.";
        }
    } elseif ($action === 'edit') {
        $id = $_POST['id'] ?? '';
        $title = $_POST['title'] ?? '';
        $date = $_POST['date'] ?? date('Y-m-d');
        $description = $_POST['description'] ?? '';
        $type = $_POST['type'] ?? 'post';

        if (!empty($id) && !empty($title) && !empty($description)) {
            $stmt = $pdo->prepare("UPDATE Announcements SET title_en = ?, title_ar = ?, content_en = ?, content_ar = ?, created_at = ?, type = ? WHERE id = ?");
            $stmt->execute([$title, $title, $description, $description, $date, $type, $id]);
            $success = "Announcement updated successfully!";
        } else {
            $error = "Please fill in all required fields.";
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        if (!empty($id)) {
            $stmt = $pdo->prepare("DELETE FROM Announcements WHERE id = ?");
            $stmt->execute([$id]);
            $success = "Announcement deleted successfully!";
        }
    }
}

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
    $where[] = "(title_en LIKE ? OR content_en LIKE ?)";
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
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-black text-slate-800">
                <?= $lang == 'en' ? 'Manage Announcements' : 'إدارة الإعلانات' ?>
            </h1>
            <button onclick="openAddModal()"
                class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                <?= $lang == 'en' ? '+ Add New Announcement' : '+ إضافة إعلان جديد' ?>
            </button>
        </div>

        <?php if (isset($success)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <?= $success ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <?= $error ?>
            </div>
        <?php endif; ?>

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

        <!-- Existing Announcements List -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
            <?php if (empty($announcements)): ?>
                <div class="text-center text-slate-300 italic py-12">
                    <?= $lang == 'en' ? 'No announcements found.' : 'لم يتم العثور على إعلانات.' ?>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach($announcements as $a): ?>
                    <div class="border border-slate-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="px-2 py-1 rounded text-xs font-semibold <?= $a['type'] === 'deadline' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' ?>">
                                        <?= $a['type'] === 'deadline' ? ($lang == 'en' ? 'Deadline' : 'موعد نهائي') : ($lang == 'en' ? 'Post' : 'مشاركة') ?>
                                    </span>
                                    <span class="text-sm text-slate-500">
                                        <?= date('M d, Y', strtotime($a['created_at'])) ?>
                                    </span>
                                </div>
                                <h3 class="font-bold text-slate-800 text-lg mb-2">
                                    <?= htmlspecialchars($a['title_en']) ?>
                                </h3>
                                <p class="text-slate-600">
                                    <?= htmlspecialchars($a['content_en']) ?>
                                </p>
                            </div>
                            <div class="flex gap-2">
                                <button onclick="openEditModal(<?= htmlspecialchars(json_encode($a)) ?>)"
                                    class="bg-yellow-500 text-white px-4 py-2 rounded-lg hover:bg-yellow-600 transition-colors">
                                    <?= $lang == 'en' ? 'Edit' : 'تعديل' ?>
                                </button>
                                <form method="POST" action="" onsubmit="return confirm('<?= $lang == 'en' ? 'Are you sure you want to delete this announcement?' : 'هل أنت متأكد أنك تريد حذف هذا الإعلان؟' ?>')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                    <button type="submit"
                                        class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-colors">
                                        <?= $lang == 'en' ? 'Delete' : 'حذف' ?>
                                    </button>
                                </form>
                            </div>
                        </div>
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

    <!-- Add Modal -->
    <div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-2xl shadow-xl p-6 max-w-lg w-full mx-4">
            <h2 class="text-xl font-bold text-slate-800 mb-6">
                <?= $lang == 'en' ? 'Add New Announcement' : 'إضافة إعلان جديد' ?>
            </h2>
            <form method="POST" action="" class="space-y-4">
                <input type="hidden" name="action" value="add">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        <?= $lang == 'en' ? 'Title' : 'العنوان' ?>
                    </label>
                    <input type="text" name="title" required
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="<?= $lang == 'en' ? 'Enter announcement title' : 'أدخل عنوان الإعلان' ?>">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        <?= $lang == 'en' ? 'Date' : 'التاريخ' ?>
                    </label>
                    <input type="date" name="date" required
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        value="<?= date('Y-m-d') ?>">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        <?= $lang == 'en' ? 'Type' : 'النوع' ?>
                    </label>
                    <select name="type" required
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="post">
                            <?= $lang == 'en' ? 'Post' : 'مشاركة' ?>
                        </option>
                        <option value="deadline">
                            <?= $lang == 'en' ? 'Deadline' : 'موعد نهائي' ?>
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        <?= $lang == 'en' ? 'Description' : 'الوصف' ?>
                    </label>
                    <textarea name="description" rows="4" required
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="<?= $lang == 'en' ? 'Enter announcement description' : 'أدخل وصف الإعلان' ?>"></textarea>
                </div>
                <div class="flex gap-4">
                    <button type="submit"
                        class="flex-1 bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        <?= $lang == 'en' ? 'Add Announcement' : 'إضافة الإعلان' ?>
                    </button>
                    <button type="button" onclick="closeAddModal()"
                        class="flex-1 bg-slate-300 text-slate-700 px-6 py-2 rounded-lg hover:bg-slate-400 transition-colors">
                        <?= $lang == 'en' ? 'Cancel' : 'إلغاء' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-2xl shadow-xl p-6 max-w-lg w-full mx-4">
            <h2 class="text-xl font-bold text-slate-800 mb-6">
                <?= $lang == 'en' ? 'Edit Announcement' : 'تعديل الإعلان' ?>
            </h2>
            <form method="POST" action="" class="space-y-4">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="editId">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        <?= $lang == 'en' ? 'Title' : 'العنوان' ?>
                    </label>
                    <input type="text" name="title" id="editTitle" required
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        <?= $lang == 'en' ? 'Date' : 'التاريخ' ?>
                    </label>
                    <input type="date" name="date" id="editDate" required
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        <?= $lang == 'en' ? 'Type' : 'النوع' ?>
                    </label>
                    <select name="type" id="editType" required
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="post">
                            <?= $lang == 'en' ? 'Post' : 'مشاركة' ?>
                        </option>
                        <option value="deadline">
                            <?= $lang == 'en' ? 'Deadline' : 'موعد نهائي' ?>
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        <?= $lang == 'en' ? 'Description' : 'الوصف' ?>
                    </label>
                    <textarea name="description" id="editDescription" rows="4" required
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>
                <div class="flex gap-4">
                    <button type="submit"
                        class="flex-1 bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        <?= $lang == 'en' ? 'Update' : 'تحديث' ?>
                    </button>
                    <button type="button" onclick="closeEditModal()"
                        class="flex-1 bg-slate-300 text-slate-700 px-6 py-2 rounded-lg hover:bg-slate-400 transition-colors">
                        <?= $lang == 'en' ? 'Cancel' : 'إلغاء' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('addModal').classList.remove('hidden');
            document.getElementById('addModal').classList.add('flex');
        }

        function closeAddModal() {
            document.getElementById('addModal').classList.add('hidden');
            document.getElementById('addModal').classList.remove('flex');
        }

        function openEditModal(announcement) {
            document.getElementById('editId').value = announcement.id;
            document.getElementById('editTitle').value = announcement.title_en;
            document.getElementById('editDate').value = announcement.created_at.split(' ')[0];
            document.getElementById('editDescription').value = announcement.content_en;
            document.getElementById('editType').value = announcement.type || 'post';
            document.getElementById('editModal').classList.remove('hidden');
            document.getElementById('editModal').classList.add('flex');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
            document.getElementById('editModal').classList.remove('flex');
        }

        function applyFilters() {
            const search = document.getElementById('searchInput').value;
            const type = document.getElementById('typeFilter').value;
            window.location.href = `?search=${encodeURIComponent(search)}&type=${type}`;
        }

        // Close modals when clicking outside
        document.getElementById('addModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeAddModal();
            }
        });

        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });

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