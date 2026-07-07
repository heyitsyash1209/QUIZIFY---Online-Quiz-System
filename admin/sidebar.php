<div class="sidebar">

  <div class="logo">⚡ Admin Panel</div>

  <a href="dashboard.php?page=home" class="<?= ($page ?? '')=='home'?'active':'' ?>">🏠 Dashboard</a>

  <a href="dashboard.php?page=addquiz" class="<?= ($page ?? '')=='addquiz'?'active':'' ?>">➕ Add Quiz</a>

  <a href="dashboard.php?page=manage_quiz" class="<?= ($page ?? '')=='manage_quiz'?'active':'' ?>">📋 Manage Quiz</a>

  <a href="dashboard.php?page=add_question" class="<?= ($page ?? '')=='add_question'?'active':'' ?>">❓ Add Question</a>

  <a href="dashboard.php?page=upload_questions" class="<?= ($page ?? '')=='upload_questions'?'active':'' ?>">📂 Upload CSV</a>

  <a href="dashboard.php?page=manage_question" class="<?= ($page ?? '')=='manage_question'?'active':'' ?>">🛠 Manage Question</a>

  <a href="dashboard.php?page=user_analytics" class="<?= ($page ?? '')=='user_analytics'?'active':'' ?>">
👤 Users Analytics
</a>

  <a href="dashboard.php?page=user" class="<?= ($page ?? '')=='user'?'active':'' ?>">👥 Users</a>

  <a href="dashboard.php?page=analytics" class="<?= $page=='analytics'?'active':'' ?>">📈 Analytics</a>

  <a href="dashboard.php?page=result" class="<?= ($page ?? '')=='result'?'active':'' ?>">📊 Results</a>

  <hr>

  <a href="dashboard.php?page=admin_notification" class="<?= ($page=='admin_notification') ? 'active' : '' ?>">
🔔 Notifications
</a>

  <a href="dashboard.php?page=settings" class="<?= ($page=='settings') ? 'active' : '' ?>">
⚙ Settings
</a>
  <hr>

  <a href="../logout.php" class="logout">🚪 Logout</a>

</div>

<style>
.sidebar{
    width:260px;
    height:100vh;
    background: linear-gradient(180deg,#0f172a,#1e293b);
    padding:20px 15px;
    color:white;
    position:sticky;
    top:0;
    font-family:Segoe UI;
}

.logo{
    text-align:center;
    font-size:20px;
    font-weight:bold;
    margin-bottom:20px;
    color:#60a5fa;
}

.sidebar a{
    display:block;
    color:#cbd5e1;
    padding:12px;
    margin:5px 0;
    text-decoration:none;
    border-radius:10px;
    transition:0.2s;
    font-size:14px;
}

.sidebar a:hover{
    background:#334155;
    color:#fff;
    transform:translateX(5px);
}

.sidebar a.active{
    background:#2563eb;
    color:#fff;
    font-weight:bold;
}

.sidebar hr{
    border:0;
    border-top:1px solid #334155;
    margin:12px 0;
}

.sidebar a.logout{
    background:#dc2626;
    color:white;
}

.sidebar a.logout:hover{
    background:#b91c1c;
}
</style>