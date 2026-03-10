<?php 
require 'config.php';
checkAuth('student');

$errors = [];
$edit_id = $_GET['edit'] ?? null;
$existing_proposal = null;
$existing_students = [];

// 1. جلب البيانات إذا كنا في وضع التعديل
if ($edit_id) {
    $stmt = $pdo->prepare("SELECT * FROM Proposals WHERE proposal_id = ?");
    $stmt->execute([$edit_id]);
    $existing_proposal = $stmt->fetch();

    if ($existing_proposal) {
        $st_stmt = $pdo->prepare("SELECT * FROM Proposal_Students WHERE proposal_id = ?");
        $st_stmt->execute([$edit_id]);
        $existing_students = $st_stmt->fetchAll();
    }
}

// 2. معالجة إرسال النموذج (Submit / Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // التحقق من الحقول (Validation)
    if (empty($_POST['students']) || !is_array($_POST['students'])) $errors[] = 'At least one student is required.';
    if (empty($_POST['title']) || empty($_POST['problem'])) $errors[] = 'Project details are required.';
    if (empty($_POST['accept_terms'])) $errors[] = 'You must accept the terms.';

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            $timeline = json_encode($_POST['timeline'] ?? []);

            if ($edit_id) {
                // وضع التعديل: UPDATE
                $stmt = $pdo->prepare("UPDATE Proposals SET 
                    title=?, problem=?, objectives=?, significance=?, literature_review=?, references_list=?, 
                    supervisor_name=?, supervisor_email=?, cosupervisor_name=?, cosupervisor_email=?, 
                    timeline=?, status='Pending' WHERE proposal_id=?");
                $stmt->execute([
                    $_POST['title'], $_POST['problem'], $_POST['objectives'], $_POST['significance'],
                    $_POST['literature_review'], $_POST['references'],
                    $_POST['supervisor']['name'], $_POST['supervisor']['email'],
                    $_POST['cosupervisor']['name'], $_POST['cosupervisor']['email'],
                    $timeline, $edit_id
                ]);

                // تحديث الطلاب (نحذف القدامى ونضيف الجدد لضمان الدقة)
                $pdo->prepare("DELETE FROM Proposal_Students WHERE proposal_id = ?")->execute([$edit_id]);
                $proposal_id = $edit_id;
            } else {
                // وضع جديد: INSERT
                $stmt = $pdo->prepare("INSERT INTO Proposals (title, problem, objectives, significance, literature_review, references_list, supervisor_name, supervisor_email, cosupervisor_name, cosupervisor_email, timeline, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())");
                $stmt->execute([
                    $_POST['title'], $_POST['problem'], $_POST['objectives'], $_POST['significance'],
                    $_POST['literature_review'], $_POST['references'],
                    $_POST['supervisor']['name'], $_POST['supervisor']['email'],
                    $_POST['cosupervisor']['name'], $_POST['cosupervisor']['email'],
                    $timeline
                ]);
                $proposal_id = $pdo->lastInsertId();
            }

            // إضافة الطلاب
            $student_stmt = $pdo->prepare("INSERT INTO Proposal_Students (proposal_id, student_name, student_id, student_email) VALUES (?, ?, ?, ?)");
            foreach ($_POST['students'] as $student) {
                if(!empty($student['name'])) {
                    $student_stmt->execute([$proposal_id, $student['name'], $student['id'], $student['email']]);
                }
            }

            $pdo->commit();
            header("Location: student_dashboard.php?success=1");
            exit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}

include 'header.php';
include 'navbar.php';
?>

