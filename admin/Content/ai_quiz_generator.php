```php
<?php include('../../config.php'); ?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>AI Quiz Generator</title>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family: Arial, sans-serif;
    background:#0f172a;
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    padding:30px;
}

.container{
    width:100%;
    max-width:900px;
}

.card{
    background:#111827;
    border-radius:20px;
    padding:30px;
    box-shadow:0 0 40px rgba(0,0,0,.4);
}

.heading{
    text-align:center;
    color:white;
    margin-bottom:25px;
}

.heading h1{
    font-size:32px;
    margin-bottom:8px;
}

.heading p{
    color:#94a3b8;
}

.form-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:15px;
}

.full{
    grid-column:1 / -1;
}

label{
    display:block;
    color:#e5e7eb;
    margin-bottom:6px;
    font-size:14px;
}

input,
select{
    width:100%;
    padding:12px;
    border:none;
    border-radius:10px;
    background:#1f2937;
    color:white;
    outline:none;
}

input:focus,
select:focus{
    border:1px solid #22c55e;
}

.section{
    margin-top:20px;
    padding:20px;
    border-radius:12px;
    background:#1e293b;
}

.section-title{
    color:white;
    margin-bottom:15px;
    font-weight:bold;
}

button{
    width:100%;
    margin-top:25px;
    padding:14px;
    border:none;
    border-radius:12px;
    background:#22c55e;
    color:white;
    font-size:16px;
    cursor:pointer;
    font-weight:bold;
}

button:hover{
    background:#16a34a;
}

#paidFields{
    display:none;
}

.loader{
    display:none;
    text-align:center;
    margin-top:20px;
    color:white;
}

.typing{
    display:inline-block;
}

.typing span{
    animation: blink 1.4s infinite;
}

.typing span:nth-child(2){
    animation-delay:.2s;
}

.typing span:nth-child(3){
    animation-delay:.4s;
}

@keyframes blink{
    0%{opacity:.2}
    20%{opacity:1}
    100%{opacity:.2}
}

@media(max-width:768px){

.form-grid{
    grid-template-columns:1fr;
}

}

</style>

</head>
<body>

<div class="container">

<div class="card">

<div class="heading">
<h1>🤖 AI Quiz Generator</h1>
<p>Create complete quizzes using AI</p>
</div>

<form action="generate_quiz.php" method="POST" onsubmit="showLoader()">

<div class="form-grid">

<div class="full">
<label>Quiz Title</label>
<input type="text"
name="title"
placeholder="e.g DBMS Master Test"
required>
</div>

<div>
<label>Topic</label>
<input type="text"
name="topic"
placeholder="DBMS, PHP, Java..."
required>
</div>

<div>
<label>Difficulty</label>
<select name="difficulty">
<option value="Easy">Easy</option>
<option value="Medium">Medium</option>
<option value="Hard">Hard</option>
</select>
</div>

<div>
<label>Questions Count</label>
<input type="number"
name="num_questions"
value="10"
min="1"
max="50"
required>
</div>

<div>
<label>Time Limit (Minutes)</label>
<input type="number"
name="time"
value="30"
required>
</div>

</div>

<div class="section">

<div class="section-title">
📝 Quiz Type
</div>

<select
name="quiz_type"
id="quiz_type"
onchange="toggleFields()">

<option value="practice">
Practice Quiz
</option>

<option value="free_certificate">
Free Certification Quiz
</option>

<option value="paid_certificate">
Paid Certification Quiz
</option>

</select>

</div>

<div class="section">

<div class="section-title">
🎓 Certificate Settings
</div>

<div class="form-grid">

<div>
<label>Certificate Enabled</label>

<select name="certificate_enabled">

<option value="1">
Yes
</option>

<option value="0">
No
</option>

</select>

</div>

<div>
<label>Passing Percentage</label>

<input
type="number"
name="passing_percentage"
value="50"
min="0"
max="100">

</div>

</div>

</div>

<div class="section" id="paidFields">

<div class="section-title">
💳 Paid Quiz Settings
</div>

<div class="form-grid">

<div>
<label>Price</label>

<input
type="number"
name="price"
value="0">
</div>

<div>
<label>Result Mode</label>

<select name="result_mode">

<option value="instant">
Instant Result
</option>

<option value="manual">
Manual Result
</option>

</select>

</div>

<div>
<label>Exam Date</label>

<input
type="date"
name="exam_date">

</div>

<div>
<label>Exam Time</label>

<input
type="time"
name="exam_time">

</div>

</div>

</div>

<button type="submit">
🚀 Generate Quiz With AI
</button>

<div class="loader" id="loader">

Generating Quiz Using AI

<div class="typing">
<span>.</span>
<span>.</span>
<span>.</span>
</div>

</div>

</form>

</div>

</div>

<script>

function toggleFields(){

    let type =
    document.getElementById('quiz_type').value;

    let paid =
    document.getElementById('paidFields');

    if(type === 'paid_certificate'){
        paid.style.display='block';
    }else{
        paid.style.display='none';
    }
}

toggleFields();

function showLoader(){
    document.getElementById('loader').style.display='block';
}

</script>

</body>
</html>
```
