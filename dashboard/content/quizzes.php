<?php
// Secure Configuration and Session Routing Matrix Integration
include($_SERVER['DOCUMENT_ROOT'].'/quiz_system/config.php');

// User Authentication State Tracking
$isLoggedIn = isset($_SESSION['user_id']);

// Fetch active certification quizzes securely from the database
$q = mysqli_query($conn, "SELECT * FROM quizzes WHERE quiz_type IN ('free_certificate', 'paid_certificate')");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Certification Quizzes</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
    /* --- MODERN SAAS GRID & SYSTEM STYLING (UNTOUCHED UNLESS OVERRIDDEN BELOW) --- */
    .quiz-container {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        margin-top: 30px;
        padding: 0 20px;
        max-width: 1400px;
        margin-left: auto;
        margin-right: auto;
    }

    .quiz-title {
        font-size: 26px;
        font-weight: 700;
        margin-bottom: 24px;
        color: #0f172a;
        letter-spacing: -0.025em;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .quiz-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
        gap: 28px;
    }

    /* --- FIXED ISOLATED CARDS RE-DESIGN --- */
    .quiz-card {
        background: linear-gradient(135deg, #13203d, #1b2f55) !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        border-radius: 20px !important;
        padding: 20px !important;
        box-sizing: border-box !important;
        
        /* Rigid Constraints to Prevent Shifting/Jumping */
        min-height: 320px !important;
        max-height: 320px !important;
        overflow: hidden !important;
        
        display: flex !important;
        flex-direction: column !important;
        justify-content: space-between !important;
        position: relative !important;
        
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3), 0 8px 16px -6px rgba(0, 0, 0, 0.3) !important;
        transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.25s cubic-bezier(0.4, 0, 0.2, 1), border-color 0.25s ease !important;
    }

    /* Stable Hover State Matrix */
    .quiz-card:hover {
        transform: translateY(-4px) !important;
        border-color: rgba(96, 165, 250, 0.4) !important;
        box-shadow: 0 16px 30px -4px rgba(0, 0, 0, 0.5), 0 0 15px rgba(96, 165, 250, 0.15) !important;
    }

    /* --- HEADER META & GLASS BADGES --- */
    .quiz-header-meta {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        margin-bottom: 12px !important;
    }

    .type-badge {
        font-size: 11px !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        padding: 5px 12px !important;
        border-radius: 9999px !important;
        letter-spacing: 0.04em !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
    }

    /* Green Glass Verified Badge */
    .type-badge.type-free {
        background-color: rgba(34, 197, 94, 0.12) !important;
        color: #4ade80 !important;
        border: 1px solid rgba(34, 197, 94, 0.3) !important;
    }

    /* Gold Premium Professional Badge */
    .type-badge.type-paid {
        background-color: rgba(245, 158, 11, 0.12) !important;
        color: #fbbf24 !important;
        border: 1px solid rgba(245, 158, 11, 0.3) !important;
    }

    .price-tag {
        font-size: 17px !important;
        font-weight: 700 !important;
        color: #fbbf24 !important;
        letter-spacing: -0.01em !important;
    }

    /* --- COMPACT INTERIOR TYPOGRAPHY --- */
    .quiz-card h3 {
        font-size: 17px !important;
        font-weight: 600 !important;
        line-height: 1.4 !important;
        color: #ffffff !important;
        margin: 0 0 12px 0 !important;
        display: -webkit-box !important;
        -webkit-line-clamp: 2 !important;
        -webkit-box-orient: vertical !important;
        overflow: hidden !important;
        height: 48px !important;
    }

    /* --- META STATS ROW STRUCTURE --- */
    .quiz-info-grid {
        display: grid !important;
        grid-template-columns: 1fr 1fr !important;
        gap: 8px !important;
        padding: 10px 0 !important;
        border-top: 1px dashed rgba(255, 255, 255, 0.08) !important;
        border-bottom: 1px dashed rgba(255, 255, 255, 0.08) !important;
        margin-bottom: 14px !important;
    }

    .quiz-info {
        font-size: 12px !important;
        color: #cbd5e1 !important;
        font-weight: 500 !important;
        display: flex !important;
        align-items: center !important;
        gap: 6px !important;
    }

    .quiz-info i {
        color: #60a5fa !important;
        font-size: 13px !important;
    }

    .quiz-info strong {
        color: #ffffff !important;
    }

    /* Live Schedule Inner Subheading Frame */
    .quiz-schedule-banner {
        grid-column: span 2 !important;
        font-size: 11px !important;
        color: #cbd5e1 !important;
        background: rgba(255, 255, 255, 0.04) !important;
        border: 1px solid rgba(255, 255, 255, 0.06) !important;
        padding: 6px 10px !important;
        border-radius: 6px !important;
        display: flex !important;
        align-items: center !important;
        gap: 6px !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
    }

    .quiz-schedule-banner i {
        color: #60a5fa !important;
    }

    /* --- ACTION GRADIENT BLUE SAAS BUTTONS --- */
    .start-btn {
        width: 100% !important;
        text-align: center !important;
        padding: 11px 16px !important;
        background: linear-gradient(135deg, #3b82f6, #1d4ed8) !important;
        color: #ffffff !important;
        text-decoration: none !important;
        border-radius: 10px !important;
        font-size: 13.5px !important;
        font-weight: 600 !important;
        transition: opacity 0.2s ease, box-shadow 0.2s ease !important;
        display: block !important;
        box-sizing: border-box !important;
        border: none !important;
        box-shadow: 0 4px 12px rgba(29, 78, 216, 0.25) !important;
    }

    .start-btn:hover {
        background: linear-gradient(135deg, #4f46e5, #3b82f6) !important;
        box-shadow: 0 6px 16px rgba(59, 130, 246, 0.4) !important;
        color: #ffffff !important;
    }

    /* --- LOCKED COMPONENT MATRICES --- */
    .quiz-card.card-locked {
        background: linear-gradient(135deg, #0e182e, #142340) !important;
        border-color: rgba(255, 255, 255, 0.04) !important;
    }

    .quiz-card.card-locked:hover {
        transform: translateY(-4px) !important; /* Ensure hover translation metrics match perfectly */
    }

    .quiz-card.card-locked h3 {
        color: #94a3b8 !important;
    }

    .start-btn.locked {
        background: rgba(255, 255, 255, 0.08) !important;
        color: #94a3b8 !important;
        border: 1px solid rgba(255, 255, 255, 0.05) !important;
        box-shadow: none !important;
    }
    
    .start-btn.locked:hover {
        background: rgba(255, 255, 255, 0.12) !important;
        color: #ffffff !important;
    }
    </style>
</head>
<body>

<div class="quiz-container">
    <div class="quiz-title">
        <i class="fa-solid fa-graduation-cap" style="color: #2563eb;"></i> 
        <span>Available Certification Programs</span>
    </div>

    <div class="quiz-list">
        <?php 
        if ($q && mysqli_num_rows($q) > 0) {
            while($row = mysqli_fetch_assoc($q)){ 
                // Database Error Prevention Patch: Normalize case-sensitive column identity identifiers safely
                $quiz_id = isset($row['ID']) ? $row['ID'] : (isset($row['id']) ? $row['id'] : 0);
                $time_limit = isset($row['Time-limit']) ? $row['Time-limit'] : (isset($row['time_limit']) ? $row['time_limit'] : '--');
                
                // Track visitor structural locking layout state
                $isCardLocked = !$isLoggedIn;
        ?>
        <div class="quiz-card <?php echo $isCardLocked ? 'card-locked' : ''; ?>">
            
            <div>
                <div class="quiz-header-meta">
                    <?php if($row['quiz_type'] == 'free_certificate'){ ?>
                        <span class="type-badge type-free"><i class="fa-solid fa-award"></i> Free Verified</span>
                    <?php } elseif($row['quiz_type'] == 'paid_certificate') { ?>
                        <span class="type-badge type-paid"><i class="fa-solid fa-crown"></i> Premium Professional</span>
                        <span class="price-tag">₹<?php echo isset($row['price']) ? $row['price'] : '0'; ?></span>
                    <?php } ?>
                </div>

                <h3><?php echo htmlspecialchars($row['title']); ?></h3>

                <div class="quiz-info-grid">
                    <div class="quiz-info">
                        <i class="fa-regular fa-clock"></i>
                        <span>Duration: <strong><?php echo $time_limit; ?> Mins</strong></span>
                    </div>
                    <div class="quiz-info">
                        <i class="fa-solid fa-bullseye"></i>
                        <span>Passing: <strong><?php echo isset($row['passing_percentage']) ? $row['passing_percentage'] : '50'; ?>%</strong></span>
                    </div>
                    
                    <?php if($row['quiz_type'] == 'paid_certificate' && (!empty($row['exam_date']) || !empty($row['exam_time']))){ ?>
                        <div class="quiz-schedule-banner">
                            <i class="fa-regular fa-calendar-check"></i>
                            <span>Live: <strong><?php echo isset($row['exam_date']) ? $row['exam_date'] : '--'; ?></strong> | <strong><?php echo isset($row['exam_time']) ? $row['exam_time'] : '--'; ?></strong></span>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <div class="quiz-action-wrapper">
                <?php if($row['quiz_type'] == 'free_certificate'){ ?>
                    <?php if($isLoggedIn){ ?>
                        <a class="start-btn" href="/quiz_system/dashboard/level.php?quiz_id=<?php echo $quiz_id; ?>">
                            Start Examination <i class="fa-solid fa-arrow-right" style="margin-left: 4px; font-size: 12px;"></i>
                        </a>
                    <?php } else { ?>
                        <a class="start-btn locked" href="javascript:void(0)" onclick="showRegisterPopup()">
                            <i class="fa-solid fa-lock" style="margin-right: 6px;"></i> Register To Unlock
                        </a>
                    <?php } ?>
                <?php } ?>

                <?php if($row['quiz_type'] == 'paid_certificate'){ ?>
                    <?php if($isLoggedIn){ ?>
                        <a class="start-btn" href="content/register_quiz.php?quiz_id=<?php echo $quiz_id; ?>">
                            Secure Seat Registration <i class="fa-solid fa-chevron-right" style="margin-left: 4px; font-size: 11px;"></i>
                        </a>
                    <?php } else { ?>
                        <a class="start-btn locked" href="javascript:void(0)" onclick="showRegisterPopup()">
                            <i class="fa-solid fa-lock" style="margin-right: 6px;"></i> Authentication Required
                        </a>
                    <?php } ?>
                <?php } ?>
            </div>

        </div>
        <?php 
            } 
        } else {
        ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 60px 20px; color: #64748b; background: rgba(255,255,255,0.02); border-radius: 12px; border: 1px dashed rgba(255,255,255,0.1);">
                <i class="fa-regular fa-folder-open" style="font-size: 40px; margin-bottom: 12px; color: #94a3b8;"></i>
                <p style="font-size: 15px; font-weight: 500;">No active certification examinations detected at this time.</p>
            </div>
        <?php } ?>
    </div>
</div>

</body>
</html>