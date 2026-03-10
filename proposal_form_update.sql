-- USERS TABLE
CREATE TABLE IF NOT EXISTS Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name_en VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('student','professor') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ANNOUNCEMENTS TABLE
CREATE TABLE IF NOT EXISTS Announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title_en VARCHAR(255) NOT NULL,
    title_ar VARCHAR(255),
    content_en TEXT NOT NULL,
    content_ar TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- PROPOSALS TABLE
CREATE TABLE IF NOT EXISTS Proposals (
    proposal_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    problem TEXT NOT NULL,
    objectives TEXT NOT NULL,
    significance TEXT NOT NULL,
    literature_review TEXT NOT NULL,
    references_list TEXT NOT NULL,
    supervisor_name VARCHAR(255) NOT NULL,
    supervisor_email VARCHAR(255) NOT NULL,
    cosupervisor_name VARCHAR(255),
    cosupervisor_email VARCHAR(255),
    timeline JSON NOT NULL,
    accept_terms BOOLEAN NOT NULL DEFAULT 0,
    prof_feedback TEXT,
    status ENUM('Pending','Approved','Rejected','Modifications Required') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- PROPOSAL STUDENTS TABLE
CREATE TABLE IF NOT EXISTS Proposal_Students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    proposal_id INT NOT NULL,
    student_name VARCHAR(255) NOT NULL,
    student_id VARCHAR(50) NOT NULL,
    student_email VARCHAR(255) NOT NULL,
    FOREIGN KEY (proposal_id) REFERENCES Proposals(proposal_id) ON DELETE CASCADE
);

-- INDEXES
CREATE INDEX IF NOT EXISTS idx_proposal_id ON Proposal_Students(proposal_id);

-- USERS
INSERT INTO Users (full_name_en, email, password, role) VALUES
('Alice Smith', 'alice@stu.uob.bh', 'password1', 'student'),
('Bob Johnson', 'bob@stu.uob.bh', 'password2', 'student'),
('Charlie Lee', 'charlie@stu.uob.bh', 'password3', 'student'),
('Diana King', 'diana@stu.uob.bh', 'password4', 'student'),
('Evan Wright', 'evan@stu.uob.bh', 'password5', 'student'),
('Prof. Ahmed', 'ahmed@uob.edu.bh', 'password6', 'professor'),
('Prof. Sara', 'sara@uob.edu.bh', 'password7', 'professor'),
('Prof. Omar', 'omar@uob.edu.bh', 'password8', 'professor'),
('Prof. Mona', 'mona@uob.edu.bh', 'password9', 'professor'),
('Prof. John', 'john@uob.edu.bh', 'password10', 'professor');

-- ANNOUNCEMENTS
INSERT INTO Announcements (title_en, title_ar, content_en, content_ar) VALUES
('Welcome', 'مرحبا', 'Welcome to the system!', 'مرحبا بكم في النظام!'),
('Deadline', 'الموعد النهائي', 'Project deadline is April 30.', 'الموعد النهائي للمشروع هو 30 أبريل.'),
('Holiday', 'عطلة', 'University will be closed next week.', 'الجامعة ستكون مغلقة الأسبوع القادم.'),
('Workshop', 'ورشة عمل', 'Join our research workshop.', 'انضم إلى ورشة البحث.'),
('Results', 'النتائج', 'Results will be announced soon.', 'سيتم إعلان النتائج قريباً.'),
('Update', 'تحديث', 'System update on Friday.', 'تحديث النظام يوم الجمعة.'),
('Reminder', 'تذكير', 'Submit your proposals.', 'قدم مقترحاتك.'),
('Event', 'حدث', 'Annual event next month.', 'الحدث السنوي الشهر القادم.'),
('Support', 'الدعم', 'Contact support for help.', 'اتصل بالدعم للمساعدة.'),
('Survey', 'استبيان', 'Fill out the survey.', 'املأ الاستبيان.');

