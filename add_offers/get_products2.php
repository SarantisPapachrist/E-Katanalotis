<?php
include '../database/db.php';

$sql = "SELECT Category, SubCategory, Product_name FROM Products";
$result = mysqli_query($conn, $sql);

if (!$result) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . mysqli_error($conn)]);
    exit();
}

$data = [];

while ($row = mysqli_fetch_assoc($result)) {
    $category = $row['Category'];
    $subcategory = $row['SubCategory'];
    $product = $row['Product_name'];

    if (!isset($data[$category])) {
        $data[$category] = [];
    }
    if (!isset($data[$category][$subcategory])) {
        $data[$category][$subcategory] = [];
    }

    $data[$category][$subcategory][] = $product;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
