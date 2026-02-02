<?php
session_start();

// Security Check
if (!isset($_SESSION['admin_auth']) || $_SESSION['admin_auth'] !== true) {
    header("Location: login.php");
    exit;
}

require "db.php";

// Fetch all logs from all proposals, joining with proposals to get names
// We use the same query structure to maintain compatibility with your table
$query = "SELECT v.*, p.sender_name, p.receiver_name, p.public_code 
          FROM visitor_logs v
          JOIN proposals p ON v.proposal_id = p.id
          ORDER BY v.visited_at DESC 
          LIMIT 100"; 
$logs = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="icon.ico">
    <title>Global Traffic Feed 📡</title>
    <style>
        body {
    font-family: 'Segoe UI', sans-serif;
    background: #f0f2f5;
    margin: 0;
    padding: 20px;
    color: #444;
}

.container {
    max-width: 1100px;
    margin: auto;
}

.nav {
    margin-bottom: 20px;
}

.nav a {
    color: #ff4d88;
    text-decoration: none;
    font-weight: bold;
    margin-right: 15px;
}

h1 {
    color: #333;
    margin-bottom: 25px;
    font-size: 1.6rem;
}

.live-indicator {
    height: 10px;
    width: 10px;
    background: #4CAF50;
    border-radius: 50%;
    display: inline-block;
    margin-right: 5px;
    animation: blink 2s infinite;
}

@keyframes blink {
    0% { opacity: 1; }
    50% { opacity: 0.3; }
    100% { opacity: 1; }
}

.log-table {
    width: 100%;
    background: white;
    border-collapse: collapse;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    table-layout: fixed; /* important for responsive truncation */
}

th, td {
    padding: 12px 10px;
    text-align: left;
    border-bottom: 1px solid #f0f0f0;
    word-wrap: break-word; /* ensures long text breaks properly */
}

th {
    background: #ff4d88;
    color: white;
    font-size: 13px;
    text-transform: uppercase;
}

td .device-tag {
    display: inline-block;
    padding: 3px 6px;
    background: #eee;
    border-radius: 4px;
    font-size: 11px;
    color: #666;
    max-width: 120px; /* restrict width for long device strings */
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

td .visit-info {
    max-width: 180px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.target-name {
    color: #d81b60;
    font-weight: bold;
}

.timestamp {
    color: #888;
    font-size: 12px;
    white-space: nowrap;
}

tr:hover {
    background-color: #fff9fa;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .log-table, .log-table thead, .log-table tbody, .log-table th, .log-table td, .log-table tr {
        display: block;
        width: 100%;
    }

    .log-table thead {
        display: none; /* hide headers on mobile */
    }

    .log-table tr {
        margin-bottom: 15px;
        background: #fff;
        border-radius: 10px;
        padding: 12px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.03);
    }

    .log-table td {
        display: flex;
        justify-content: space-between;
        padding: 6px 10px;
        border: none;
        font-size: 13px;
    }

    .log-table td::before {
        content: attr(data-label);
        font-weight: bold;
        color: #555;
        flex: 1;
    }

    td .device-tag {
        max-width: 100px;
        font-size: 10px;
    }

    td .visit-info {
        max-width: 120px;
        font-size: 12px;
    }

    .timestamp {
        font-size: 11px;
    }
}

    </style>
</head>
<body>

<div class="container">
    <div class="nav">
        <a href="admin.php">← Back to Dashboard</a>
        <a href="master_logs.php">🔄 Refresh Feed</a>
    </div>

    <h1><span class="live-indicator"></span> Global Visitor Feed</h1>

    <table class="log-table">
        <thead>
            <tr>
                <th>Time</th>
                <th>Who was Visited?</th>
                <th>Location</th>
                <th>Device</th>
                <th>IP Address</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($logs && $logs->num_rows > 0): ?>
                <?php while($row = $logs->fetch_assoc()): ?>
                <tr>
                    <td class="timestamp">
                        <?= date("M j, g:i:s a", strtotime($row['visited_at'])) ?>
                    </td>
                    <td>
                        <span class="target-name"><?= htmlspecialchars($row['receiver_name']) ?></span>
                        <br><small style="color:#aaa">Sent by <?= htmlspecialchars($row['sender_name']) ?></small>
                    </td>
                    <td class="visit-info">
                        📍 <?= htmlspecialchars($row['visitor_location']) ?>
                    </td>
                    <td>
                        <span class="device-tag"><?= htmlspecialchars($row['visitor_device']) ?></span>
                    </td>
                    <td class="timestamp">
                        <?= htmlspecialchars($row['visitor_ip']) ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align:center; padding: 40px; color: #999;">No visits logged yet. Link sharing hasn't started!</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>