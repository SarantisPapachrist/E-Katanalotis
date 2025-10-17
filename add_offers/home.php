<?php
session_start();

if (!isset($_SESSION['Username'])) {
    header("Location: ../user/login.php");
    exit();
}

include '../database/db.php'; 

$username = $_SESSION['Username'];


$stmt = $conn->prepare("SELECT * FROM Users WHERE Username = ? LIMIT 1");
$stmt->bind_param("s", $username); // "s" = string
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("User not found.");
}
$stmt->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Home - E-Katanalotis</title>

<link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="../css/home_style.css">

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />

</head>
<body>

<div class="navbar">
    <div class="welcome">
        🛒 Welcome, <?php echo htmlspecialchars($_SESSION['Username']); ?>!
    </div>
    <div class="nav-buttons">
    <button id="open-profile">My Profile</button>
    <form action="../user/logout.php" method="post" style="display:inline;">
        <button type="submit">Logout</button>
    </form>
</div>
</div>

<div id="offer-panel">
  <h3>Add Offer for <span id="shop-name-display">Shop</span></h3>

  <label>Category:</label>
  <select id="offer-category"></select>

  <label>Subcategory:</label>
  <select id="offer-subcategory"></select>

  <label>Product:</label>
  <select id="offer-product"></select>

  <label>Price (€):</label>
  <input type="number" id="offer-price" step="0.01" min="0">

  <button id="submit-offer">Submit Offer</button>
  <button id="close-offer">Close</button>

  <p id="offer-message" style="display:none; margin-top:10px;"></p>
</div>

<div id="rate-panel" style="display:none;">
  <h3>Rate Offers for <span id="rate-shop-name">Shop</span></h3>
  <div id="offers-container"></div>
  <button id="close-rate">Close</button>
</div>

<div class="profile-panel" id="panel1">
    <h3>User Information</h3>
    <p><strong>Username:</strong> <?php echo htmlspecialchars($user['Username']); ?></p>
    <p><strong>First Name:</strong> <?php echo htmlspecialchars($user['First_Name']); ?></p>
    <p><strong>Last Name:</strong> <?php echo htmlspecialchars($user['Last_Name']); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['Email']); ?></p>
    <p><strong>Score:</strong> <?php echo htmlspecialchars($user['score']); ?></p>
    <p><strong>Total Score:</strong> <?php echo htmlspecialchars($user['total_score']); ?></p>
    <p><strong>Tokens:</strong> <?php echo htmlspecialchars($user['tokens']); ?></p>
    <p><strong>Total Tokens:</strong> <?php echo htmlspecialchars($user['total_tokens']); ?></p>
    <p><strong>Role:</strong> <?php echo htmlspecialchars($user['adminstrator']); ?></p>
</div>

<div class="profile-panel" id="panel2">
    <h3>Update Account</h3>

    <form id="usernameForm">
        <label for="newUsername">New Username:</label>
        <input type="text" id="newUsername" name="newUsername" required>
        <button type="submit">Update Username</button>
    </form>
    <p id="usernameMessage" style="color: green;"></p>

    <hr>

    <form id="passwordForm">
        <label id="newPassword" for="newPasswordInput">New Password</label>
        <input type="password" id="newPasswordInput" name="newPassword" required>
        <button type="submit">Update Password</button>
    </form>
    <p id="passwordMessage" style="color: green;"></p>
</div>


<div class="profile-panel" id="panel3">
    <h3>My Offers</h3>
    <div id="offersContainer" style="overflow-y: auto; max-height: 350px;">
        <?php
        $stmt = $conn->prepare("SELECT * FROM Offers WHERE Pusername = ? ORDER BY created_at DESC");
        $stmt->bind_param("s", $_SESSION['Username']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($offer = $result->fetch_assoc()) {
                echo '<div class="user-offer">';
                echo '<p><strong>Product:</strong> ' . htmlspecialchars($offer['Product']) . '</p>';
                echo '<p><strong>Category:</strong> ' . htmlspecialchars($offer['Category']) . ' / ' . htmlspecialchars($offer['SubCategory']) . '</p>';
                echo '<p><strong>Price:</strong> €' . htmlspecialchars($offer['Price']) . '</p>';
                echo '<p><strong>Shop:</strong> ' . htmlspecialchars($offer['shop_name']) . '</p>';
                echo '<p><strong>Likes:</strong> ' . htmlspecialchars($offer['Likes']) . ' | <strong>Dislikes:</strong> ' . htmlspecialchars($offer['Dislikes']) . '</p>';
                echo '<p><strong>Apothema:</strong> ' . ($offer['Apothema'] ? 'Yes' : 'No') . '</p>';
                echo '<hr>';
                echo '</div>';
            }
        } else {
            echo '<p>No offers found.</p>';
        }

        $stmt->close();
        ?>
    </div>
</div>

<div class="profile-panel" id="panel4">
    <h3>My Likes & Dislikes</h3>
    <div id="likesContainer" style="overflow-y: auto; max-height: 350px;">
        <?php
        $stmt = $conn->prepare(
            "SELECT ld.Click1, o.Product, o.shop_name, o.Category, o.SubCategory
             FROM LikesDislikes ld
             JOIN Offers o ON ld.offer_id = o.offer_id
             WHERE ld.person_id = ? 
             ORDER BY o.created_at DESC"
        );
        $stmt->bind_param("i", $user['PersonID']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($entry = $result->fetch_assoc()) {
                echo '<div class="user-like">';
                echo '<p><strong>Product:</strong> ' . htmlspecialchars($entry['Product']) . '</p>';
                echo '<p><strong>Shop:</strong> ' . htmlspecialchars($entry['shop_name']) . '</p>';
                echo '<p><strong>Category:</strong> ' . htmlspecialchars($entry['Category']) . ' / ' . htmlspecialchars($entry['SubCategory']) . '</p>';
                echo '<p><strong>Action:</strong> ' . ucfirst($entry['Click1']) . '</p>';
                echo '<hr>';
                echo '</div>';
            }
        } else {
            echo '<p>No likes or dislikes found.</p>';
        }

        $stmt->close();
        ?>
    </div>
</div>



<div id="message-panel"></div>

<div id="map"></div>
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script src="map.js"></script>
<script src="profile.js"></script>

</body>
</html>
