<?php
header('Content-Type: application/json');
include '../database/db.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['offer_id'])) {
    echo json_encode(["success" => false, "message" => "Offer ID missing."]);
    exit();
}

$offer_id = intval($input['offer_id']);

$conn->query("SET FOREIGN_KEY_CHECKS=0");

$sql = "DELETE FROM Offers WHERE offer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $offer_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Offer deleted successfully."]);
} else {
    echo json_encode(["success" => false, "message" => "Database error: " . $stmt->error]);
}

$conn->query("SET FOREIGN_KEY_CHECKS=1");

$stmt->close();
$conn->close();
exit();
?>
