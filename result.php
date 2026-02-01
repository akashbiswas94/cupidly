<?php
require "db.php";
$code = $_GET["c"] ?? "";

$stmt = $conn->prepare("SELECT * FROM proposals WHERE private_code=?");
$stmt->bind_param("s",$code);
$stmt->execute();
$res = $stmt->get_result();
$p = $res->fetch_assoc();

if(!$p) die("Invalid link");

// Funny commentary logic
$commentary = "Generating the courage to send it...";
if ($p["status"] == "accepted") {
    $commentary = "MISSION ACCOMPLISHED. Retreat from the friendzone immediately! 🥂";
} elseif ($p["responded_at"]) {
    $commentary = "Well, at least you tried. Time for ice cream? 🍦";
} elseif ($p["viewed_at"]) {
    $commentary = "THEY ARE LOOKING AT IT RIGHT NOW. DON'T PANIC. BREATHE. 😱";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Love Lab 🧪</title>
    <link href="https://fonts.googleapis.com/css2?family=Handlee&family=Quicksand:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
    background-color: #fdf0f0;
    background-image: radial-gradient(#ffc1cc 0.5px, transparent 0.5px);
    background-size: 20px 20px;
    font-family: 'Quicksand', sans-serif;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    margin: 0;
    padding: 10px;
}

.notebook {
    background: #fff;
    width: 100%;
    max-width: 400px;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 10px 10px 0px #ffb3c1;
    border: 2px solid #555;
    position: relative;
    transition: transform 0.3s ease;
}

.notebook:hover {
    transform: translateY(-5px) scale(1.02);
}

/* Red margin line */
.notebook::before {
    content: '';
    position: absolute;
    top: 0;
    left: 40px;
    width: 2px;
    height: 100%;
    background: rgba(255, 0, 0, 0.1);
}

h2 {
    font-family: 'Handlee', cursive;
    color: #d1495b;
    font-size: clamp(1.5rem, 6vw, 2rem);
    margin-top: 0;
    text-align: center;
}

.log-entry {
    margin-bottom: 15px;
    padding-left: 25px;
}

.label {
    font-size: 0.75rem;
    font-weight: bold;
    color: #999;
    text-transform: uppercase;
    display: block;
}

.value {
    font-size: clamp(0.9rem, 4vw, 1.1rem);
    color: #333;
    word-wrap: break-word;
}

.pulse-box {
    background: #fff0f3;
    border-left: 5px solid #ff4d88;
    padding: 15px;
    margin: 20px 0;
    font-style: italic;
    color: #d1495b;
    font-size: clamp(0.85rem, 3.5vw, 1rem);
}

.stamp {
    position: absolute;
    right: 15px;
    top: 15px;
    transform: rotate(15deg);
    border: 3px solid #ff4d88;
    color: #ff4d88;
    padding: 5px 10px;
    font-weight: bold;
    border-radius: 5px;
    font-family: sans-serif;
    opacity: 0.8;
    text-transform: uppercase;
    font-size: clamp(0.7rem, 3vw, 0.9rem);
}

.accepted-stamp {
    border-color: #4CAF50;
    color: #4CAF50;
}

button {
    background: #555;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    font-family: 'Quicksand', sans-serif;
    width: 100%;
    font-size: clamp(0.9rem, 4vw, 1rem);
    transition: background 0.3s ease, transform 0.2s ease;
}

button:hover {
    background: #333;
    transform: translateY(-2px);
}

.coffee-stain {
    position: absolute;
    bottom: -20px;
    right: -20px;
    width: 80px;
    opacity: 0.08;
    pointer-events: none;
}

/* Responsive adjustments */
@media (max-width: 480px) {
    .notebook {
        padding: 20px;
        border-radius: 12px;
        box-shadow: 6px 6px 0px #ffb3c1;
    }

    .stamp {
        top: 10px;
        right: 10px;
    }

    .pulse-box {
        padding: 12px;
        margin: 15px 0;
    }
}
    </style>
</head>
<body>

<div class="notebook">
    <div class="stamp <?= $p["status"] == 'accepted' ? 'accepted-stamp' : '' ?>">
        <?= $p["status"] == 'accepted' ? 'Success!' : 'Top Secret' ?>
    </div>

    <h2>Top Secret Intel 🕵️‍♂️</h2>
    
    <div class="log-entry">
        <span class="label">Target Subject:</span>
        <span class="value"><?= htmlspecialchars($p["receiver_name"]) ?></span>
    </div>

    <div class="log-entry">
        <span class="label">Last Seen:</span>
        <span class="value">
            <?= $p["viewed_at"] ? "Peeked at it on " . date("H:i", strtotime($p["viewed_at"])) : "Hasn't opened the envelope yet." ?>
        </span>
    </div>

    <div class="pulse-box">
        <strong>Relationship Pulse:</strong><br>
        "<?= $commentary ?>"
    </div>

    <?php if($p["status"] == "accepted"): ?>
        <div style="text-align:center; font-size: 3rem;">🥳</div>
        <p style="text-align:center; color:#4CAF50; font-weight:bold;">WE ARE IN! Clear your schedule for a date!</p>
    <?php endif; ?>

    <button onclick="location.reload()">Refresh Intelligence</button>
    
    <p style="font-size: 0.7rem; color: #ccc; margin-top: 15px; text-align: center;">
        Hand-crafted with love and extreme anxiety.
    </p>
</div>

</body>
</html>