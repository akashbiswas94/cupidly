<?php
require "db.php";

function generateCode($len) {
    return substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, $len);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $sender   = trim($_POST["sender"]);
    $receiver = trim($_POST["receiver"]);

    $public  = generateCode(8);
    $private = generateCode(12);

    $stmt = $conn->prepare("INSERT INTO proposals (sender_name, receiver_name, public_code, private_code) VALUES (?,?,?,?)");
    $stmt->bind_param("ssss", $sender, $receiver, $public, $private);
    $stmt->execute();

    $valentine = "http://localhost/valentine/v.php?c=$public";
    $result    = "http://localhost/valentine/result.php?c=$private";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Your Valentine 💖</title>
    <style>
        :root {
    --primary: #ff4d88;
    --secondary: #ff85a2;
    --bg: #fff0f3;
    --card-bg: rgba(255, 255, 255, 0.9);
}

* {
    box-sizing: border-box;
}

body, html {
    margin: 0;
    padding: 0;
    width: 100%;
    min-height: 100vh;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    /* Use a fixed background or linear gradient that covers everything */
    background: linear-gradient(135deg, #fce4ec 0%, #ffe1e9 100%);
    display: flex;
    justify-content: center;
    align-items: center;
    overflow-x: hidden; /* Prevent horizontal scrolling */
}

/* Background Hearts - Hidden on very small heights to save space */
.hearts {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
    z-index: -1;
}

.hearts li {
    position: absolute;
    list-style: none;
    display: block;
    background: rgba(255, 77, 136, 0.2);
    animation: animate 25s linear infinite;
    bottom: -150px;
    border-radius: 50%;
}

@keyframes animate { 
    0% { transform: translateY(0) rotate(0deg); opacity: 1; }
    100% { transform: translateY(-1000px) rotate(720deg); opacity: 0; }
}

/* The Main Responsive Container */
.container {
    background: var(--card-bg);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    
    /* Width logic: 90% of screen on mobile, max 400px on desktop */
    width: 92%;
    max-width: 400px;
    
    padding: clamp(20px, 5vw, 40px);
    border-radius: 24px;
    box-shadow: 0 15px 35px rgba(255, 77, 136, 0.15);
    text-align: center;
    border: 2px solid #fff;
    margin: 20px 0; /* Ensures it doesn't touch screen edges when scrolling */
}

h2 {
    color: #d81b60;
    margin-bottom: 15px;
    /* Fluid font size: Minimum 22px, scales with screen, max 28px */
    font-size: clamp(22px, 6vw, 28px);
}

p {
    color: #666;
    font-size: 14px;
    line-height: 1.5;
    margin-bottom: 25px;
}

.input-group {
    margin-bottom: 20px;
    text-align: left;
}

label {
    display: block;
    margin-bottom: 8px;
    color: var(--primary);
    font-weight: bold;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

input {
    width: 100%;
    padding: 14px 16px;
    border: 2px solid #ffdae0;
    border-radius: 14px;
    outline: none;
    transition: all 0.3s ease;
    font-size: 16px; /* Prevents auto-zoom on iPhone */
    background: #fff;
}

input:focus {
    border-color: var(--primary);
    box-shadow: 0 0 10px rgba(255, 77, 136, 0.1);
}

button {
    background: linear-gradient(to right, #ff4d88, #ff85a2);
    color: white;
    border: none;
    padding: 16px;
    border-radius: 14px;
    width: 100%;
    font-size: 18px;
    font-weight: bold;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
    margin-top: 5px;
}

/* Active touch feedback for mobile */
button:active {
    transform: scale(0.98);
}

button:hover {
    box-shadow: 0 8px 20px rgba(255, 77, 136, 0.3);
}

/* Responsive Result Boxes */
.result-box {
    background: #fff5f7;
    padding: 15px;
    border-radius: 14px;
    margin-top: 15px;
    text-align: left;
    border: 1px dashed #ff4d88;
    word-break: break-all; /* Critical for long URLs on mobile */
}

.result-box a {
    color: #ff4d88;
    text-decoration: none;
    font-weight: bold;
    font-size: 13px;
    display: inline-block;
    margin-top: 5px;
}

/* Tablet and larger tweaks */
@media (min-width: 768px) {
    .container {
        box-shadow: 0 25px 50px rgba(255, 77, 136, 0.2);
    }
}

/* Landscape/Short Screen Fix */
@media (max-height: 500px) {
    body {
        align-items: flex-start;
        padding: 20px 0;
    }
    .hearts { display: none; }
}
    </style>
</head>
<body>
    <ul class="hearts">
        <li style="left: 25%; width: 80px; height: 80px; animation-delay: 0s;"></li>
        <li style="left: 10%; width: 20px; height: 20px; animation-delay: 2s; animation-duration: 12s;"></li>
        <li style="left: 70%; width: 20px; height: 20px; animation-delay: 4s;"></li>
        <li style="left: 40%; width: 60px; height: 60px; animation-delay: 0s; animation-duration: 18s;"></li>
    </ul>

    <div class="container">
        <?php if(!isset($valentine)): ?>
            <h2>Create a Proposal 💌</h2>
            <p>Enter your details to generate a magical link for your special someone.</p>
            <form method="post">
                <div class="input-group">
                    <label>Your Name</label>
                    <input name="sender" placeholder="e.g. Romeo" required>
                </div>
                <div class="input-group">
                    <label>Their Name</label>
                    <input name="receiver" placeholder="e.g. Juliet" required>
                </div>
                <button type="submit">Generate Magic ✨</button>
            </form>
        <?php else: ?>
            <div style="font-size: 50px;">💖</div>
            <h2>It's Ready!</h2>
            
            <div class="result-box">
                <label style="display:block; margin-bottom:5px;">SHARE THIS WITH THEM:</label>
                <a href="<?= $valentine ?>" target="_blank" style="font-size: 14px;"><?= $valentine ?></a>
            </div>

            <div class="result-box" style="border-color: #4CAF50; background: #f0fff4;">
                <label style="color: #4CAF50; display:block; margin-bottom:5px;">YOUR TRACKING LINK:</label>
                <a href="<?= $result ?>" target="_blank" style="color: #4CAF50; font-size: 14px;"><?= $result ?></a>
            </div>
            
            <button onclick="window.location.href='index.php'" style="background: #ccc; margin-top: 20px; font-size: 14px; padding: 10px;">Create Another</button>
        <?php endif; ?>
    </div>
</body>
</html>