<div class="max-w-4xl mx-auto p-6">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="bg-slate-50 border-b p-4 flex justify-between px-10">
            <?php for($i=1; $i<=5; $i++): ?>
                <div class="step-idx <?= $i==1?'active-step':'' ?>" id="idx-<?= $i ?>"><?= $i ?></div>
            <?php endfor; ?>
        </div>

        <form action="proposal_form.php<?= $edit_id ? '?edit='.$edit_id : '' ?>" method="POST" class="p-8 space-y-6" id="multiStepForm">
            
            <?php if (!empty($errors)): ?>
                <div class="bg-red-100 text-red-700 p-4 rounded-xl mb-4">
                    <?php foreach ($errors as $err) echo "<div>".htmlspecialchars($err)."</div>"; ?>
                </div>
            <?php endif; ?>

            <div class="form-step" id="step-1">
                <h2 class="text-xl font-bold mb-6 text-slate-800">1. Student(s) & Supervisor Info</h2>
                <div id="students-list" class="space-y-4">
                    <?php 
                    $students_to_show = $edit_id ? $existing_students : [['student_name'=>'','student_id'=>'','student_email'=>'']];
                    foreach($students_to_show as $idx => $st): 
                    ?>
                    <div class="student-row flex gap-4 flex-wrap">
                        <input type="text" name="students[<?= $idx ?>][name]" placeholder="Student Name" value="<?= htmlspecialchars($st['student_name']??$st['name']??'') ?>" class="border p-3 rounded-xl flex-1" required>
                        <input type="text" name="students[<?= $idx ?>][id]" placeholder="Student ID" value="<?= htmlspecialchars($st['student_id']??$st['id']??'') ?>" class="border p-3 rounded-xl w-32" required>
                        <input type="email" name="students[<?= $idx ?>][email]" placeholder="Student Email" value="<?= htmlspecialchars($st['student_email']??$st['email']??'') ?>" class="border p-3 rounded-xl flex-1" required>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" onclick="addStudent()" class="mt-4 px-4 py-2 bg-blue-100 text-blue-700 rounded-xl font-bold">+ Add Student</button>
                
                <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-4 border-t pt-6">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Supervisor Name</label>
                        <input type="text" name="supervisor[name]" value="<?= htmlspecialchars($existing_proposal['supervisor_name']??'') ?>" class="w-full border p-3 rounded-xl" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Supervisor Email</label>
                        <input type="email" name="supervisor[email]" value="<?= htmlspecialchars($existing_proposal['supervisor_email']??'') ?>" class="w-full border p-3 rounded-xl" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Co-Supervisor Name (Optional)</label>
                        <input type="text" name="cosupervisor[name]" value="<?= htmlspecialchars($existing_proposal['cosupervisor_name']??'') ?>" class="w-full border p-3 rounded-xl">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Co-Supervisor Email (Optional)</label>
                        <input type="email" name="cosupervisor[email]" value="<?= htmlspecialchars($existing_proposal['cosupervisor_email']??'') ?>" class="w-full border p-3 rounded-xl">
                    </div>
                </div>
            </div>

            <div class="form-step hidden" id="step-2">
                <h2 class="text-xl font-bold mb-6 text-slate-800">2. Project Details</h2>
                <div class="space-y-4">
                    <input type="text" name="title" value="<?= htmlspecialchars($existing_proposal['title']??'') ?>" placeholder="Title of Project" class="w-full border p-3 rounded-xl" required>
                    <textarea name="problem" placeholder="Problem Statement" class="w-full border p-3 rounded-xl h-32" required><?= htmlspecialchars($existing_proposal['problem']??'') ?></textarea>
                    <textarea name="objectives" placeholder="Project Objectives" class="w-full border p-3 rounded-xl h-32" required><?= htmlspecialchars($existing_proposal['objectives']??'') ?></textarea>
                    <textarea name="significance" placeholder="Project Significance" class="w-full border p-3 rounded-xl h-32" required><?= htmlspecialchars($existing_proposal['significance']??'') ?></textarea>
                </div>
            </div>

            <div class="form-step hidden" id="step-3">
                <h2 class="text-xl font-bold mb-6 text-slate-800">3. Literature & References</h2>
                <textarea name="literature_review" class="w-full border p-3 rounded-xl h-32 mb-4" placeholder="Literature Review"><?= htmlspecialchars($existing_proposal['literature_review']??'') ?></textarea>
                <textarea name="references" class="w-full border p-3 rounded-xl h-32" placeholder="References (APA style)"><?= htmlspecialchars($existing_proposal['references_list']??'') ?></textarea>
            </div>

            <div class="form-step hidden" id="step-4">
                <h2 class="text-xl font-bold mb-6 text-slate-800">4. Project Timeline</h2>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-[10px]">
                        <thead><tr class="bg-slate-800 text-white"><th class="p-2 border">Phase</th><?php for($i=1;$i<=16;$i++) echo "<th class='p-1 border'>W$i</th>"; ?></tr></thead>
                        <tbody>
                            <?php 
                            $saved_timeline = json_decode($existing_proposal['timeline']??'[]', true);
                            $phases = ['Requirement Collection','Literature Review','Design','Implementation','Testing','Report Writing'];
                            foreach($phases as $phase): ?>
                            <tr>
                                <td class="p-2 border font-bold"><?= $phase ?></td>
                                <?php for($i=1;$i<=16;$i++): 
                                    $checked = (isset($saved_timeline[$phase]) && in_array($i, $saved_timeline[$phase])) ? 'checked' : '';
                                ?>
                                    <td class="border text-center"><input type="checkbox" name="timeline[<?= $phase ?>][]" value="<?= $i ?>" <?= $checked ?>></td>
                                <?php endfor; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="form-step hidden" id="step-5">
                <h2 class="text-xl font-bold mb-6 text-slate-800">5. Terms & Authenticity</h2>
                <div class="bg-blue-50 p-6 rounded-xl border border-blue-100">
                    <p class="italic text-blue-900 mb-4">"I/We declare that this is original work..."</p>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="accept_terms" required class="w-5 h-5">
                        <span class="font-bold text-blue-800">I Accept the Terms</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-between mt-10 border-t pt-6">
                <button type="button" id="prevBtn" onclick="changeStep(-1)" class="px-6 py-2 border rounded-xl text-gray-500 hidden">Previous</button>
                <button type="button" id="nextBtn" onclick="changeStep(1)" class="px-8 py-2 bg-blue-600 text-white rounded-xl font-bold shadow-lg">Next</button>
                <button type="submit" id="submitBtn" class="px-8 py-2 bg-green-600 text-white rounded-xl font-bold shadow-lg" style="display:none"><?= $edit_id?'Update':'Submit' ?> Proposal</button>
            </div>
        </form>
    </div>
