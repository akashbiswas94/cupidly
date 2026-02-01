<?php
session_start();

// --- CONFIGURATION ---
$admin_user = "admin";
$admin_pass = password_hash("Cupid2026", PASSWORD_DEFAULT);// Change this to your preferred password

if (isset($_POST['login'])) {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    if ($user === $admin_user && password_verify($pass, $admin_pass)) {
        $_SESSION['admin_auth'] = true;
        header("Location: admin.php");
        exit;
    } else {
        $error = "Invalid credentials, Detective. 🕵️‍♂️";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login | Love Lab</title>
    <style>
        /* Base styles */
body {
    font-family: 'Segoe UI', sans-serif;
    background: linear-gradient(135deg, #ffdde1, #ffc3a0, #ffafbd, #ee9ca7);
    background-size: 400% 400%;
    animation: gradientBG 15s ease infinite;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
}

/* Gradient animation */
@keyframes gradientBG {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* Login card */
.login-card {
    background: rgba(255, 255, 255, 0.95);
    padding: 40px;
    border-radius: 25px;
    box-shadow: 0 10px 30px rgba(216, 27, 96, 0.2);
    width: 100%;
    max-width: 360px;
    text-align: center;
    position: relative;
    overflow: hidden;
    animation: floatCard 2s ease infinite alternate;
}

/* Floating effect for card */
@keyframes floatCard {
    0% { transform: translateY(0); }
    100% { transform: translateY(-5px); }
}

/* Heading */
h2 {
    color: #d81b60;
    margin-bottom: 20px;
    font-size: clamp(1.5rem, 5vw, 2rem);
}

/* Input fields */
input {
    width: 100%;
    padding: 12px;
    margin: 12px 0;
    border: 1px solid #ddd;
    border-radius: 10px;
    box-sizing: border-box;
    font-size: 1rem;
    transition: 0.3s;
}

input:focus {
    border-color: #ff4d88;
    outline: none;
    box-shadow: 0 0 10px rgba(255, 77, 136, 0.3);
}

/* Submit button */
button {
    width: 100%;
    padding: 12px;
    background: linear-gradient(135deg, #ff4d88, #ff85a2);
    color: white;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    font-weight: bold;
    font-size: 1rem;
    transition: transform 0.2s, box-shadow 0.2s;
}

button:hover {
    transform: translateY(-2px) scale(1.02);
    box-shadow: 0 8px 20px rgba(255, 77, 136, 0.5);
}

/* Error message */
.error {
    color: #d81b60;
    font-size: 0.85rem;
    margin-bottom: 10px;
    text-align: left;
}

/* Heart particles (optional fun effect) */
.login-card::after {
    content: '💖';
    position: absolute;
    top: -10px;
    right: -10px;
    font-size: 3rem;
    opacity: 0.3;
    animation: heartPulse 2s infinite alternate;
}

@keyframes heartPulse {
    0% { transform: scale(1) rotate(0deg); }
    50% { transform: scale(1.2) rotate(10deg); }
    100% { transform: scale(1) rotate(0deg); }
}

/* Responsive */
@media (max-width: 480px) {
    .login-card {
        padding: 25px;
        border-radius: 20px;
    }
    h2 { font-size: 1.5rem; }
    input, button { font-size: 0.95rem; padding: 10px; }
}

    </style>
</head>
<body>
    <div class="login-card">
        <h2>Admin Access 🔒</h2>
        <?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login">Enter Dashboard</button>
        </form>
    </div>
</body>
</html>