-- PROPOSALS
INSERT INTO Proposals (title, problem, objectives, significance, literature_review, references_list, supervisor_name, supervisor_email, cosupervisor_name, cosupervisor_email, timeline, accept_terms, prof_feedback, status) VALUES
('AI Project', 'AI problem', 'AI objectives', 'AI significance', 'AI literature', 'AI refs', 'Prof. Ahmed', 'ahmed@uob.edu.bh', NULL, NULL, '{"phase": "1"}', 1, 'Good work', 'Approved'),
('Web App', 'Web problem', 'Web objectives', 'Web significance', 'Web literature', 'Web refs', 'Prof. Sara', 'sara@uob.edu.bh', NULL, NULL, '{"phase": "2"}', 1, 'Needs improvement', 'Modifications Required'),
('IoT System', 'IoT problem', 'IoT objectives', 'IoT significance', 'IoT literature', 'IoT refs', 'Prof. Omar', 'omar@uob.edu.bh', NULL, NULL, '{"phase": "3"}', 1, '', 'Pending'),
('ML Analysis', 'ML problem', 'ML objectives', 'ML significance', 'ML literature', 'ML refs', 'Prof. Mona', 'mona@uob.edu.bh', NULL, NULL, '{"phase": "4"}', 1, '', 'Pending'),
('Cloud App', 'Cloud problem', 'Cloud objectives', 'Cloud significance', 'Cloud literature', 'Cloud refs', 'Prof. John', 'john@uob.edu.bh', NULL, NULL, '{"phase": "5"}', 1, '', 'Pending'),
('Security', 'Security problem', 'Security objectives', 'Security significance', 'Security literature', 'Security refs', 'Prof. Ahmed', 'ahmed@uob.edu.bh', NULL, NULL, '{"phase": "6"}', 1, '', 'Pending'),
('Mobile App', 'Mobile problem', 'Mobile objectives', 'Mobile significance', 'Mobile literature', 'Mobile refs', 'Prof. Sara', 'sara@uob.edu.bh', NULL, NULL, '{"phase": "7"}', 1, '', 'Pending'),
('Data Mining', 'DM problem', 'DM objectives', 'DM significance', 'DM literature', 'DM refs', 'Prof. Omar', 'omar@uob.edu.bh', NULL, NULL, '{"phase": "8"}', 1, '', 'Pending'),
('Robotics', 'Robotics problem', 'Robotics objectives', 'Robotics significance', 'Robotics literature', 'Robotics refs', 'Prof. Mona', 'mona@uob.edu.bh', NULL, NULL, '{"phase": "9"}', 1, '', 'Pending'),
('Blockchain', 'Blockchain problem', 'Blockchain objectives', 'Blockchain significance', 'Blockchain literature', 'Blockchain refs', 'Prof. John', 'john@uob.edu.bh', NULL, NULL, '{"phase": "10"}', 1, '', 'Pending');

-- PROPOSAL STUDENTS
INSERT INTO Proposal_Students (proposal_id, student_name, student_id, student_email) VALUES
(1, 'Alice Smith', 'S1001', 'alice@stu.uob.bh'),
(2, 'Bob Johnson', 'S1002', 'bob@stu.uob.bh'),
(3, 'Charlie Lee', 'S1003', 'charlie@stu.uob.bh'),
(4, 'Diana King', 'S1004', 'diana@stu.uob.bh'),
(5, 'Evan Wright', 'S1005', 'evan@stu.uob.bh'),
(6, 'Alice Smith', 'S1001', 'alice@stu.uob.bh'),
(7, 'Bob Johnson', 'S1002', 'bob@stu.uob.bh'),
(8, 'Charlie Lee', 'S1003', 'charlie@stu.uob.bh'),
(9, 'Diana King', 'S1004', 'diana@stu.uob.bh'),
(10, 'Evan Wright', 'S1005', 'evan@stu.uob.bh');
