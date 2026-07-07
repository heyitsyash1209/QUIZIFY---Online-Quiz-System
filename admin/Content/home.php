<h2 style="margin-bottom:20px;">Admin Dashboard</h2>

<!-- CARDS -->
<div style="display:grid; grid-template-columns:repeat(4,1fr); gap:15px;">

<div style="background:white; padding:20px; border-radius:8px; text-align:center; box-shadow:0 2px 8px rgba(0,0,0,0.1);">
    <h3>Total Quizzes</h3>
    <p style="font-size:22px; color:#007bff;">--</p>
</div>

<div style="background:white; padding:20px; border-radius:8px; text-align:center; box-shadow:0 2px 8px rgba(0,0,0,0.1);">
    <h3>Total Questions</h3>
    <p style="font-size:22px; color:#28a745;">--</p>
</div>

<div style="background:white; padding:20px; border-radius:8px; text-align:center; box-shadow:0 2px 8px rgba(0,0,0,0.1);">
    <h3>Total Users</h3>
    <p style="font-size:22px; color:#ffc107;">--</p>
</div>

<div style="background:white; padding:20px; border-radius:8px; text-align:center; box-shadow:0 2px 8px rgba(0,0,0,0.1);">
    <h3>Total Results</h3>
    <p style="font-size:22px; color:#dc3545;">--</p>
</div>

</div>

<!-- QUICK ACTION -->
<div style="margin-top:30px; background:white; padding:20px; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.1);">

<h3>Quick Actions</h3>

<div style="margin-top:15px; display:flex; gap:15px; flex-wrap:wrap;">

<a href="dashboard.php?page=add_quiz" style="padding:10px 15px; background:#007bff; color:white; text-decoration:none; border-radius:5px;">
➕ Add Quiz
</a>

<a href="dashboard.php?page=add_question" style="padding:10px 15px; background:#28a745; color:white; text-decoration:none; border-radius:5px;">
❓ Add Question
</a>

<a href="dashboard.php?page=users" style="padding:10px 15px; background:#ffc107; color:black; text-decoration:none; border-radius:5px;">
👤 Manage Users
</a>

<a href="dashboard.php?page=results" style="padding:10px 15px; background:#dc3545; color:white; text-decoration:none; border-radius:5px;">
📊 View Results
</a>

</div>

</div>