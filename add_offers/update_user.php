<?php
session_start();
include '../database/db.php';

if (!isset($_SESSION['Username'])) {
    http_response_code(403);
    echo json_encode(["error" => "User not logged in."]);
    exit();
}

$username = $_SESSION['Username'];

$stmt = $conn->prepare("SELECT PersonID FROM Users WHERE Username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    http_response_code(404);
    echo json_encode(["error" => "User not found."]);
    exit();
}

$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['newUsername'])) {
        $newUsername = $conn->real_escape_string($_POST['newUsername']);
        $stmt = $conn->prepare("UPDATE Users SET Username = ? WHERE PersonID = ?");
        $stmt->bind_param("si", $newUsername, $user['PersonID']);
        if ($stmt->execute()) {
            $_SESSION['Username'] = $newUsername;
            $response['username'] = "Username updated successfully!";
        } else {
            $response['username'] = "Error updating username.";
        }
        $stmt->close();
    }

    if (isset($_POST['newPassword'])) {
        $newPassword = $_POST['newPassword'];

        if (preg_match('/[A-Z]/', $newPassword) &&
            preg_match('/[0-9]/', $newPassword) &&
            preg_match('/[\W]/', $newPassword)) {

            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE Users SET Pass = ? WHERE PersonID = ?");
            $stmt->bind_param("si", $hashedPassword, $user['PersonID']);
            if ($stmt->execute()) {
                $response['password'] = "Password updated successfully!";
            } else {
                $response['password'] = "Error updating password.";
            }
            $stmt->close();
        } else {
            $response['password'] = "Password does not meet the requirements.";
        }
    }

    echo json_encode($response);
}
?>
