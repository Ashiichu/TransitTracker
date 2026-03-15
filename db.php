<?php
// db.php
$servername = "sql100.infinityfree.com";
$username = "if0_40964251";
$password = "Z1vQOOVAj4UGee";
$dbname = "if0_40964251_intellibus";

// Enable mysqli exceptions for strict error handling
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed.']));
}
?>