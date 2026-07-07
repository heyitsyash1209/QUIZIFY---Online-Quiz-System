<?php
include("../config.php");

if(isset($_POST['register'])){

    $fullname        = $_POST['fullname'] ?? '';
    $institute_name  = $_POST['institute_name'] ?? '';
    $institute_email = $_POST['institute_email'] ?? '';
    $phone           = $_POST['phone'] ?? '';
    $password        = $_POST['password'] ?? '';
    $qualification   = $_POST['qualification'] ?? '';
    $department      = $_POST['department'] ?? '';
    $experience      = $_POST['experience'] ?? '';

    // Institute Email Check
    if(
        strpos($institute_email, '.edu') !== false ||
        strpos($institute_email, '.ac.in') !== false ||
        strpos($institute_email, '@itm.com') !== false
    ){

        // Password Encrypt
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Generate Quizify Email
        $random_number = rand(100,999);
        $name = strtolower(str_replace(' ','',$fullname));
        $quizify_email = "QF-" . $name . $random_number . "@quizify.com";

        // Generate Special Code
        $special_code = "QF-TCH-" . rand(10000,99999);

        // INSERT QUERY
        $query = "INSERT INTO teachers
        (fullname, institute_name, institute_email, quizify_email, phone, Password, qualification, department, experience, special_code)

        VALUES
        ('$fullname','$institute_name','$institute_email','$quizify_email','$phone','$hashed_password','$qualification','$department','$experience','$special_code')";

        mysqli_query($conn, $query);

        $success = true;

    }else{
        $error = "Use Institute Email Only ";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Teacher Registration</title>

<style>

body{
    margin:0;
    padding:0;
    font-family:Arial;

    background:url('https://images.unsplash.com/photo-1516321318423-f06f85e504b3')
    no-repeat center center/cover;

    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    position:relative;
}

body::before{
    content:"";
    position:absolute;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.65);
}

.box{
    position:relative;
    z-index:1;

    width:430px;
    padding:35px;

    background:rgba(255,255,255,0.12);
    backdrop-filter:blur(14px);

    border-radius:18px;
    border:1px solid rgba(255,255,255,0.2);

    color:white;
    text-align:center;
}

h2{
    margin-bottom:20px;
}

input, select{
    width:100%;
    padding:12px;
    margin:8px 0;

    border:none;
    border-radius:8px;

    outline:none;

    background:rgba(255,255,255,0.2);
    color:white;
}

input::placeholder{
    color:#ddd;
}

select option{
    color:black;
}

button{
    width:100%;
    padding:12px;

    border:none;
    border-radius:8px;

    background:linear-gradient(45deg,#ff512f,#dd2476);

    color:white;
    font-size:16px;

    cursor:pointer;
    transition:0.3s;
}

button:hover{
    transform:scale(1.03);
    box-shadow:0 0 15px #dd2476;
}

.success{
    background:rgba(0,255,100,0.2);
    padding:15px;
    border-radius:10px;
    margin-bottom:15px;
}

.error{
    background:rgba(255,0,0,0.2);
    padding:15px;
    border-radius:10px;
    margin-bottom:15px;
}

a{
    color:#00f2fe;
    text-decoration:none;
}

</style>
</head>

<body>

<div class="box">

<h2>👨‍🏫 Teacher Registration</h2>

<?php if(isset($success)){ ?>

<div class="success">

<b>Registration Successful ✅</b>

<br><br>

<b>Quizify Email:</b><br>
<?php echo $quizify_email; ?>

<br><br>

<b>Special Code:</b><br>
<?php echo $special_code; ?>

</div>

<?php } ?>

<?php if(isset($error)){ ?>

<div class="error">
<?php echo $error; ?>
</div>

<?php } ?>

<form method="POST">

<input type="text" name="fullname" placeholder="Full Name" required>

<input type="text" name="institute_name" placeholder="Institute Name" required>

<input type="email" name="institute_email" placeholder="Institute Email" required>

<input type="text" name="phone" placeholder="Phone Number" required>

<input type="password" name="password" placeholder="Password" required>

<input type="text" name="qualification" placeholder="Qualification" required>

<!-- DEPARTMENT -->
<input type="text" name="department" placeholder="Department (CSE, IT, etc)" required>

<!-- EXPERIENCE DROPDOWN -->
<select name="experience" required>
    <option value="">Select Experience</option>
    <?php
        for($i=1; $i<=30; $i++){
            echo "<option value='{$i} Years'>{$i} Years</option>";
        }
    ?>
</select>

<button type="submit" name="register">
Register
</button>

</form>

<p>
Already have an account?
<a href="adminlogin.php">Login</a>
</p>

</div>

</body>
</html>