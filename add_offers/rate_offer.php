<?php
session_start();
header('Content-Type: application/json');
include '../database/db.php';

if (!isset($_SESSION['Username'])) {
    echo json_encode(["success" => false, "message" => "You must be logged in."]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
$offer_id = intval($data['offer_id'] ?? 0);
$type = $data['type'] ?? '';
$username = $_SESSION['Username'];

if (!$offer_id || !in_array($type, ['like', 'dislike'])) {
    echo json_encode(["success" => false, "message" => "Invalid data."]);
    exit();
}

$userQuery = $conn->prepare("SELECT PersonID FROM Users WHERE Username = ?");
$userQuery->bind_param("s", $username);
$userQuery->execute();
$userResult = $userQuery->get_result();
if (!$userRow = $userResult->fetch_assoc()) {
    echo json_encode(["success" => false, "message" => "User not found."]);
    exit();
}
$person_id = $userRow['PersonID'];

$offerQuery = $conn->prepare("SELECT Pusername FROM Offers WHERE offer_id = ?");
$offerQuery->bind_param("i", $offer_id);
$offerQuery->execute();
$offerResult = $offerQuery->get_result();
if (!$offerRow = $offerResult->fetch_assoc()) {
    echo json_encode(["success" => false, "message" => "Offer not found."]);
    exit();
}
$creatorUsername = $offerRow['Pusername'];

$creatorQuery = $conn->prepare("SELECT PersonID FROM Users WHERE Username = ?");
$creatorQuery->bind_param("s", $creatorUsername);
$creatorQuery->execute();
$creatorResult = $creatorQuery->get_result();
$creatorRow = $creatorResult->fetch_assoc();
$creator_id = $creatorRow ? $creatorRow['PersonID'] : null;

$checkQuery = $conn->prepare("SELECT Click1 FROM LikesDislikes WHERE offer_id = ? AND person_id = ?");
$checkQuery->bind_param("ii", $offer_id, $person_id);
$checkQuery->execute();
$checkResult = $checkQuery->get_result();

if ($row = $checkResult->fetch_assoc()) {
    $previousType = $row['Click1'];

    if ($previousType === $type) {
        echo json_encode(["success" => false, "message" => "You have already rated this offer."]);
        exit();
    }

    $updateQuery = $conn->prepare("UPDATE LikesDislikes SET Click1 = ? WHERE offer_id = ? AND person_id = ?");
    $updateQuery->bind_param("sii", $type, $offer_id, $person_id);
    $updateQuery->execute();

    if ($type === 'like') {
        $conn->query("UPDATE Offers SET Likes = Likes + 1, Dislikes = Dislikes - 1 WHERE offer_id = $offer_id");
    } else {
        $conn->query("UPDATE Offers SET Dislikes = Dislikes + 1, Likes = Likes - 1 WHERE offer_id = $offer_id");
    }

    if ($creator_id) {
        if ($type === 'like') {
            $adjust = 15;
        } else {
            $adjust = -15;
        }
        $scoreUpdate = $conn->prepare("UPDATE Users SET Score = Score + ? WHERE PersonID = ?");
        $scoreUpdate->bind_param("ii", $adjust, $creator_id);
        $scoreUpdate->execute();
    }

    echo json_encode(["success" => true, "message" => "Your rating was updated."]);
    exit();

} else {
    $insertQuery = $conn->prepare("INSERT INTO LikesDislikes (offer_id, person_id, Click1) VALUES (?, ?, ?)");
    $insertQuery->bind_param("iis", $offer_id, $person_id, $type);
    $insertQuery->execute();

    if ($type === 'like') {
        $conn->query("UPDATE Offers SET Likes = Likes + 1 WHERE offer_id = $offer_id");
        $points = 10;
    } else {
        $conn->query("UPDATE Offers SET Dislikes = Dislikes + 1 WHERE offer_id = $offer_id");
        $points = -5;
    }

    if ($creator_id) {
        $scoreUpdate = $conn->prepare("UPDATE Users SET Score = Score + ? WHERE PersonID = ?");
        $scoreUpdate->bind_param("ii", $points, $creator_id);
        $scoreUpdate->execute();
    }

    echo json_encode(["success" => true, "message" => "Your rating was recorded."]);
    exit();
}

?>