<?php
include('../database/db.php');
session_start();

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    $email = trim($_POST['email']);
    $first = trim($_POST['first_name']);
    $last = trim($_POST['last_name']);

    if ($password !== $confirm) {
        $message = "❌ Passwords do not match.";
    } else {
        $stmt = $conn->prepare("SELECT PersonID FROM Users WHERE Username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $message = "⚠️ Username already exists.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO Users (Username, Pass, Email, First_Name, Last_Name) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $username, $hashedPassword, $email, $first, $last);

            if ($stmt->execute()) {
                $message = "✅ Registration successful! Redirecting...";
                header("Refresh:2; url=login.php");
            } else {
                $message = "❌ Something went wrong.";
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Register - E-Supermarket</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="login-card">
    <h2>🛒 E-Supermarket Register</h2>
    <form method="POST" action="">
        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="first_name" placeholder="First Name" required>
        <input type="text" name="last_name" placeholder="Last Name" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        <button type="submit">Sign Up</button>
    </form>
    <?php if($message) echo '<p class="message">'.$message.'</p>'; ?>
    <div class="register-link">
        Already have an account? <a href="login.php">Login here</a>
    </div>
</div>

</body>
</html>