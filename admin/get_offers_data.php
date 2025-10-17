<?php
include '../database/db.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$month = intval($input['month'] ?? date('m'));
$year = intval($input['year'] ?? date('Y'));

$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$allDates = [];
for ($d=1;$d<=$daysInMonth;$d++) {
    $allDates[] = sprintf('%04d-%02d-%02d', $year, $month, $d);
}

$query = "SELECT DATE(created_at) AS date, COUNT(*) AS count
          FROM Offers
          WHERE YEAR(created_at)=? AND MONTH(created_at)=?
          GROUP BY DATE(created_at)";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $year, $month);
$stmt->execute();
$result = $stmt->get_result();

$offerData = [];
while($row = $result->fetch_assoc()) {
    $offerData[$row['date']] = intval($row['count']);
}

foreach($allDates as $date){
    if(!isset($offerData[$date])) $offerData[$date] = 0;
}

ksort($offerData);

$output = [];
foreach($offerData as $date=>$count){
    $output[] = ['date'=>$date,'count'=>$count];
}

echo json_encode($output);
