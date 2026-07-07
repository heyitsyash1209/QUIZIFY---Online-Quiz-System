<?php
include("../config.php");
if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$result = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($result);

if(!$user){
    die("User not found!");
}

// UPDATE
if(isset($_POST['update'])){

    $fullname  = $_POST['fullname'];
    $phone     = $_POST['phone'];
    $dob       = $_POST['dob'];
    $course    = $_POST['course'];
    $college   = $_POST['college'];
    $institute = $_POST['institute'];

    // PHOTO
    if(!empty($_FILES['profile_pic']['name'])){

        $img = time()."_".$_FILES['profile_pic']['name'];
        move_uploaded_file($_FILES['profile_pic']['tmp_name'], "../uploads/".$img);

        mysqli_query($conn,"UPDATE users SET profile_pic='$img' WHERE id='$user_id'");
    }

    // PASSWORD
    if(!empty($_POST['old_password']) && !empty($_POST['new_password'])){

        if(password_verify($_POST['old_password'], $user['Password'])){

            $new = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            mysqli_query($conn,"UPDATE users SET Password='$new' WHERE id='$user_id'");

        }else{
            $error = "Old password wrong!";
        }
    }

    mysqli_query($conn,"UPDATE users SET 
        fullname='$fullname',
        phone='$phone',
        dob='$dob',
        course='$course',
        college='$college',
        institute='$institute'
        WHERE id='$user_id'
    ");

    if(!isset($error)){
        $success = "Profile Updated Successfully!";
        $result = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
        $user = mysqli_fetch_assoc($result);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Settings</title>

<style>

/* BACKGROUND */
body{
    margin:0;
    font-family:Arial;
    background:#f1f5f9;
}

/* PAGE WRAPPER */
.wrapper{
    display:flex;
    justify-content:center;
    padding:40px;
}

/* MAIN CARD */
.card{
    width:700px;
    background:white;
    border-radius:15px;
    box-shadow:0 10px 30px rgba(0,0,0,0.1);
    overflow:hidden;
}

/* HEADER BLUE */
.header{
    background:linear-gradient(45deg,#3b82f6,#2563eb);
    color:white;
    padding:20px;
    text-align:center;
}

.header h2{
    margin:0;
}

/* BODY */
.body{
    padding:25px;
}

/* PROFILE SECTION */
.profile{
    text-align:center;
    margin-bottom:20px;
}

.profile img{
    width:100px;
    height:100px;
    border-radius:50%;
    border:3px solid #3b82f6;
    object-fit:cover;
}

/* CHANGE BUTTON */
.upload-btn{
    display:inline-block;
    margin-top:10px;
    padding:8px 12px;
    background:#3b82f6;
    color:white;
    border-radius:8px;
    cursor:pointer;
}

.upload-btn input{
    display:none;
}

/* INPUT STYLE */
input{
    width:100%;
    padding:12px;
    margin:8px 0;
    border:1px solid #ddd;
    border-radius:10px;
    outline:none;
    transition:0.3s;
}

input:focus{
    border-color:#3b82f6;
    box-shadow:0 0 5px #3b82f6;
}

/* SECTION TITLE */
.section{
    margin-top:15px;
    font-weight:bold;
    color:#2563eb;
}

/* BUTTON */
button{
    width:100%;
    padding:12px;
    border:none;
    border-radius:10px;
    background:#3b82f6;
    color:white;
    font-size:16px;
    cursor:pointer;
    margin-top:10px;
}

button:hover{
    background:#2563eb;
}

/* STATUS BOX */
.info{
    background:#f8fafc;
    padding:10px;
    border-radius:10px;
    font-size:14px;
    margin-top:10px;
}

/* MESSAGE */
.success{
    background:#dcfce7;
    color:#166534;
    padding:10px;
    border-radius:8px;
}

.error{
    background:#fee2e2;
    color:#991b1b;
    padding:10px;
    border-radius:8px;
}

</style>

</head>

<body>

<div class="wrapper">

<div class="card">

<!-- HEADER -->
<div class="header">
    <h2>⚙️ Account Settings</h2>
</div>

<div class="body">

<?php if(isset($success)){ ?>
<div class="success"><?php echo $success; ?></div>
<?php } ?>

<?php if(isset($error)){ ?>
<div class="error"><?php echo $error; ?></div>
<?php } ?>

<!-- PROFILE -->
<div class="profile">

<?php if(!empty($user['profile_pic'])) { ?>
    <img src="../uploads/<?php echo $user['profile_pic']; ?>">
<?php } else { ?>
    <img src="https://cdn-icons-png.flaticon.com/512/149/149071.png">
<?php } ?>

<br>

<label class="upload-btn">
📸 Change Photo
<input type="file" name="profile_pic">
</label>

</div>

<form method="POST" enctype="multipart/form-data">

<div class="section">👤 Personal Info</div>

<input type="text" name="fullname" value="<?php echo $user['fullname']; ?>" placeholder="Full Name">

<input type="text" name="phone" value="<?php echo $user['phone']; ?>" placeholder="Phone">

<input type="date" name="dob" value="<?php echo $user['dob']; ?>">

<input type="text" name="course" value="<?php echo $user['course']; ?>" placeholder="Course">

<input type="text" name="college" value="<?php echo $user['college']; ?>" placeholder="College">

<input type="text" name="institute" value="<?php echo $user['institute']; ?>" placeholder="Institute">

<div class="section">🔐 Security</div>

<input type="password" name="old_password" placeholder="Old Password">
<input type="password" name="new_password" placeholder="New Password">

<div class="section">📊 System Info</div>

<div class="info">
Status: <?php echo $user['status'] ?? 'active'; ?><br>
Last Login: <?php echo $user['last_login'] ?? 'N/A'; ?>
</div>

<button type="submit" name="update">Save Changes</button>

</form>

</div>

</div>

</div>

</body>
</html>