<?php
// db.php
if ($_SERVER['SERVER_NAME'] === 'localhost') {
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db   = "valentine";
} else {
    $host = "localhost";
    $user = "yeslyonl_valentine";
    $pass = "XWkxJuyB93FMPfEUKeA8";
    $db   = "yeslyonl_valentine";
}

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    // If this is an AJAX request, return JSON. Otherwise, die.
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' || isset($_POST['answer'])) {
        header('Content-Type: application/json');
        echo json_encode(["success" => false, "error" => "Database connection failed"]);
        exit;
    }
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");