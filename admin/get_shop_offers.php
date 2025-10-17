<?php
include '../database/db.php';

$shop_name = $_GET['shop_name'] ?? '';

if (!$shop_name) {
    echo json_encode([]);
    exit();
}

$shop_name = mysqli_real_escape_string($conn, $shop_name);

$sql = "SELECT * FROM Offers WHERE shop_name = '$shop_name'";
$result = mysqli_query($conn, $sql);

$offers = [];
while ($row = mysqli_fetch_assoc($result)) {
    $offers[] = $row;
}

header('Content-Type: application/json');
echo json_encode($offers);
?>
