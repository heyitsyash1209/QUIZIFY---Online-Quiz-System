<?php
/**
 * Quiz System Notification Interface Engine
 * Path: /quiz_system/dashboard/content/user_notification.php
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include($_SERVER['DOCUMENT_ROOT'].'/quiz_system/config.php');

/* ================= SESSION SECURITY VALIDATION ================= */
if (!isset($_SESSION['user_id'])) {
    // Return structured JSON data if an unauthenticated user hits an AJAX route
    if (isset($_GET['ajax']) || isset($_GET['ajax_action'])) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Session expired. Please log in again.']);
        exit;
    }
    die("Login required. Please login first.");
}

$user_id = mysqli_real_escape_string($conn, $_SESSION['user_id']);

/* ================= HELPER FUNCTION: RENDERING DASHBOARD CARDS ================= */
function getNotificationsHTML($conn, $user_id) {
    $user_id = mysqli_real_escape_string($conn, $user_id);
    $q = mysqli_query($conn, "
        SELECT * FROM notifications
        WHERE user_id = '$user_id' OR user_id IS NULL
        ORDER BY id DESC
        LIMIT 30
    ");
    
    $html = "";
    if (mysqli_num_rows($q) > 0) {
        while ($n = mysqli_fetch_assoc($q)) {
            // Force strict comparative matching for the read metric status
            $isUnread = (intval($n['is_read']) === 0);
            $unreadClass = $isUnread ? 'unread' : 'read';
            
            $html .= '<div class="card ' . $unreadClass . '" id="card' . $n['id'] . '">';
            $html .= '  <div class="card-header-row">';
            $html .= '      <span class="badge badge-' . strtolower(htmlspecialchars($n['type'])) . '">' . htmlspecialchars($n['type']) . '</span>';
            $html .= '      <span class="time">🕑 ' . htmlspecialchars($n['created_at']) . '</span>';
            $html .= '  </div>';
            $html .= '  <div class="title">' . htmlspecialchars($n['title']) . '</div>';
            $html .= '  <div class="msg">' . htmlspecialchars($n['message']) . '</div>';
            
            if ($isUnread) {
                $html .= '  <div class="card-actions">';
                $html .= '      <button type="button" onclick="markRead(' . intval($n['id']) . ')" class="btn btn-secondary btn-sm">Mark as Read</button>';
                $html .= '  </div>';
            }
            $html .= '</div>';
        }
    } else {
        $html = '
        <div class="empty-state-container">
            <div class="empty-icon">🔔</div>
            <div class="empty-text">All caught up! No notifications found.</div>
        </div>';
    }
    
    return $html;
}

/* ================= CONTROLLER: MARK ALL READ VIA AJAX ================= */
if (isset($_GET['mark_all_read'])) {
    mysqli_query($conn, "
        UPDATE notifications
        SET is_read = 1
        WHERE user_id = '$user_id' OR user_id IS NULL
    ");

    if (isset($_GET['ajax_action'])) {
        header('Content-Type: application/json; charset=utf-8');
        $htmlOutput = getNotificationsHTML($conn, $user_id);
        echo json_encode([
            "success" => true,
            "count" => 0,
            "html" => $htmlOutput
        ]);
        exit;
    }

    header("Location: /quiz_system/dashboard/content/user_notification.php");
    exit;
}

/* ================= CONTROLLER: MARK SINGLE READ VIA AJAX ================= */
if (isset($_GET['mark_single_read'])) {
    $id = intval($_GET['mark_single_read']);

    mysqli_query($conn, "
        UPDATE notifications
        SET is_read = 1
        WHERE id = $id AND (user_id = '$user_id' OR user_id IS NULL)
    ");

    if (isset($_GET['ajax_action'])) {
        header('Content-Type: application/json; charset=utf-8');
        
        // Dynamic SQL reassessment of exact current balances
        $cq = mysqli_query($conn, "
            SELECT COUNT(*) as total 
            FROM notifications 
            WHERE (user_id = '$user_id' OR user_id IS NULL) AND is_read = 0
        ");
        $count = intval(mysqli_fetch_assoc($cq)['total']);
        $htmlOutput = getNotificationsHTML($conn, $user_id);

        echo json_encode([
            "success" => true,
            "count" => $count,
            "html" => $htmlOutput
        ]);
        exit;
    }

    header("Location: /quiz_system/dashboard/content/user_notification.php");
    exit;
}

/* ================= CONTROLLER: BACKGROUND STREAM SYNC ROUTE ================= */
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json; charset=utf-8');
    $cq = mysqli_query($conn, "
        SELECT COUNT(*) as total 
        FROM notifications
        WHERE (user_id = '$user_id' OR user_id IS NULL) AND is_read = 0
    ");
    $count = intval(mysqli_fetch_assoc($cq)['total']);
    $htmlOutput = getNotificationsHTML($conn, $user_id);

    echo json_encode([
        "count" => $count,
        "html" => $htmlOutput
    ]);
    exit;
}

/* ================= INITIAL SYSTEM FETCH MATRIX ================= */
$cq = mysqli_query($conn, "
    SELECT COUNT(*) as total 
    FROM notifications
    WHERE (user_id = '$user_id' OR user_id IS NULL) AND is_read = 0
");
$count = intval(mysqli_fetch_assoc($cq)['total']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications Hub - Quiz System</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        :root {
            --bg-canvas: #f8fafc;
            --surface-card: #ffffff;
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-muted: #94a3b8;
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --danger: #ef4444;
            --danger-hover: #dc2626;
            --border-color: #e2e8f0;
            --unread-tint: #f0fdf4;
            --unread-border: #22c55e;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--bg-canvas);
            color: var(--text-primary);
            margin: 0;
            padding: 0;
            line-height: 1.5;
        }

        /* DASHBOARD INTEGRATED HEADER BANNER */
        .dashboard-header {
            background: var(--surface-card);
            border-bottom: 1px solid var(--border-color);
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .header-left h3 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .pill-counter {
            background: var(--danger);
            color: #ffffff;
            font-size: 12px;
            font-weight: 700;
            padding: 2px 10px;
            border-radius: 9999px;
            box-shadow: 0 2px 4px rgba(239, 68, 68, 0.2);
        }

        /* RESPONSIVE LAYOUT WRAPPER */
        .container {
            max-width: 800px;
            margin: 32px auto;
            padding: 0 20px;
        }

        /* PREMIUM NOTIFICATION COMPONENT CARD STYLE */
        .card {
            background: var(--surface-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02), 0 1px 2px rgba(0, 0, 0, 0.04);
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
            position: relative;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
        }

        /* CONTEXTUAL STATE STYLING METRICS */
        .card.unread {
            background: var(--unread-tint);
            border-left: 4px solid var(--unread-border);
        }

        .card.read {
            opacity: 0.85;
        }

        .card-header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .title {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 6px;
        }

        .msg {
            font-size: 14px;
            color: var(--text-secondary);
            line-height: 1.6;
        }

        .time {
            font-size: 12px;
            color: var(--text-muted);
            font-weight: 500;
        }

        /* DYNAMIC CATEGORY BADGES */
        .badge {
            font-size: 11px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .badge-quiz { background: #e0f2fe; color: #0369a1; }
        .badge-system { background: #f1f5f9; color: #475569; }
        .badge-alert { background: #fee2e2; color: #b91c1c; }

        /* ACTIONS ACTION CONTROL FRAMEWORK */
        .card-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 14px;
            border-top: 1px dashed var(--border-color);
            padding-top: 12px;
        }

        .btn {
            font-family: inherit;
            font-size: 13px;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: background 0.15s ease, transform 0.1s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn:active {
            transform: scale(0.98);
        }

        .btn-primary {
            background: var(--primary);
            color: #ffffff;
        }

        .btn-primary:hover {
            background: var(--primary-hover);
        }

        .btn-secondary {
            background: transparent;
            color: var(--primary);
            border: 1px solid var(--primary);
        }

        .btn-secondary:hover {
            background: rgba(37, 99, 235, 0.05);
        }

        .btn-sm {
            padding: 5px 12px;
            font-size: 12px;
            border-radius: 6px;
        }

        /* SYSTEM LEVEL BLANK MESSAGES STATE styling */
        .empty-state-container {
            text-align: center;
            padding: 48px 24px;
            background: var(--surface-card);
            border: 1px dashed var(--text-muted);
            border-radius: 16px;
            margin: 20px 0;
        }

        .empty-icon {
            font-size: 40px;
            margin-bottom: 12px;
        }

        .empty-text {
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 15px;
        }
    </style>
</head>
<body>

<div class="dashboard-header">
    <div class="header-left">
        <h3>🔔 Notifications Hub</h3>
        <span class="pill-counter" id="count"><?= $count ?></span>
    </div>

    <div>
        <button type="button" id="markAllReadBtn" class="btn btn-primary" onclick="markAllNotificationsRead()">
            ✓ Mark All Read
        </button>
    </div>
</div>

<div class="container">
    <div id="notifBox">
        <?= getNotificationsHTML($conn, $user_id) ?>
    </div>
</div>

<script>
/**
 * Asynchronously pull fresh notification payloads from backend engine
 */
function loadNotifications() {
    $.ajax({
        url: "/quiz_system/dashboard/content/user_notification.php",
        type: "GET",
        data: { ajax: 1 },
        dataType: "json",
        success: function(res) {
            if (res.count !== undefined && res.html !== undefined) {
                updateUIElements(res.count, res.html);
            }
        },
        error: function(xhr, status, error) {
            console.warn("Background workspace notification sync layer encountered processing exception loops:", error);
        }
    });
}

/**
 * Standardize dynamic DOM asset insertions across system pipelines
 */
function updateUIElements(count, html) {
    const currentCount = parseInt($("#count").text(), 10);
    const incomingCount = parseInt(count, 10);
    
    // Only flush HTML content zones if data changes occur to prevent layout shifting
    if (currentCount !== incomingCount || $("#notifBox").find('.empty-state-container').length > 0) {
        $("#notifBox").html(html);
    }
    
    $("#count").text(incomingCount);
    
    // Toggle active state of batch modifier tools depending on balance count metric
    if (incomingCount === 0) {
        $("#markAllReadBtn").prop('disabled', true).css({ 'opacity': '0.5', 'cursor': 'not-allowed' });
    } else {
        $("#markAllReadBtn").prop('disabled', false).css({ 'opacity': '1', 'cursor': 'pointer' });
    }
}

/**
 * Perform asynchronous structural batch adjustments 
 */
function markAllNotificationsRead() {
    if (confirm("Are you sure you want to mark all notifications as read?")) {
        $.ajax({
            url: "/quiz_system/dashboard/content/user_notification.php",
            type: "GET",
            data: {
                mark_all_read: 1,
                ajax_action: 1
            },
            dataType: "json",
            success: function(res) {
                if (res.success) {
                    updateUIElements(res.count, res.html);
                }
            },
            error: function(xhr, status, error) {
                console.error("Batch mark-all-read action execution failed:", error);
            }
        });
    }
}

/**
 * Handle asynchronous mutations for single elements
 */
function markRead(id) {
    // Optimistic UI updates: Apply instantaneous transitional properties to the single element on the fly
    const targetCard = $("#card" + id);
    targetCard.removeClass("unread").addClass("read");
    targetCard.find(".card-actions").fadeOut(150);

    $.ajax({
        url: "/quiz_system/dashboard/content/user_notification.php",
        type: "GET",
        data: {
            mark_single_read: id,
            ajax_action: 1
        },
        dataType: "json",
        success: function(res) {
            if (res.success) {
                updateUIElements(res.count, res.html);
            }
        },
        error: function(xhr, status, error) {
            console.error("Single mutation event execution framework failed:", error);
            // Fallback recovery path inside sync layer if an invalid operational payload occurs
            loadNotifications();
        }
    });
}

// Instantiate view parameters directly on startup matrix initialization
$(document).ready(function() {
    const initCount = parseInt($("#count").text(), 10);
    if (initCount === 0) {
        $("#markAllReadBtn").prop('disabled', true).css({ 'opacity': '0.5', 'cursor': 'not-allowed' });
    }
    
    // Check for incoming network changes every 5 seconds
    setInterval(loadNotifications, 5000);
});
</script>

</body>
</html>