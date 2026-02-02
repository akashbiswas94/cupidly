<?php
session_start();
if (!isset($_SESSION['admin_auth']) || $_SESSION['admin_auth'] !== true) {
    header("Location: login.php");
    exit;
}

require "db.php";

// Logout Logic
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}

// Fetch all proposals with stats
$query = "SELECT p.*, 
          (SELECT COUNT(*) FROM visitor_logs WHERE proposal_id = p.id) as total_visits,
          (SELECT visited_at FROM visitor_logs WHERE proposal_id = p.id ORDER BY visited_at DESC LIMIT 1) as last_visit
          FROM proposals p ORDER BY created_at DESC";
$proposals = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Valentine Admin Dashboard</title>
    <style>
        /* --- Base Styles --- */
body {
    font-family: 'Segoe UI', sans-serif;
    background: linear-gradient(135deg, #ffe6f0, #ffdde1, #ffc3a0);
    background-size: 400% 400%;
    animation: gradientBG 20s ease infinite;
    margin: 0;
    padding: 20px;
    color: #333;
}

/* Gradient Animation */
@keyframes gradientBG {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* Header */
.header {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    max-width: 1000px;
    margin: auto;
    padding-bottom: 15px;
}

.header h1 {
    color: #d81b60;
    font-size: 1.8rem;
    margin: 0;
}

.header a {
    text-decoration: none;
    font-weight: 600;
    margin-left: 15px;
    transition: 0.3s;
}

.header a:hover {
    color: #ff85a2;
}

/* Dashboard Container */
.dashboard {
    max-width: 1000px;
    margin: 20px auto;
    background: rgba(255, 255, 255, 0.95);
    padding: 25px;
    border-radius: 20px;
    box-shadow: 0 8px 25px rgba(216, 27, 96, 0.1);
    overflow-x: auto;
}

/* Table Styles */
table {
    width: 100%;
    border-collapse: collapse;
    min-width: 700px;
}

th, td {
    padding: 15px 12px;
    text-align: left;
    border-bottom: 1px solid #f1f1f1;
}

th {
    background: #fffafb;
    color: #d81b60;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

/* Status Badge */
.status {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: bold;
    text-transform: uppercase;
}

.status-accepted {
    background: #e8f5e9;
    color: #2e7d32;
}

.status-pending {
    background: #fff3e0;
    color: #ef6c00;
}

/* Links / Buttons */
.btn {
    color: #ff4d88;
    text-decoration: none;
    font-weight: 600;
    transition: 0.3s;
}

.btn:hover {
    color: #d81b60;
}

/* Logout Button */
.logout-btn {
    color: #888;
    font-size: 0.85rem;
    text-decoration: none;
    transition: 0.3s;
}

.logout-btn:hover {
    color: #d81b60;
}

/* Responsive */
@media (max-width: 768px) {
    .header {
        flex-direction: column;
        align-items: flex-start;
    }
    .header a {
        margin: 5px 0 0 0;
    }
    table {
        font-size: 0.9rem;
    }
}

@media (max-width: 480px) {
    .dashboard {
        padding: 15px;
    }
    th, td {
        padding: 10px 8px;
    }
    .header h1 {
        font-size: 1.5rem;
    }
}

    </style>
</head>
<body>

<div class="header">
    <h1 style="color: #d81b60;">Yesly- Intelligence</h1>
    <a href="master_logs.php" style="margin-right: 20px; color: #ff4d88; font-weight: bold; text-decoration: none;">🌐 Global Feed</a>
    <a href="?logout=1" class="logout-btn">Logout 🚪</a>
</div>

<div class="dashboard">
    <table>
        <thead>
            <tr>
                <th>Target (Receiver)</th>
                <th>Sender</th>
                <th>Status</th>
                <th>Visits</th>
                <th>Last Interaction</th>
                <th>Intel</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $proposals->fetch_assoc()): ?>
            <tr>
                <td><strong><?= htmlspecialchars($row['receiver_name']) ?></strong></td>
                <td><?= htmlspecialchars($row['sender_name']) ?></td>
                <td>
                    <span class="status status-<?= $row['status'] ?>">
                        <?= $row['status'] ?>
                    </span>
                </td>
                <td><b style="color: #ff4d88;"><?= $row['total_visits'] ?></b></td>
                <td><?= $row['last_visit'] ? date("M j, H:i", strtotime($row['last_visit'])) : "---" ?></td>
                <td><a href="view_logs.php?id=<?= $row['id'] ?>" class="btn">View Logs 📂</a></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>