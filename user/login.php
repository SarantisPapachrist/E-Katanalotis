<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('../database/db.php');
session_start();

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['username']);
    $pass = $_POST['password'];

    $sql = "SELECT * FROM Users WHERE Username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();

        
        if ($pass === $row['Pass'] || password_verify($pass, $row['Pass'])) {

            $_SESSION['PersonID'] = $row['PersonID'];
            $_SESSION['Username'] = $row['Username'];
            $_SESSION['Email'] = $row['Email'];
            $_SESSION['First_Name'] = $row['First_Name'];
            $_SESSION['Last_Name'] = $row['Last_Name'];
            $_SESSION['score'] = $row['score'];
            $_SESSION['tokens'] = $row['tokens'];
            $_SESSION['total_score'] = $row['total_score'];
            $_SESSION['total_tokens'] = $row['total_tokens'];
            $_SESSION['admin'] = $row['adminstrator'];

            if ($row['adminstrator'] === 'admin') {
                header("Location: ../admin/admin.php");
            } else {
                header("Location: ../add_offers/home.php");
            }
            exit();

        } else {
            $message = "❌ Invalid password.";
        }
    } else {
        $message = "❌ User not found.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login - E-Supermarket</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="login-card">
    <h2>🛒 E-Katanalotis Login</h2>
    <form method="POST" action="">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
    <?php if($message): ?>
        <p class="message"><?= $message ?></p>
    <?php endif; ?>
    <div class="register-link">
        Don't have an account? <a href="register.php">Register here</a>
    </div>
</div>

</body>
</html>