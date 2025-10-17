<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Upload Products</title>
<link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<div class="upload-container">
  <h2>Upload Products JSON</h2>
  
  <input type="file" id="productFileInput" accept=".json">
  <button id="uploadProductsBtn">Upload File</button>
  
  <p id="status"></p>

  <br>
  <a href="admin.php" class="back-btn">← Back to Admin Panel</a>
</div>

<script src="upload_products.js"></script>
</body>
</html>

