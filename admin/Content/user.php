<?php
include($_SERVER['DOCUMENT_ROOT'].'/quiz_system/config.php');

$query = mysqli_query($conn, "SELECT * FROM users");
?>

<!DOCTYPE html>
<html>
<head>
<title>Users</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{
    background:#f5f7fb;
    font-family:Segoe UI;
}

.container{
    max-width:900px;
}

/* USER CARD */
.user-card{
    background:white;
    border-radius:14px;
    padding:12px 15px;
    margin-bottom:10px;
    box-shadow:0 3px 10px rgba(0,0,0,0.05);
    cursor:pointer;
    transition:0.2s;
}

.user-card:hover{
    transform:scale(1.01);
}

/* DETAILS */
.user-details{
    display:none;
    margin-top:10px;
    padding-top:10px;
    border-top:1px solid #eee;
    font-size:13px;
    color:#334155;
}

/* NAME */
.username{
    font-weight:600;
    color:#2563eb;
}

/* SEARCH */
.search-box{
    margin-bottom:15px;
}

input{
    border-radius:10px;
    padding:10px;
    border:1px solid #ddd;
}
</style>

</head>

<body>

<div class="container mt-4">

<h3 class="mb-3">👥 All Users</h3>

<!-- SEARCH -->
<input type="text" id="search" class="form-control search-box" placeholder="Search user...">

<?php while($row = mysqli_fetch_assoc($query)){ ?>

<div class="user-card" onclick="toggle(this)">

    <!-- ONLY NAME SHOW -->
    <div class="username">
        👤 <?= $row['fullname'] ?>
    </div>

    <!-- DETAILS (HIDDEN INITIALLY) -->
    <div class="user-details">

        <p>Username: <?= $row['Username'] ?></p>
        <p>Email: <?= $row['email'] ?></p>
        <p>Branch/Course: <?= $row['course'] ?></p>
        <p>Institute: <?= $row['institute'] ?></p>
        <p>Points: <?= $row['points'] ?></p>
        <p>Badge: <?= $row['badge'] ?></p>
        <p>Status: <?= $row['status'] ?></p>

    </div>

</div>

<?php } ?>

</div>

<script>

/* TOGGLE DETAILS */
function toggle(el){
    let details = el.querySelector(".user-details");
    details.style.display = (details.style.display === "block") ? "none" : "block";
}

/* SEARCH FILTER */
document.getElementById("search").addEventListener("keyup", function(){
    let value = this.value.toLowerCase();
    let cards = document.querySelectorAll(".user-card");

    cards.forEach(card => {
        let name = card.querySelector(".username").innerText.toLowerCase();
        card.style.display = name.includes(value) ? "block" : "none";
    });
});

</script>

</body>
</html>