<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Panel - E-Supermarket</title>

<link rel="stylesheet" href="../css/admin_style.css">

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
</head>
<body>

<div class="navbar">
    <div class="welcome">
        🛠️ Admin Panel
    </div>
    <div class="nav-buttons">
        <form action="show_chart1.php" method="get" style="display:inline;">
            <button type="submit">View Discount Chart</button>
        </form>
        <form action="show_chart2.php" method="get" style="display:inline;">
            <button type="submit">View Offers Chart</button>
        </form>
        <form action="view_products.php" method="get" style="display:inline;">
            <button type="submit">View Products</button>
        </form>
        <form action="upload_products.php" method="get" style="display:inline;">
            <button type="submit">Upload Products</button>
        </form>
        <form action="upload_prices.php" method="get" style="display:inline;">
            <button type="submit">Upload Prices</button>
        </form>
        <form action="../user/logout.php" method="post" style="display:inline;">
        <button type="submit">Logout</button>
        </form>
    </div>
</div>

<div id="admin-map"></div>

<div id="admin-offers-panel">
    <h3 id="admin-offers-title">Shop Offers</h3>
    <div id="admin-offers-container"></div>
    <button id="close-admin-offers">Close</button>
</div>

<div id="message-panel"></div>
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script src="admin_map.js"></script>

</body>
</html>
