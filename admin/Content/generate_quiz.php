<?php
include('../../config.php');

require_once __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->safeLoad();

/* GROQ API KEY */
$apiKey = $_ENV['GROK_API_KEY'];;

/* FORM DATA */
$title = mysqli_real_escape_string($conn,$_POST['title']);
$topic = mysqli_real_escape_string($conn,$_POST['topic']);
$difficulty = mysqli_real_escape_string($conn,$_POST['difficulty']);
$num_questions = (int)$_POST['num_questions'];

$time = (int)$_POST['time'];
$quiz_type = mysqli_real_escape_string($conn,$_POST['quiz_type']);

$certificate_enabled = (int)($_POST['certificate_enabled'] ?? 1);
$passing_percentage = (int)($_POST['passing_percentage'] ?? 50);

$price = (float)($_POST['price'] ?? 0);

$result_mode = mysqli_real_escape_string(
    $conn,
    $_POST['result_mode'] ?? 'instant'
);

$exam_date = !empty($_POST['exam_date'])
    ? $_POST['exam_date']
    : NULL;

$exam_time = !empty($_POST['exam_time'])
    ? $_POST['exam_time']
    : NULL;

/* CREATE QUIZ */
$quiz_sql = "
INSERT INTO quizzes
(
    title,
    `Time-limit`,
    quiz_type,
    price,
    passing_percentage,
    certificate_enabled,
    result_mode,
    exam_date,
    exam_time
)
VALUES
(
    '$title',
    '$time',
    '$quiz_type',
    '$price',
    '$passing_percentage',
    '$certificate_enabled',
    '$result_mode',
    ".($exam_date ? "'$exam_date'" : "NULL").",
    ".($exam_time ? "'$exam_time'" : "NULL")."
)
";

if(!mysqli_query($conn,$quiz_sql)){
    die("Quiz Insert Error: " . mysqli_error($conn));
}

$quiz_id = mysqli_insert_id($conn);

/* AI PROMPT */
$prompt = "Generate $num_questions MCQ questions in STRICT JSON only.

Topic: $topic
Difficulty: $difficulty

Return format:
[
 {
  \"question\":\"Question\",
  \"option1\":\"Option A\",
  \"option2\":\"Option B\",
  \"option3\":\"Option C\",
  \"option4\":\"Option D\",
  \"answer\":\"A\",
  \"correct_answer\":\"Option A\"
 }
]

Return ONLY JSON.
";

/* API DATA */
$data = [
    "model" => "llama-3.3-70b-versatile",
    "messages" => [
        [
            "role" => "user",
            "content" => $prompt
        ]
    ]
];

$ch = curl_init();

curl_setopt($ch,CURLOPT_URL,"https://api.groq.com/openai/v1/chat/completions");
curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
curl_setopt($ch,CURLOPT_POST,true);

curl_setopt($ch,CURLOPT_HTTPHEADER,[
    "Content-Type: application/json",
    "Authorization: Bearer ".$apiKey
]);

curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($data));

$response = curl_exec($ch);

if(curl_errno($ch)){
    die("Curl Error: ".curl_error($ch));
}

curl_close($ch);

$result = json_decode($response,true);

$content = $result['choices'][0]['message']['content'] ?? '';

$content = str_replace("```json","",$content);
$content = str_replace("```","",$content);
$content = trim($content);

$questions = json_decode($content,true);

if(!is_array($questions)){
    die("<pre>AI Response Error:\n".$content."</pre>");
}

$inserted = 0;

foreach($questions as $q){

    $question = mysqli_real_escape_string($conn,$q['question']);
    $o1 = mysqli_real_escape_string($conn,$q['option1']);
    $o2 = mysqli_real_escape_string($conn,$q['option2']);
    $o3 = mysqli_real_escape_string($conn,$q['option3']);
    $o4 = mysqli_real_escape_string($conn,$q['option4']);
    $answer = mysqli_real_escape_string($conn,$q['answer']);
    $correct = mysqli_real_escape_string($conn,$q['correct_answer']);

    $question_sql = "
    INSERT INTO questions
    (
        quiz_id,
        topic,
        Question,
        Option1,
        Option2,
        Option3,
        Option4,
        Answer,
        Difficulty,
        correct_answer
    )
    VALUES
    (
        '$quiz_id',
        '$topic',
        '$question',
        '$o1',
        '$o2',
        '$o3',
        '$o4',
        '$answer',
        '$difficulty',
        '$correct'
    )
    ";

    if(mysqli_query($conn,$question_sql)){

        $question_id = mysqli_insert_id($conn);

        mysqli_query($conn,"
        INSERT INTO quiz_questions
        (
            Quiz_id,
            Question_id
        )
        VALUES
        (
            '$quiz_id',
            '$question_id'
        )
        ");

        $inserted++;
    }
}

echo "
<script>
alert('Quiz Created Successfully! Quiz ID: $quiz_id | Questions Added: $inserted');
window.location.href='ai_quiz_generator.php';
</script>
";
?>