<?php
include '../database/db.php';
$data = json_decode(file_get_contents('php://input'), true);
$shop_id = mysqli_real_escape_string($conn, $data['shop_id'] ?? '');

if (!$shop_id) {
    echo json_encode([]);
    exit();
}

$sql = "SELECT * FROM Offers WHERE Shop_id = '$shop_id'";
$result = mysqli_query($conn, $sql);
$offers = [];
while($row = mysqli_fetch_assoc($result)) {
    $offers[] = $row;
}

header('Content-Type: application/json');
echo json_encode($offers);
