<?php
include($_SERVER['DOCUMENT_ROOT'].'/quiz_system/config.php');

/* ================= GET TEACHER ================= */
$teacher_id = $_SESSION['teacher_id'] ?? 0;

$query_result = mysqli_query($conn, "SELECT * FROM teachers WHERE id='$teacher_id'");
$teacher = mysqli_fetch_assoc($query_result);

if (!$teacher) {
    $teacher = [
        'fullname' => '',
        'quizify_email' => '',
        'institute_name' => '',
        'department' => '',
        'experience' => '',
        'phone' => '',
        'qualification' => '',
        'Password' => ''
    ];
}

/* ================= UPDATE PROFILE ================= */
if(isset($_POST['update_profile'])){

    $fullname        = $_POST['fullname'] ?? '';
    $phone           = $_POST['phone'] ?? '';
    $qualification   = $_POST['qualification'] ?? '';
    $department      = $_POST['department'] ?? '';
    $experience      = $_POST['experience'] ?? '';

    mysqli_query($conn,"
    UPDATE teachers SET
    fullname='$fullname',
    phone='$phone',
    qualification='$qualification',
    department='$department',
    experience='$experience'
    WHERE id='$teacher_id'
    ");

    $teacher['fullname'] = $fullname;
    $teacher['phone'] = $phone;
    $teacher['qualification'] = $qualification;
    $teacher['department'] = $department;
    $teacher['experience'] = $experience;

    $msg = "Profile Updated Successfully ✅";
}

/* ================= CHANGE PASSWORD ================= */
if(isset($_POST['change_pass'])){

    $old = $_POST['old_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $db_password = $teacher['Password'] ?? '';

    if(!empty($old) && !empty($db_password) && password_verify($old, $db_password)){

        $hashed = password_hash($new, PASSWORD_DEFAULT);

        mysqli_query($conn,"
        UPDATE teachers SET Password='$hashed'
        WHERE id='$teacher_id'
        ");

        $teacher['Password'] = $hashed; 
        $msg = "Password Changed Successfully 🔐";

    }else{
        $error = "Old Password is incorrect ❌";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Settings</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
body {
    background: #eef2ff;
    font-family: 'Plus Jakarta Sans', sans-serif;
    color: #334155;
}

.container {
    max-width: 1050px;
}

/* HEADER STYLE */
h3 {
    color: #1d4ed8;
    font-weight: 700;
    letter-spacing: -0.5px;
}

/* MODERN CARD REFACTORING */
.card {
    border: none;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
    background: white;
    transition: transform 0.2s ease;
}

.card h5 {
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 20px;
    font-size: 1.15rem;
}

/* SIDEBAR PROFILE GRADIENT BOX */
.profile-box {
    background: linear-gradient(135deg, #2563eb, #60a5fa);
    color: white;
    border-radius: 16px;
    padding: 28px 24px;
    box-shadow: 0 10px 20px rgba(37, 99, 235, 0.15);
    position: sticky;
    top: 24px;
}

.profile-box h5 {
    font-weight: 700;
    margin-bottom: 4px;
    font-size: 1.25rem;
}

.profile-box hr {
    border-color: rgba(255, 255, 255, 0.2);
    margin: 20px 0;
}

.small {
    font-size: 13.5px;
    opacity: 0.9;
    font-weight: 500;
}

/* LABELS & INPUT FORM FIELDS */
.form-label {
    font-size: 13px;
    font-weight: 600;
    color: #64748b;
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.form-control, .form-select {
    border-radius: 10px !important;
    border: 1px solid #cbd5e1;
    padding: 10px 14px;
    font-size: 14.5px;
    color: #1e293b;
    background-color: #f8fafc;
    transition: all 0.2s ease-in-out;
}

.form-control:focus, .form-select:focus {
    background-color: #fff;
    border-color: #2563eb;
    box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
    outline: none;
}

/* BUTTON CONTROLS */
.btn-primary {
    background: #2563eb;
    border: none;
    padding: 11px 24px;
    font-weight: 600;
    font-size: 14.5px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
    transition: all 0.2s ease;
}

.btn-primary:hover {
    background: #1e40af;
    transform: translateY(-1px);
    box-shadow: 0 6px 15px rgba(37, 99, 235, 0.3);
}

.btn-warning {
    padding: 11px 24px;
    font-weight: 600;
    font-size: 14.5px;
    border-radius: 10px;
    transition: all 0.2s ease;
}

.btn-warning:hover {
    transform: translateY(-1px);
}

.alert {
    border-radius: 12px;
    border: none;
    font-weight: 500;
    font-size: 14.5px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.03);
}
</style>
</head>

<body>

<div class="container mt-5">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <h3>⚙️ Teacher Settings</h3>
    </div>

    <?php if(isset($msg)){ ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $msg ?>
    </div>
    <?php } ?>

    <?php if(isset($error)){ ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $error ?>
    </div>
    <?php } ?>

    <div class="row g-4 mt-1">

        <div class="col-lg-4">
            <div class="profile-box">
                <h5><?= htmlspecialchars($teacher['fullname'] ?? '', ENT_QUOTES, 'UTF-8') ?></h5>
                <p class="small mb-0 text-white-50"><?= htmlspecialchars($teacher['quizify_email'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                
                <hr>
                
                <div class="d-flex flex-column gap-2">
                    <div class="small">🏫 Institute: <span class="fw-bold"><?= htmlspecialchars($teacher['institute_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></span></div>
                    <div class="small">📂 Department: <span class="fw-bold"><?= htmlspecialchars($teacher['department'] ?? '', ENT_QUOTES, 'UTF-8') ?></span></div>
                    <div class="small">💼 Experience: <span class="fw-bold"><?= htmlspecialchars($teacher['experience'] ?? '', ENT_QUOTES, 'UTF-8') ?></span></div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            
            <div class="card mb-4">
                <h5>Update Profile Information</h5>
                
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="fullname" value="<?= htmlspecialchars($teacher['fullname'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Enter Full Name">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Phone Number</label>
                            <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($teacher['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Enter Phone Number">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Qualification</label>
                            <input type="text" class="form-control" name="qualification" value="<?= htmlspecialchars($teacher['qualification'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="e.g. M.Tech, PhD">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Department</label>
                            <input type="text" class="form-control" name="department" value="<?= htmlspecialchars($teacher['department'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="e.g. Computer Applications">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Teaching Experience</label>
                            <select class="form-select" name="experience">
                                <option value="<?= htmlspecialchars($teacher['experience'] ?? '', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($teacher['experience'] ?? 'Select Experience', ENT_QUOTES, 'UTF-8') ?></option>
                                <?php for($i=1;$i<=30;$i++){ ?>
                                <option value="<?= $i ?> Years"><?= $i ?> Years</option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <button class="btn btn-primary" name="update_profile">
                            Save Profile Changes
                        </button>
                    </div>
                </form>
            </div>

            <div class="card">
                <h5>Change Account Security Password</h5>
                
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Current Password</label>
                            <input type="password" class="form-control" name="old_password" placeholder="••••••••" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">New Secure Password</label>
                            <input type="password" class="form-control" name="new_password" placeholder="••••••••" required>
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <button class="btn btn-warning text-dark" name="change_pass">
                            Update Password Token
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<div class="mb-5"></div>

</body>
</html>