<?php
// login_api.php
require 'db.php';
header('Content-Type: application/json');

// Start a session to remember the user (crucial for later when you make the app dynamic)
session_start(); 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';

    if (empty($email) || empty($password) || empty($role)) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all fields.']);
        exit;
    }

    try {
        // Query the database for the user based on email AND the role they selected
        $stmt = $conn->prepare("SELECT id, password_hash FROM users WHERE email = ? AND role = ?");
        $stmt->bind_param("ss", $email, $role);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            // Verify the hashed password from the database
            if (password_verify($password, $row['password_hash'])) {
                
                // Store their ID in the server session for future API calls
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['role'] = $role;

                // Determine routing destination
                $redirect = ($role === 'driver') ? 'driver.php' : 'routes.php';
                
                echo json_encode(['success' => true, 'redirect' => $redirect]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Incorrect password.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Account not found for this role.']);
        }
        
        $stmt->close();

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error occurred.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>