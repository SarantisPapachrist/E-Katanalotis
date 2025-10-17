<?php
include '../database/db.php';
header('Content-Type: text/plain');

$json = file_get_contents('php://input');
if (!$json) {
    exit("❌ No JSON data received.");
}

$data = json_decode($json, true);
if (!$data || !isset($data['products']) || !isset($data['categories'])) {
    exit("❌ Invalid JSON: Missing 'products' or 'categories' arrays.");
}

$subcategoryMap = [];

foreach ($data['categories'] as $cat) {
    $categoryName = $cat['name'];
    if (isset($cat['subcategories'])) {
        foreach ($cat['subcategories'] as $sub) {
            $subcategoryMap[$sub['uuid']] = [
                'category' => $categoryName,
                'subcategory' => $sub['name']
            ];
        }
    }
}

$inserted = 0;

foreach ($data['products'] as $prod) {
    $productName = mysqli_real_escape_string($conn, $prod['name']);
    $subUUID = $prod['subcategory'];

    if (isset($subcategoryMap[$subUUID])) {
        $category = mysqli_real_escape_string($conn, $subcategoryMap[$subUUID]['category']);
        $subcategory = mysqli_real_escape_string($conn, $subcategoryMap[$subUUID]['subcategory']);
    } else {
        $category = "Unknown";
        $subcategory = "Unknown";
    }

    $sql = "INSERT INTO Products (Product_name, Category, SubCategory)
            VALUES ('$productName', '$category', '$subcategory')";

    if (mysqli_query($conn, $sql)) {
        $inserted++;
    }
}

echo "✅ Uploaded successfully! Inserted $inserted products.\n";
?>
