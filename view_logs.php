<?php
session_start();

// --- 1. SECURITY CHECK ---
if (!isset($_SESSION['admin_auth']) || $_SESSION['admin_auth'] !== true) {
    header("Location: login.php");
    exit;
}

require "db.php";

$proposal_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// --- 2. FETCH PROPOSAL HEADER ---
$p_stmt = $conn->prepare("SELECT sender_name, receiver_name, created_at FROM proposals WHERE id = ?");
$p_stmt->bind_param("i", $proposal_id);
$p_stmt->execute();
$proposal = $p_stmt->get_result()->fetch_assoc();

if (!$proposal) {
    die("Error: This proposal file does not exist in the Love Lab records.");
}

// --- 3. FETCH VISITOR HISTORY ---
$l_stmt = $conn->prepare("SELECT * FROM visitor_logs WHERE proposal_id = ? ORDER BY visited_at DESC");
$l_stmt->bind_param("i", $proposal_id);
$l_stmt->execute();
$logs = $l_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intelligence Report | <?= htmlspecialchars($proposal['receiver_name']) ?></title>
    <style>
        :root {
    --pink: #ff4d88;
    --dark-pink: #d81b60;
    --bg: #fdf2f5;
    --card-bg: #fff;
    --shadow-color: rgba(216, 27, 96, 0.05);
}

body {
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    background-color: var(--bg);
    margin: 0;
    padding: 20px;
    color: #444;
}

.report-wrapper {
    max-width: 800px;
    margin: 20px auto;
}

/* Back button */
.back-btn {
    display: inline-block;
    text-decoration: none;
    color: var(--pink);
    font-weight: 600;
    margin-bottom: 15px;
    font-size: 14px;
    transition: 0.3s;
}

.back-btn:hover {
    color: var(--dark-pink);
}

/* Proposal Header */
.header-card {
    background: var(--card-bg);
    padding: 30px;
    border-radius: 20px;
    box-shadow: 0 4px 15px var(--shadow-color);
    margin-bottom: 20px;
    border-left: 8px solid var(--pink);
}

.header-card h1 {
    margin: 0;
    color: var(--dark-pink);
    font-size: 24px;
}

.header-card .subtitle {
    color: #888;
    font-size: 14px;
    margin-top: 5px;
}

/* Visitor Logs */
.log-entry {
    background: var(--card-bg);
    padding: 20px;
    border-radius: 15px;
    margin-bottom: 12px;
    display: grid;
    grid-template-columns: 1fr 2fr 1.5fr;
    align-items: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.02);
    border: 1px solid rgba(255, 77, 136, 0.1);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.log-entry:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(216, 27, 96, 0.1);
}

.time-stamp {
    font-weight: bold;
    color: #555;
    font-size: 14px;
}

.location {
    color: var(--dark-pink);
    font-weight: 500;
}

.device {
    color: #777;
    font-size: 13px;
    text-align: right;
}

.ip {
    display: block;
    font-size: 11px;
    color: #bbb;
    margin-top: 4px;
    font-style: italic;
}

/* No data message */
.no-data {
    text-align: center;
    padding: 50px;
    color: #999;
    background: var(--card-bg);
    border-radius: 20px;
    box-shadow: 0 2px 8px var(--shadow-color);
}

/* Responsive */
@media (max-width: 600px) {
    .log-entry {
        grid-template-columns: 1fr;
        gap: 10px;
        text-align: left;
    }
    .device {
    color: #777;
    font-size: 13px;
    text-align: left;
    max-width: 100%;
    word-break: break-word; /* ensures long words break to next line */
    overflow: hidden;
    }

    .device span.user-agent {
        display: block;
        font-size: 10px;
        color: #ccc;
        text-overflow: ellipsis;
        overflow: hidden;
        white-space: nowrap; /* keep on one line */
        max-width: 100%;
    }

    .header-card {
        padding: 20px;
    }
}

    </style>
</head>
<body>

<div class="report-wrapper">
    <a href="admin.php" class="back-btn">← Back to Master Dashboard</a>

    <div class="header-card">
        <h1>Intelligence Report: <?= htmlspecialchars($proposal['receiver_name']) ?></h1>
        <p class="subtitle">
            Proposal sent by <strong><?= htmlspecialchars($proposal['sender_name']) ?></strong> 
            on <?= date("M j, Y", strtotime($proposal['created_at'])) ?>
        </p>
    </div>

    <?php if ($logs->num_rows === 0): ?>
        <div class="no-data">
            <p>No activity detected yet. The subject hasn't opened the link.</p>
        </div>
    <?php else: ?>
        <?php while($log = $logs->fetch_assoc()): ?>
            <div class="log-entry">
                <div class="time-stamp">
                    <?= date("H:i:s", strtotime($log['visited_at'])) ?>
                    <span style="display:block; font-size:11px; color:#aaa; font-weight:normal;">
                        <?= date("M j, Y", strtotime($log['visited_at'])) ?>
                    </span>
                </div>
                
                <div class="location">
                    📍 <?= htmlspecialchars($log['visitor_location']) ?>
                    <span class="ip">IP: <?= htmlspecialchars($log['visitor_ip']) ?></span>
                </div>

                <div class="device">
                    <span><?= htmlspecialchars($log['visitor_device']) ?></span>
                    <span class="user-agent" title="<?= htmlspecialchars($log['user_agent']) ?>">
                        <?= htmlspecialchars($log['user_agent']) ?>
                    </span>
                </div>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

</body>
</html>