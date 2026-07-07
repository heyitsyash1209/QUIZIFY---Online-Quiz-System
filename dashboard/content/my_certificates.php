<?php

include($_SERVER['DOCUMENT_ROOT'].'/quiz_system/config.php');

$user_id = $_SESSION['user_id'];

// FETCH CERTIFICATES
$query = mysqli_query($conn,"
SELECT certificates.*, quizzes.Title AS quiz_title
FROM certificates
JOIN quizzes ON certificates.quiz_id = quizzes.ID
WHERE certificates.user_id='$user_id'
ORDER BY certificates.id DESC
");
?>

<style>
.cert-container{
    padding:20px;
}

.cert-title{
    font-size:28px;
    font-weight:bold;
    margin-bottom:20px;
    color:#1e293b;
}

.cert-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(320px,1fr));
    gap:20px;
}

.cert-card{
    background:white;
    border-radius:16px;
    padding:20px;
    box-shadow:0 5px 15px rgba(0,0,0,0.08);
    border-left:5px solid #2563eb;
    transition:0.3s;
}

.cert-card:hover{
    transform:translateY(-5px);
}

.cert-card h3{
    margin:0 0 10px;
    color:#2563eb;
}

.cert-info{
    color:#475569;
    line-height:1.8;
}

.download-btn{
    display:inline-block;
    margin-top:15px;
    padding:10px 16px;
    background:#2563eb;
    color:white;
    text-decoration:none;
    border-radius:8px;
    transition:0.3s;
}

.download-btn:hover{
    background:#1d4ed8;
}

.empty{
    background:white;
    padding:40px;
    border-radius:16px;
    text-align:center;
    box-shadow:0 5px 15px rgba(0,0,0,0.08);
}
</style>

<div class="cert-container">

<div class="cert-title">🎓 My Certificates</div>

<?php if(mysqli_num_rows($query) > 0){ ?>

<div class="cert-grid">

<?php while($row = mysqli_fetch_assoc($query)){ ?>

<div class="cert-card">

<h3><?php echo $row['quiz_title']; ?></h3>

<div class="cert-info">

<b>Certificate No:</b><br>
<?php echo $row['certificate_no']; ?>

<br><br>

<b>Score:</b><br>
<?php echo $row['score']; ?>%

<br><br>

<b>Issue Date:</b><br>
<?php echo $row['issue_date']; ?>

</div>

<a class="download-btn"
href="/quiz_system/certificates/download_certificate.php?id=<?php echo $row['id']; ?>">
⬇ Download Certificate
</a>

</div>

<?php } ?>

</div>

<?php } else { ?>

<div class="empty">
<h2>😔 No Certificates Yet</h2>
<p>Complete quizzes and pass quizzes to earn certificates.</p>
</div>

<?php } ?>

</div>