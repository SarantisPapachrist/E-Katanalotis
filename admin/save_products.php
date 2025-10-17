<?php
include '../database/db.php'; 

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || !isset($data['products'])) {
    exit('❌ Invalid JSON or missing "products" array.');
}

$inserted = 0;
foreach ($data['products'] as $prod) {
    $name = mysqli_real_escape_string($conn, $prod['name']);
    $category = mysqli_real_escape_string($conn, $prod['category']);
    $subcategory = mysqli_real_escape_string($conn, $prod['subcategory']);

    $sql = "INSERT INTO Products (Product_name, Category, SubCategory)
            VALUES ('$name', '$category', '$subcategory')";
    if (mysqli_query($conn, $sql)) {
        $inserted++;
    }
}

echo "Products uploaded successfully! ($inserted records added)";
?>

