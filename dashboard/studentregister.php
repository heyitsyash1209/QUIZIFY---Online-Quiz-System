<?php
include("../config.php");

if(isset($_POST['register'])){

    $fullname  = $_POST['fullname'];
    $username  = $_POST['username'];
    $dob       = $_POST['dob'];
    $course    = $_POST['course'];
    $email     = $_POST['email'];
    $phone     = $_POST['phone'];
    $password  = $_POST['password'];
    $college   = $_POST['college'];
    $institute = $_POST['institute'];

    // Password Encrypt
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert Query
    $query = "INSERT INTO users 
    (fullname, Username, dob, course, email, phone, Password, college, institute)

    VALUES 
    ('$fullname', '$username', '$dob', '$course', '$email', '$phone', '$hashed_password', '$college', '$institute')";

    mysqli_query($conn, $query);

    header("Location: login.php");
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Student Register</title>

<style>

body{
    margin:0;
    padding:0;
    font-family:Arial;

    background:url('https://images.unsplash.com/photo-1522202176988-66273c2fd55f')
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

    width:420px;
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

    background:linear-gradient(45deg,#667eea,#764ba2);

    color:white;
    font-size:16px;

    cursor:pointer;
    transition:0.3s;
}

button:hover{
    transform:scale(1.03);
    box-shadow:0 0 15px #764ba2;
}

a{
    color:#00f2fe;
    text-decoration:none;
}

</style>
</head>

<body>

<div class="box">

<h2>🎓 Student Registration</h2>

<form method="POST">

<input type="text" name="fullname" placeholder="Full Name" required>

<input type="text" name="username" placeholder="Username" required>

<input type="date" name="dob" required>

<select name="course" required>
    <option value="">Select Course</option>
    <option>BCA</option>
    <option>B.Tech</option>
    <option>MCA</option>
    <option>Other..</option>
    
</select>

<input type="email" name="email" placeholder="Email Address" required>

<input type="text" name="phone" placeholder="Phone Number" required>

<input type="password" name="password" placeholder="Password" required>

<input type="text" name="college" placeholder="College Name" required>

<input type="text" name="institute" placeholder="Institute Name" required>

<button type="submit" name="register">
Register
</button>

</form>

<p>
Already have an account?
<a href="login.php">Login</a>
</p>

</div>

</body>
</html>