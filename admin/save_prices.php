<?php
include '../database/db.php'; 

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || !isset($data['data'])) {
    exit('❌ Invalid JSON or missing "data" array.');
}

$inserted = 0;
foreach ($data['data'] as $item) {
    $productName = mysqli_real_escape_string($conn, $item['name']);

    foreach ($item['prices'] as $priceInfo) {
        $price = floatval($priceInfo['price']);
        $date = mysqli_real_escape_string($conn, $priceInfo['date']);

        $sql = "INSERT INTO Prices (Price, Product_name, price_date)
                VALUES ('$price', '$productName', '$date')
                ON DUPLICATE KEY UPDATE Price='$price'";

        if (mysqli_query($conn, $sql)) {
            $inserted++;
        }
    }
}

echo "Prices uploaded successfully! ($inserted records added/updated)";
?>
