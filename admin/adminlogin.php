<?php
session_start();
include("../config.php");

if(isset($_POST['login'])){

    $email = $_POST['email'];
    $password = $_POST['password'];
    $special_code = $_POST['special_code'];

    $query = "SELECT * FROM teachers WHERE quizify_email='$email'";
    $result = mysqli_query($conn, $query);

    if(mysqli_num_rows($result) > 0){

        $row = mysqli_fetch_assoc($result);

        if(password_verify($password, $row['Password'])){

            if($special_code == $row['special_code']){

                /* ✅ FIXED SESSION (IMPORTANT) */
                $_SESSION['admin_id'] = $row['id'];
                $_SESSION['admin_name'] = $row['fullname'];
                $_SESSION['admin_email'] = $row['quizify_email'];

                header("Location: dashboard.php");
                exit();

            } else {
                $error = "Invalid Special Code!";
            }

        } else {
            $error = "Invalid Password!";
        }

    } else {
        $error = "Quizify Email Not Found!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Teacher Login</title>

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

    width:390px;
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

<h2>👨‍🏫 Teacher Login</h2>

<?php if(isset($error)){ ?>
<div class="error">
<?php echo $error; ?>
</div>
<?php } ?>

<form method="POST">

<input type="email" name="email" placeholder="Quizify Email" required>

<input type="password" name="password" placeholder="Password" required>

<input type="text" name="special_code" placeholder="Special Login Code" required>

<button type="submit" name="login">
Login
</button>

</form>

<p>
Don't have an account?
<a href="adminregister.php">Register</a>
</p>

</div>

</body>
</html>