</div>

<style>
    .step-idx { width: 35px; height: 35px; border-radius: 50%; background: #e2e8f0; display: flex; align-items: center; justify-content: center; font-weight: bold; color: #64748b; }
    .active-step { background: #2563eb; color: white; box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3); }
</style>

<script>
    let currentStep = 1;
    function changeStep(n) {
        const steps = document.querySelectorAll('.form-step');
        steps[currentStep-1].classList.add('hidden');
        document.getElementById('idx-' + currentStep).classList.remove('active-step');
        currentStep += n;
        steps[currentStep-1].classList.remove('hidden');
        document.getElementById('idx-' + currentStep).classList.add('active-step');
        
        document.getElementById('prevBtn').classList.toggle('hidden', currentStep === 1);
        document.getElementById('nextBtn').style.display = (currentStep === 5) ? 'none' : 'block';
        document.getElementById('submitBtn').style.display = (currentStep === 5) ? 'block' : 'none';
    }

    function addStudent() {
        const list = document.getElementById('students-list');
        const idx = list.querySelectorAll('.student-row').length;
        const div = document.createElement('div');
        div.className = 'student-row flex gap-4 flex-wrap mt-2';
        div.innerHTML = `
            <input type="text" name="students[${idx}][name]" placeholder="Name" class="border p-3 rounded-xl flex-1" required>
            <input type="text" name="students[${idx}][id]" placeholder="ID" class="border p-3 rounded-xl w-32" required>
            <input type="email" name="students[${idx}][email]" placeholder="Email" class="border p-3 rounded-xl flex-1" required>
            <button type='button' onclick='this.parentNode.remove()' class='text-red-500 font-bold'>×</button>`;
        list.appendChild(div);
    }
</script>