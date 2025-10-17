<?php
include '../database/db.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$category = $input['category'] ?? '';
$subcategory = $input['subcategory'] ?? null;

if (!$category) {
    echo json_encode([]);
    exit();
}

$products_values = [];
if ($subcategory) {
    $sql = "SELECT Products.Product_name, AVG(Prices.Price) as avg_price
            FROM Products 
            JOIN Prices ON Products.Product_name = Prices.Product_name
            WHERE Products.Category=? AND Products.SubCategory=?
            GROUP BY Products.Product_name";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $category, $subcategory);
} else {
    $sql = "SELECT Products.Product_name, AVG(Prices.Price) as avg_price
            FROM Products 
            JOIN Prices ON Products.Product_name = Prices.Product_name
            WHERE Products.Category=?
            GROUP BY Products.Product_name";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $category);
}

$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $products_values[$row['Product_name']] = floatval($row['avg_price']);
}

$last7days = [];
for ($i = 6; $i >= 0; $i--) {
    $last7days[] = date('Y-m-d', strtotime("-$i days"));
}

if ($subcategory) {
    $sql2 = "SELECT Product, Price, DATE(created_at) as date FROM Offers 
             WHERE Category=? AND SubCategory=? AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("ss", $category, $subcategory);
} else {
    $sql2 = "SELECT Product, Price, DATE(created_at) as date FROM Offers 
             WHERE Category=? AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("s", $category);
}

$stmt2->execute();
$result2 = $stmt2->get_result();

$discounts = [];
while ($row = $result2->fetch_assoc()) {
    $product = $row['Product'];
    $offerPrice = floatval($row['Price']);
    $date = $row['date'];

    if (!isset($products_values[$product])) continue;

    $avgPrice = $products_values[$product];
    $discountPercent = (($avgPrice - $offerPrice) / $avgPrice) * 100;

    if (!isset($discounts[$date])) $discounts[$date] = ['sum' => 0, 'count' => 0];
    $discounts[$date]['sum'] += $discountPercent;
    $discounts[$date]['count']++;
}

$dataset = [];
foreach ($last7days as $day) {
    if (isset($discounts[$day])) {
        $avg = $discounts[$day]['count'] > 0 ? $discounts[$day]['sum'] / $discounts[$day]['count'] : 0;
    } else {
        $avg = 0;
    }
    $dataset[] = ['date' => $day, 'average' => number_format($avg, 2)];
}

echo json_encode($dataset);
?>
