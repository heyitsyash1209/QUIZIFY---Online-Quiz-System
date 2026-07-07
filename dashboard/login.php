<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/quiz_system/config.php');

if(isset($_POST['login'])){

    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check User
    $query = "SELECT * FROM users WHERE email='$email'";

    $result = mysqli_query($conn, $query);

    if(mysqli_num_rows($result) > 0){

        $row = mysqli_fetch_assoc($result);

        // Verify Password
       if(password_verify($password, $row['Password'])){

    $_SESSION['user_id'] = $row['id'];
    $_SESSION['user_name'] = $row['fullname'];

    header("Location: dashboard.php");
    exit();

}else{
    $error = "Invalid Password!";
}
    }else{
        $error = "Email Not Found!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Student Login</title>

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

    width:380px;
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

input{
    width:100%;
    padding:12px;
    margin:10px 0;

    border:none;
    border-radius:8px;

    outline:none;

    background:rgba(255,255,255,0.2);
    color:white;
}

input::placeholder{
    color:#ddd;
}

button{
    width:100%;
    padding:12px;

    border:none;
    border-radius:8px;

    background:linear-gradient(45deg,#36d1dc,#5b86e5);

    color:white;
    font-size:16px;

    cursor:pointer;
    transition:0.3s;
}

button:hover{
    transform:scale(1.03);
    box-shadow:0 0 15px #5b86e5;
}

.error{
    background:rgba(255,0,0,0.2);
    padding:12px;
    border-radius:8px;
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

<h2>🎓 Student Login</h2>

<?php if(isset($error)){ ?>
<div class="error">
<?php echo $error; ?>
</div>
<?php } ?>

<form method="POST">

<input type="email" name="email" placeholder="Enter Email" required>

<input type="password" name="password" placeholder="Enter Password" required>

<button type="submit" name="login">
Login
</button>

</form>

<p>
Don't have an account?
<a href="studentregister.php">Register</a>
</p>

</div>

</body>
</html>