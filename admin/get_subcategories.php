<?php
include '../database/db.php';

$category = mysqli_real_escape_string($conn, $_GET['category']);
$query = "SELECT DISTINCT SubCategory FROM Products WHERE Category='$category' ORDER BY SubCategory ASC";
$result = mysqli_query($conn, $query);

echo "<option value=''>-- Select Subcategory --</option>";
while ($row = mysqli_fetch_assoc($result)) {
    echo "<option value='".htmlspecialchars($row['SubCategory'])."'>".htmlspecialchars($row['SubCategory'])."</option>";
}
?>
