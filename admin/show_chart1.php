<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Weekly Offers Chart</title>
<link rel="stylesheet" href="chart.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    </div>
</div>

<h2>Weekly Offers & Discounts</h2>
<div class="form-container">
    <label>Category:</label>
    <select id="category-select">
        <option value="">Select Category</option>
    </select>

    <label>Subcategory:</label>
    <select id="subcategory-select">
        <option value="">Select Subcategory</option>
    </select>

    <button id="load-chart">Show Chart</button>
</div>

<div class="chart-container">
    <canvas id="discountChart" width="800" height="400"></canvas>
</div>

<script src="show_chart.js"></script>
</body>
</html>


<script>
let categoriesData = {}; 
let chart; 

fetch('../add_offers/get_products2.php') 
  .then(res => res.json())
  .then(data => {
    categoriesData = data;
    let catSelect = document.getElementById('category-select');
    for (let cat in data) {
      let opt = document.createElement('option');
      opt.value = cat;
      opt.textContent = cat;
      catSelect.appendChild(opt);
    }
  });

document.getElementById('category-select').addEventListener('change', function() {
    const subSelect = document.getElementById('subcategory-select');
    subSelect.innerHTML = '<option value="">Select Subcategory</option>';
    const cat = this.value;
    if (!cat) return;
    for (let sub in categoriesData[cat]) {
        const opt = document.createElement('option');
        opt.value = sub;
        opt.textContent = sub;
        subSelect.appendChild(opt);
    }
});

document.getElementById('load-chart').addEventListener('click', () => {
    const category = document.getElementById('category-select').value;
    const subcategory = document.getElementById('subcategory-select').value;

    if (!category) {
        alert('Select a category');
        return;
    }

    fetch('get_chart_data.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ category, subcategory })
    })
    .then(res => res.json())
    .then(data => {
        const labels = data.map(d => d.date);
        const averages = data.map(d => Math.abs(parseFloat(d.average)));

        if (chart) chart.destroy();

        chart = new Chart(document.getElementById('discountChart').getContext('2d'), {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'Average Discount %',
                    data: averages,
                    borderColor: 'green',
                    backgroundColor: '#007bff',
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true, title: { display: true, text: 'Discount %' } },
                    x: { title: { display: true, text: 'Date' } }
                }
            }
        });
    });
});
</script>

</body>
</html>
