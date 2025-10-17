<?php
session_start();
header('Content-Type: application/json');
include '../database/db.php';

if (!isset($_SESSION['Username'])) {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "You must be logged in."]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
    exit();
}

$input = json_decode(file_get_contents("php://input"), true);

if (!$input) {
    echo json_encode(["success" => false, "message" => "Invalid JSON data."]);
    exit();
}

$category    = mysqli_real_escape_string($conn, $input['category'] ?? '');
$subcategory = mysqli_real_escape_string($conn, $input['subcategory'] ?? '');
$product     = mysqli_real_escape_string($conn, $input['product'] ?? '');
$price       = floatval($input['price'] ?? 0);
$shop_name   = mysqli_real_escape_string($conn, $input['shop_name'] ?? '');
$shop_id     = mysqli_real_escape_string($conn, $input['shop_id'] ?? '');
$pusername   = mysqli_real_escape_string($conn, $_SESSION['Username'] ?? '');

if (empty($category) || empty($subcategory) || empty($product) || empty($shop_name) || $price <= 0) {
    echo json_encode(["success" => false, "message" => "Please fill in all fields correctly."]);
    exit();
}

$sql = "INSERT INTO Offers 
        (Category, SubCategory, Price, Shop_id, shop_name, Likes, Dislikes, Apothema, Product, Pusername)
        VALUES (?, ?, ?, ?, ?, 0, 0, 1, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssdssss", $category, $subcategory, $price, $shop_id, $shop_name, $product, $pusername);

if (!$stmt->execute()) {
    echo json_encode(["success" => false, "message" => "Database error: " . $stmt->error]);
    exit();
}

$points = 0;

$userQuery = $conn->prepare("SELECT PersonID FROM Users WHERE Username = ?");
$userQuery->bind_param("s", $pusername);
$userQuery->execute();
$userResult = $userQuery->get_result();
if ($userRow = $userResult->fetch_assoc()) {
    $person_id = $userRow['PersonID'];
} else {
    echo json_encode(["success" => true, "message" => "Offer added, but user not found for scoring."]);
    exit();
}

$priceQuery = $conn->prepare("SELECT Price FROM Prices WHERE Product_name = ? ORDER BY price_date DESC LIMIT 5");
$priceQuery->bind_param("s", $product);
$priceQuery->execute();
$priceResult = $priceQuery->get_result();

$prices = [];
while ($row = $priceResult->fetch_assoc()) {
    $prices[] = floatval($row['Price']);
}

if (count($prices) > 0) {
    $lastPrice = $prices[0];
    $allHigher = true;
    foreach ($prices as $p) {
        if ($price >= $p) {
            $allHigher = false;
            break;
        }
    }

    if ($allHigher) {
        $points = 100;
    } elseif ($price < $lastPrice) {
        $points = 50;
    }
}

if ($points > 0) {
    $updateScore = $conn->prepare("UPDATE Users SET Score = Score + ? WHERE PersonID = ?");
    $updateScore->bind_param("ii", $points, $person_id);
    $updateScore->execute();
    $updateScore->close();
}

if ($points > 0) {
    echo json_encode([
        "success" => true,
        "message" => "🎉 Offer added successfully! You earned $points points."
    ]);
} else {
    echo json_encode([
        "success" => true,
        "message" => "Offer added successfully! (No points awarded for this price.)"
    ]);
}

$stmt->close();
$conn->close();
?>
