<?php
session_start();
require_once 'includes/db.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// ── AUTH ──────────────────────────────────────────────────────────────────────
if ($action === 'login') {
    $username = sanitize($conn, $_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $result = $conn->query("SELECT * FROM admins WHERE username = '$username' LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['full_name'];
            jsonResponse(true, 'Login successful', ['name' => $admin['full_name']]);
        }
    }
    jsonResponse(false, 'Invalid username or password.');
}

if ($action === 'logout') {
    session_destroy();
    jsonResponse(true, 'Logged out');
}

if ($action === 'check_session') {
    jsonResponse(isLoggedIn(), isLoggedIn() ? 'Active' : 'Not logged in', 
        isLoggedIn() ? ['name' => $_SESSION['admin_name']] : []);
}

// Protect all actions below
if (!isLoggedIn() && !in_array($action, ['login','logout','check_session'])) {
    jsonResponse(false, 'Unauthorized. Please log in.');
}

// ── DASHBOARD ─────────────────────────────────────────────────────────────────
if ($action === 'dashboard_stats') {
    $stats = [];
    $stats['total_students'] = $conn->query("SELECT COUNT(*) as c FROM students WHERE status='Active'")->fetch_assoc()['c'];
    $stats['total_subjects'] = $conn->query("SELECT COUNT(*) as c FROM subjects WHERE status='Active'")->fetch_assoc()['c'];
    $stats['total_classes']  = $conn->query("SELECT COUNT(*) as c FROM classes WHERE status='Active'")->fetch_assoc()['c'];
    $stats['total_departments'] = $conn->query("SELECT COUNT(*) as c FROM departments WHERE status='Active'")->fetch_assoc()['c'];
    $stats['total_faculty']  = $conn->query("SELECT COUNT(*) as c FROM faculty WHERE status='Active'")->fetch_assoc()['c'];

    // Grade summary
    $grades_summary = $conn->query("
        SELECT 
            SUM(CASE WHEN final_grade >= 90 THEN 1 ELSE 0 END) AS excellent,
            SUM(CASE WHEN final_grade >= 80 AND final_grade < 90 THEN 1 ELSE 0 END) AS very_good,
            SUM(CASE WHEN final_grade >= 70 AND final_grade < 80 THEN 1 ELSE 0 END) AS good,
            SUM(CASE WHEN final_grade >= 60 AND final_grade < 70 THEN 1 ELSE 0 END) AS satisfactory,
            SUM(CASE WHEN final_grade < 60 THEN 1 ELSE 0 END) AS below_60
        FROM grades WHERE final_grade IS NOT NULL
    ")->fetch_assoc();
    $stats['grade_summary'] = $grades_summary;

    // Recent students
    $recent = $conn->query("SELECT id_number, full_name, course, year_level, status FROM students ORDER BY created_at DESC LIMIT 5");
    $stats['recent_students'] = [];
    while ($row = $recent->fetch_assoc()) $stats['recent_students'][] = $row;

    jsonResponse(true, 'OK', $stats);
}

// ── STUDENTS ──────────────────────────────────────────────────────────────────
if ($action === 'get_students') {
    $search = sanitize($conn, $_GET['search'] ?? '');
    $course  = sanitize($conn, $_GET['course'] ?? '');
    $year    = sanitize($conn, $_GET['year'] ?? '');
    $page    = max(1, (int)($_GET['page'] ?? 1));
    $limit   = 10;
    $offset  = ($page - 1) * $limit;

    $where = "WHERE 1=1";
    if ($search) $where .= " AND (id_number LIKE '%$search%' OR full_name LIKE '%$search%' OR course LIKE '%$search%')";
    if ($course)  $where .= " AND course = '$course'";
    if ($year)    $where .= " AND year_level = '$year'";

    $total = $conn->query("SELECT COUNT(*) as c FROM students $where")->fetch_assoc()['c'];
    $result = $conn->query("SELECT * FROM students $where ORDER BY id_number ASC LIMIT $limit OFFSET $offset");
    $students = [];
    while ($row = $result->fetch_assoc()) $students[] = $row;

    jsonResponse(true, 'OK', ['students' => $students, 'total' => $total, 'page' => $page, 'limit' => $limit]);
}

if ($action === 'get_student') {
    $id = (int)($_GET['id'] ?? 0);
    $result = $conn->query("SELECT * FROM students WHERE id = $id LIMIT 1");
    if ($result && $result->num_rows > 0) {
        jsonResponse(true, 'OK', ['student' => $result->fetch_assoc()]);
    }
    jsonResponse(false, 'Student not found.');
}

if ($action === 'add_student') {
    $id_number  = sanitize($conn, $_POST['id_number'] ?? '');
    $full_name  = sanitize($conn, $_POST['full_name'] ?? '');
    $course     = sanitize($conn, $_POST['course'] ?? '');
    $year_level = sanitize($conn, $_POST['year_level'] ?? '');
    $section    = sanitize($conn, $_POST['section'] ?? '');
    $email      = sanitize($conn, $_POST['email'] ?? '');
    $contact    = sanitize($conn, $_POST['contact'] ?? '');
    $status     = sanitize($conn, $_POST['status'] ?? 'Active');
    $date_enrolled = sanitize($conn, $_POST['date_enrolled'] ?? date('Y-m-d'));

    if (empty($id_number) || empty($full_name) || empty($course)) {
        jsonResponse(false, 'Required fields are missing.');
    }

    $check = $conn->query("SELECT id FROM students WHERE id_number = '$id_number'");
    if ($check->num_rows > 0) jsonResponse(false, 'ID Number already exists.');

    $sql = "INSERT INTO students (id_number, full_name, course, year_level, section, email, contact, status, date_enrolled) 
            VALUES ('$id_number','$full_name','$course','$year_level','$section','$email','$contact','$status','$date_enrolled')";
    
    if ($conn->query($sql)) jsonResponse(true, 'Student added successfully.');
    jsonResponse(false, 'Failed to add student: ' . $conn->error);
}

if ($action === 'update_student') {
    $id         = (int)($_POST['id'] ?? 0);
    $full_name  = sanitize($conn, $_POST['full_name'] ?? '');
    $course     = sanitize($conn, $_POST['course'] ?? '');
    $year_level = sanitize($conn, $_POST['year_level'] ?? '');
    $section    = sanitize($conn, $_POST['section'] ?? '');
    $email      = sanitize($conn, $_POST['email'] ?? '');
    $contact    = sanitize($conn, $_POST['contact'] ?? '');
    $status     = sanitize($conn, $_POST['status'] ?? 'Active');

    $sql = "UPDATE students SET full_name='$full_name', course='$course', year_level='$year_level', 
            section='$section', email='$email', contact='$contact', status='$status' WHERE id=$id";

    if ($conn->query($sql)) jsonResponse(true, 'Student updated successfully.');
    jsonResponse(false, 'Failed to update: ' . $conn->error);
}

if ($action === 'delete_student') {
    $id = (int)($_POST['id'] ?? 0);
    if ($conn->query("DELETE FROM students WHERE id = $id")) jsonResponse(true, 'Student deleted.');
    jsonResponse(false, 'Failed to delete.');
}

// ── SUBJECTS ──────────────────────────────────────────────────────────────────
if ($action === 'get_subjects') {
    $search = sanitize($conn, $_GET['search'] ?? '');
    $where = $search ? "WHERE subject_code LIKE '%$search%' OR subject_description LIKE '%$search%'" : "";
    $result = $conn->query("SELECT * FROM subjects $where ORDER BY subject_code ASC");
    $subjects = [];
    while ($row = $result->fetch_assoc()) $subjects[] = $row;
    jsonResponse(true, 'OK', ['subjects' => $subjects]);
}

if ($action === 'add_subject') {
    $code = sanitize($conn, $_POST['subject_code'] ?? '');
    $desc = sanitize($conn, $_POST['subject_description'] ?? '');
    $units = (int)($_POST['units'] ?? 3);
    $type = sanitize($conn, $_POST['type'] ?? 'Major');
    $status = sanitize($conn, $_POST['status'] ?? 'Active');

    if (empty($code) || empty($desc)) jsonResponse(false, 'Required fields missing.');

    $check = $conn->query("SELECT id FROM subjects WHERE subject_code = '$code'");
    if ($check->num_rows > 0) jsonResponse(false, 'Subject code already exists.');

    if ($conn->query("INSERT INTO subjects (subject_code, subject_description, units, type, status) VALUES ('$code','$desc',$units,'$type','$status')"))
        jsonResponse(true, 'Subject added successfully.');
    jsonResponse(false, 'Failed to add subject.');
}

if ($action === 'update_subject') {
    $id = (int)($_POST['id'] ?? 0);
    $desc = sanitize($conn, $_POST['subject_description'] ?? '');
    $units = (int)($_POST['units'] ?? 3);
    $type = sanitize($conn, $_POST['type'] ?? 'Major');
    $status = sanitize($conn, $_POST['status'] ?? 'Active');

    if ($conn->query("UPDATE subjects SET subject_description='$desc', units=$units, type='$type', status='$status' WHERE id=$id"))
        jsonResponse(true, 'Subject updated.');
    jsonResponse(false, 'Failed to update.');
}

if ($action === 'delete_subject') {
    $id = (int)($_POST['id'] ?? 0);
    if ($conn->query("DELETE FROM subjects WHERE id = $id")) jsonResponse(true, 'Subject deleted.');
    jsonResponse(false, 'Failed to delete.');
}

// ── CLASSES ───────────────────────────────────────────────────────────────────
if ($action === 'get_classes') {
    $result = $conn->query("
        SELECT c.*, f.full_name AS adviser_name,
               (SELECT COUNT(*) FROM students s WHERE s.class_id = c.id AND s.status='Active') AS student_count
        FROM classes c
        LEFT JOIN faculty f ON f.id = c.adviser_id
        ORDER BY c.course, c.year_level, c.section
    ");
    $classes = [];
    while ($row = $result->fetch_assoc()) $classes[] = $row;
    jsonResponse(true, 'OK', ['classes' => $classes]);
}

if ($action === 'add_class') {
    $class_name = sanitize($conn, $_POST['class_name'] ?? '');
    $section    = sanitize($conn, $_POST['section'] ?? '');
    $course     = sanitize($conn, $_POST['course'] ?? '');
    $year_level = sanitize($conn, $_POST['year_level'] ?? '');
    $adviser_id = (int)($_POST['adviser_id'] ?? 0);
    $school_year = sanitize($conn, $_POST['school_year'] ?? '2024-2025');

    if ($conn->query("INSERT INTO classes (class_name, section, course, year_level, adviser_id, school_year) VALUES ('$class_name','$section','$course','$year_level',$adviser_id,'$school_year')"))
        jsonResponse(true, 'Class added.');
    jsonResponse(false, 'Failed to add class.');
}

if ($action === 'update_class') {
    $id = (int)($_POST['id'] ?? 0);
    $section    = sanitize($conn, $_POST['section'] ?? '');
    $course     = sanitize($conn, $_POST['course'] ?? '');
    $year_level = sanitize($conn, $_POST['year_level'] ?? '');
    $adviser_id = (int)($_POST['adviser_id'] ?? 0);

    if ($conn->query("UPDATE classes SET section='$section', course='$course', year_level='$year_level', adviser_id=$adviser_id WHERE id=$id"))
        jsonResponse(true, 'Class updated.');
    jsonResponse(false, 'Failed to update.');
}

if ($action === 'delete_class') {
    $id = (int)($_POST['id'] ?? 0);
    if ($conn->query("DELETE FROM classes WHERE id = $id")) jsonResponse(true, 'Class deleted.');
    jsonResponse(false, 'Failed to delete.');
}

// ── DEPARTMENTS ───────────────────────────────────────────────────────────────
if ($action === 'get_departments') {
    $result = $conn->query("SELECT * FROM departments ORDER BY dept_code ASC");
    $depts = [];
    while ($row = $result->fetch_assoc()) $depts[] = $row;
    jsonResponse(true, 'OK', ['departments' => $depts]);
}

if ($action === 'add_department') {
    $code  = sanitize($conn, $_POST['dept_code'] ?? '');
    $name  = sanitize($conn, $_POST['dept_name'] ?? '');
    $chair = sanitize($conn, $_POST['chairperson'] ?? '');
    $status = sanitize($conn, $_POST['status'] ?? 'Active');

    if (empty($code) || empty($name)) jsonResponse(false, 'Required fields missing.');
    $check = $conn->query("SELECT id FROM departments WHERE dept_code='$code'");
    if ($check->num_rows > 0) jsonResponse(false, 'Department code already exists.');

    if ($conn->query("INSERT INTO departments (dept_code, dept_name, chairperson, status) VALUES ('$code','$name','$chair','$status')"))
        jsonResponse(true, 'Department added.');
    jsonResponse(false, 'Failed to add department.');
}

if ($action === 'update_department') {
    $id   = (int)($_POST['id'] ?? 0);
    $name = sanitize($conn, $_POST['dept_name'] ?? '');
    $chair = sanitize($conn, $_POST['chairperson'] ?? '');
    $status = sanitize($conn, $_POST['status'] ?? 'Active');

    if ($conn->query("UPDATE departments SET dept_name='$name', chairperson='$chair', status='$status' WHERE id=$id"))
        jsonResponse(true, 'Department updated.');
    jsonResponse(false, 'Failed to update.');
}

if ($action === 'delete_department') {
    $id = (int)($_POST['id'] ?? 0);
    if ($conn->query("DELETE FROM departments WHERE id=$id")) jsonResponse(true, 'Department deleted.');
    jsonResponse(false, 'Failed to delete.');
}

// ── FACULTY ───────────────────────────────────────────────────────────────────
if ($action === 'get_faculty') {
    $search = sanitize($conn, $_GET['search'] ?? '');
    $where = $search ? "WHERE f.full_name LIKE '%$search%' OR d.dept_code LIKE '%$search%'" : "";
    $result = $conn->query("
        SELECT f.*, d.dept_code, d.dept_name 
        FROM faculty f
        LEFT JOIN departments d ON d.id = f.department_id
        $where
        ORDER BY f.faculty_id ASC
    ");
    $faculty = [];
    while ($row = $result->fetch_assoc()) $faculty[] = $row;
    jsonResponse(true, 'OK', ['faculty' => $faculty]);
}

if ($action === 'add_faculty') {
    $faculty_id = sanitize($conn, $_POST['faculty_id'] ?? '');
    $full_name  = sanitize($conn, $_POST['full_name'] ?? '');
    $dept_id    = (int)($_POST['department_id'] ?? 0);
    $position   = sanitize($conn, $_POST['position'] ?? '');
    $email      = sanitize($conn, $_POST['email'] ?? '');
    $contact    = sanitize($conn, $_POST['contact'] ?? '');
    $status     = sanitize($conn, $_POST['status'] ?? 'Active');

    if (empty($faculty_id) || empty($full_name)) jsonResponse(false, 'Required fields missing.');
    $check = $conn->query("SELECT id FROM faculty WHERE faculty_id='$faculty_id'");
    if ($check->num_rows > 0) jsonResponse(false, 'Faculty ID already exists.');

    if ($conn->query("INSERT INTO faculty (faculty_id, full_name, department_id, position, email, contact, status) VALUES ('$faculty_id','$full_name',$dept_id,'$position','$email','$contact','$status')"))
        jsonResponse(true, 'Faculty added.');
    jsonResponse(false, 'Failed to add faculty.');
}

if ($action === 'update_faculty') {
    $id       = (int)($_POST['id'] ?? 0);
    $full_name = sanitize($conn, $_POST['full_name'] ?? '');
    $dept_id   = (int)($_POST['department_id'] ?? 0);
    $position  = sanitize($conn, $_POST['position'] ?? '');
    $email     = sanitize($conn, $_POST['email'] ?? '');
    $contact   = sanitize($conn, $_POST['contact'] ?? '');
    $status    = sanitize($conn, $_POST['status'] ?? 'Active');

    if ($conn->query("UPDATE faculty SET full_name='$full_name', department_id=$dept_id, position='$position', email='$email', contact='$contact', status='$status' WHERE id=$id"))
        jsonResponse(true, 'Faculty updated.');
    jsonResponse(false, 'Failed to update.');
}

if ($action === 'delete_faculty') {
    $id = (int)($_POST['id'] ?? 0);
    if ($conn->query("DELETE FROM faculty WHERE id=$id")) jsonResponse(true, 'Faculty deleted.');
    jsonResponse(false, 'Failed to delete.');
}

$conn->close();
?>
