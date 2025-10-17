<?php
include '../database/db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Products</title>
<link rel="stylesheet" href="../css/admin_style.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<div class="navbar">
  <div class="welcome">🛠️ Admin Panel</div>
  <div class="nav-buttons">
    <form action="admin.php" method="get" style="display:inline;">
      <button type="submit">Back to Dashboard</button>
    </form>
  </div>
</div>

<div class="view-container">
  <h2>View Products</h2>

  <label for="category">Select Category:</label>
  <select id="category">
    <option value="">-- Select Category --</option>
    <?php
      $catQuery = "SELECT DISTINCT Category FROM Products ORDER BY Category ASC";
      $catResult = mysqli_query($conn, $catQuery);
      while($cat = mysqli_fetch_assoc($catResult)) {
          echo "<option value='".htmlspecialchars($cat['Category'])."'>".htmlspecialchars($cat['Category'])."</option>";
      }
    ?>
  </select>

  <label for="subcategory">Select Subcategory:</label>
  <select id="subcategory">
    <option value="">-- Select Subcategory --</option>
  </select>

  <table id="productTable" border="1" style="margin-top:20px;">
    <thead>
      <tr>
        <th>ID</th>
        <th>Product Name</th>
        <th>Category</th>
        <th>Subcategory</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>
</div>

<script>
// Otal allazei category --> pare subcategory
$('#category').change(function() {
    let cat = $(this).val();
    if (cat) {
        $.ajax({
            url: 'get_subcategories.php',
            type: 'GET',
            data: { category: cat },
            success: function(data) {
                $('#subcategory').html(data);
            }
        });
    }
});

// Otal allazei subcat --> pare procucts
$('#subcategory').change(function() {
    let subcat = $(this).val();
    if (subcat) {
        $.ajax({
            url: 'get_products.php',
            type: 'GET',
            data: { subcategory: subcat },
            success: function(data) {
                $('#productTable tbody').html(data);
            }
        });
    }
});
</script>

</body>
</html>
