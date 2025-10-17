<?php
include '../database/db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Monthly Offers Chart</title>
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

<h2>Number of Offers Submitted Each Day</h2>

<div class="form-container">
    <label for="month">Month:</label>
    <select id="month">
        <?php for($i=1;$i<=12;$i++): ?>
            <option value="<?= $i ?>" <?= $i == date('m') ? 'selected' : '' ?>><?= date('F', mktime(0,0,0,$i,1)) ?></option>
        <?php endfor; ?>
    </select>

    <label for="year">Year:</label>
    <select id="year">
        <?php
        $currentYear = date('Y');
        for($i=$currentYear; $i>=$currentYear-10; $i--):
        ?>
            <option value="<?= $i ?>" <?= $i == date('Y') ? 'selected' : '' ?>><?= $i ?></option>
        <?php endfor; ?>
    </select>

    <button id="load-chart">Show Chart</button>
</div>

<div class="chart-container">
    <canvas id="offerChart"></canvas>
</div>

<script>
let chart;

function loadChart() {
    const month = document.getElementById('month').value;
    const year = document.getElementById('year').value;

    fetch('get_offers_data.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ month, year })
    })
    .then(res => res.json())
    .then(data => {
        const labels = data.map(d => d.date);
        const counts = data.map(d => d.count);

        if(chart) chart.destroy();

        chart = new Chart(document.getElementById('offerChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Number of Offers',
                    data: counts,
                    backgroundColor: '#007bff',
                    borderColor: 'black',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: `Offers in ${document.getElementById('month').selectedOptions[0].text} ${year}`,
                        font: { size: 22, weight: 'bold' },
                        color: '#007bff'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Offers: ' + context.parsed.y;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Date',
                            font: { size: 16, weight: 'bold' },
                            color: '#555'
                        },
                        ticks: { font: { size: 12 } }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Offers',
                            font: { size: 16, weight: 'bold' },
                            color: '#555'
                        },
                        ticks: { font: { size: 12 } }
                    }
                }
            }
        });
    })
    .catch(err => console.error('Error fetching chart data:', err));
}

loadChart();
document.getElementById('load-chart').addEventListener('click', loadChart);
</script>

</body>
</html>
