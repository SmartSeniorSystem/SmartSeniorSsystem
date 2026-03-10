<nav class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white sticky top-0 z-50 backdrop-blur-lg border-b border-slate-700/50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">
            <!-- Logo and Brand -->
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-2xl shadow-blue-500/30">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                    </svg>
                </div>
                <div class="flex flex-col">
                    <span class="text-xl font-bold text-white leading-tight tracking-tight">
                        <?= $lang == 'en' ? 'University System' : 'نظام الجامعة' ?>
                    </span>
                    <span class="text-xs text-blue-300 font-medium tracking-wide uppercase">
                        <?= $lang == 'en' ? 'Project Management' : 'إدارة المشاريع' ?>
                    </span>
                </div>
            </div>

            <!-- Navigation Links -->
            <div class="hidden md:flex items-center gap-2">
                <?php if ($_SESSION['role'] === 'professor'): ?>
                    <a href="prof_dashboard.php" class="nav-link">
                        <?= $lang == 'en' ? 'Dashboard' : 'الرئيسية' ?>
                    </a>
                    <a href="manage_announcements.php" class="nav-link">
                        <?= $lang == 'en' ? 'Announcements' : 'الإعلانات' ?>
                    </a>
                    <a href="prof_dashboard.php?view=all" class="nav-link">
                        <?= $lang == 'en' ? 'Proposals' : 'المقترحات' ?>
                    </a>
                <?php elseif ($_SESSION['role'] === 'student'): ?>
                    <a href="student_dashboard.php" class="nav-link">
                        <?= $lang == 'en' ? 'Home' : 'الرئيسية' ?>
                    </a>
                    <a href="proposal_form.php" class="nav-link">
                        <?= $lang == 'en' ? 'Submit Proposal' : 'تقديم مقترح' ?>
                    </a>
                    <a href="view_announcements.php" class="nav-link">
                        <?= $lang == 'en' ? 'Announcements' : 'الإعلانات' ?>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Right Side Actions -->
            <div class="flex items-center gap-3">
                <!-- Language Switcher -->
                <a href="?lang=<?= $lang == 'en' ? 'ar' : 'en' ?>" class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-700/50 hover:bg-slate-600/50 border border-slate-600/50 transition-all duration-300">
                    <svg class="w-5 h-5 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                    </svg>
                    <span class="text-sm font-semibold text-white"><?= $lang == 'en' ? 'AR' : 'EN' ?></span>
                </a>

                <!-- User Info -->
                <?php if (isset($_SESSION['name'])): ?>
                    <div class="hidden sm:flex items-center gap-3 px-4 py-2.5 bg-slate-700/30 rounded-xl border border-slate-600/30">
                        <div class="w-9 h-9 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white font-bold text-sm shadow-lg shadow-blue-500/30">
                            <?= substr(htmlspecialchars($_SESSION['name']), 0, 1) ?>
                        </div>
                        <span class="text-sm font-semibold text-white"><?= htmlspecialchars($_SESSION['name']) ?></span>
                    </div>
                <?php endif; ?>

                <!-- Logout Button -->
                <a href="logout.php" class="flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-xl font-semibold text-sm transition-all duration-300 shadow-lg shadow-red-500/30 hover:shadow-red-500/50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    <span><?= $lang == 'en' ? 'Logout' : 'خروج' ?></span>
                </a>
            </div>
        </div>
    </div>
</nav>

<style>
.nav-link {
    display: inline-flex;
    align-items: center;
    padding: 0.625rem 1.25rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: #cbd5e1;
    border-radius: 0.75rem;
    transition: all 0.3s ease;
    letter-spacing: 0.025em;
}

.nav-link:hover {
    background: rgba(59, 130, 246, 0.15);
    color: white;
    transform: translateY(-1px);
}

.nav-link.active {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
}
</style>