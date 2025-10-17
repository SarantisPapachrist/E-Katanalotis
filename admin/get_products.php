<?php
include '../database/db.php';

$subcategory = mysqli_real_escape_string($conn, $_GET['subcategory']);
$query = "SELECT * FROM Products WHERE SubCategory='$subcategory'";
$result = mysqli_query($conn, $query);

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>
            <td>{$row['Product_ID']}</td>
            <td>{$row['Product_name']}</td>
            <td>{$row['Category']}</td>
            <td>{$row['SubCategory']}</td>
          </tr>";
}
?>
