<?php
session_start();

// 1.Checking if a session exists
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: login.php?error=unauthorized");
    exit();
}

// 2.Checking if the logged in person is Admin
if ($_SESSION['role'] !== 'Admin') {
    // If not Admin, redirect to Customer dashboard or login.
    header("Location: customer_dashboard.php?error=access_denied");
    exit();
}


include 'db_config.php';

// --- 1. SUMMARY SECTION (Revenue & Profit) ---
// Delivered orders are not included in the total revenue.
$revenue_query = "SELECT SUM(Total_Amount) AS total_revenue FROM `order` WHERE Order_Status = 'Delivered'";
$revenue_result = $conn->query($revenue_query);
$revenue_data = $revenue_result->fetch_assoc();
$total_revenue = $revenue_data['total_revenue'] ?? 0;

// Calculating the profit on the block
$profit_query = "SELECT SUM((b.selling_price - b.buying_price) * oi.Quantity) AS calculated_profit
                 FROM order_item oi
                 JOIN inventory_batches b ON oi.Batch_ID = b.Batch_ID
                 JOIN `order` o ON oi.Order_ID = o.Order_ID
                 WHERE o.Order_Status = 'Delivered'";
$profit_result = $conn->query($profit_query);
$profit_data = $profit_result->fetch_assoc();

// Let's assume the current profit to be Rs. 10,000.
$base_profit = 10000; 
$calculated_profit = $profit_data['calculated_profit'] ?? 0;

// Total profit = 10,000 + new profit from the database
$total_profit = $base_profit + $calculated_profit;


// 1. Expiry Alerts (Next 7 Days) - Batches that will expire within the next 7 days from today
$expiry_sql = "SELECT p.Name, b.Batch_Number, b.Expiry_Date 
               FROM inventory_batches b 
               JOIN product p ON b.Product_ID = p.Product_ID 
               WHERE b.Expiry_Date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
               ORDER BY b.Expiry_Date ASC";
$expiry_result = $conn->query($expiry_sql);

// 2.Low Stock - Products that are running out of stock (e.g. less than 15)
$stock_sql = "SELECT p.Name, SUM(b.Current_Stock) as Current_Stock 
              FROM inventory_batches b 
              JOIN product p ON b.Product_ID = p.Product_ID 
              GROUP BY p.Product_ID 
              HAVING Current_Stock < 15 
              ORDER BY Current_Stock ASC";
$stock_result = $conn->query($stock_sql);

// 3. Top Products - Top selling products (from the order_item table)
$top_sql = "SELECT p.Name, SUM(oi.Quantity) as total_sold 
            FROM order_item oi 
            JOIN product p ON oi.Product_ID = p.Product_ID 
            GROUP BY p.Product_ID 
            ORDER BY total_sold DESC 
            LIMIT 5";
$top_result = $conn->query($top_sql);



// --- 6. ACTIVE FLASH OFFERS ---
$flash_query = "SELECT pr.*, p.Name 
                FROM promotions pr 
                LEFT JOIN product p ON pr.Product_ID = p.Product_ID 
                WHERE pr.Promo_Type = 'Flash Sale' 
                AND pr.Status = 1 
                AND NOW() BETWEEN pr.Start_Date AND pr.Expiry_Date";
$flash_result = $conn->query($flash_query);

// --- 5. SALES TREND FOR CHART (Last 7 Days) ---
$sales_trend_query = "SELECT DATE(Order_Date) as date, SUM(Total_Amount) as daily_revenue 
                      FROM `order` 
                      WHERE Order_Status = 'Delivered' 
                      AND Order_Date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                      GROUP BY DATE(Order_Date) 
                      ORDER BY DATE(Order_Date) ASC";
$trend_result = $conn->query($sales_trend_query);

$dates = [];
$revenues = [];

while($row = $trend_result->fetch_assoc()) {
    $dates[] = $row['date'];
    $revenues[] = $row['daily_revenue'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bfresh Admin | Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --primary: #ffcc00; --bg: #0a0a0a; --card-bg: #151515; --text: #eee; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); color: var(--text); margin: 0; padding: 20px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: var(--card-bg); padding: 20px; border-radius: 15px; border-bottom: 4px solid var(--primary); }
        .stat-card h3 { margin: 0; font-size: 0.9rem; color: #888; text-transform: uppercase; }
        .stat-card .value { font-size: 1.8rem; font-weight: bold; margin: 10px 0; color: var(--primary); }
        
        .content-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
        .content-card { background: var(--card-bg); padding: 20px; border-radius: 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { text-align: left; color: #888; font-size: 0.8rem; padding: 10px; border-bottom: 1px solid #333; }
        td { padding: 12px 10px; border-bottom: 1px solid #222; font-size: 0.9rem; }
        
        .badge { padding: 4px 8px; border-radius: 5px; font-size: 0.7rem; font-weight: bold; }
        .badge-red { background: rgba(255, 68, 68, 0.1); color: #ff4444; }
        .badge-yellow { background: rgba(255, 204, 0, 0.1); color: #ffcc00; }


        /* Navigation */
nav { margin-bottom: 30px; }
nav a {
    color: var(--text-dim);
    text-decoration: none;
    font-weight: 600;
    margin-right: 20px;
    transition: var(--transition);
}
nav a:hover { color: var(--primary); }
    </style>
</head>
<body>

    <h2><i class="fas fa-chart-pie"></i> Business Overview</h2>

    <nav>
           <a href="manage_inventory.php"><i class="fas fa-boxes"></i> manage_inventory</a>
            
            <a href="manage_products.php">Manage Products</a>
             <a href="orders.php">Manage Orders</a>
        <a href="reviews.php">View Detailed Reports</a>
        <a href="manage_promotions.php">promotions</a>
        <a href="logout.php">Logout</a>
        </nav>

    <div class="grid">
        <div class="stat-card">
            <h3>Total Revenue</h3>
            <div class="value">Rs. <?php echo number_format($total_revenue, 2); ?></div>
            <small>Total sales from delivered orders</small>
        </div>
        <div class="stat-card" style="border-color: #00ff88;">
            <h3>Net Profit</h3>
            <div class="value" style="color: #00ff88;">Rs. <?php echo number_format($total_profit, 2); ?></div>
            <small>Based on batch buying prices</small>
        </div>
        <div class="stat-card" style="border-color: #ff4444;">
            <h3>Active Alerts</h3>
            <div class="value" style="color: #ff4444;"><?php echo ($expiry_result->num_rows + $stock_result->num_rows); ?></div>
            <small>Stock & Expiry issues</small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="content-card" style="margin-bottom: 25px; border-left: 5px solid #ffcc00;">
    <h3><i class="fas fa-bolt" style="color: #ffcc00;"></i> Active Flash Sales</h3>
    <div style="display: flex; gap: 15px; flex-wrap: wrap; margin-top: 15px;">
        <?php if($flash_result->num_rows > 0): ?>
            <?php while($flash = $flash_result->fetch_assoc()): ?>
                <div style="background: #222; padding: 15px; border-radius: 10px; min-width: 200px;">
                    <div style="color: #ffcc00; font-weight: bold; font-size: 1.1rem;"><?php echo $flash['Discount_Percent']; ?>% OFF</div>
                    <div style="font-size: 0.9rem; margin: 5px 0;"><?php echo $flash['Name'] ?? 'All Products'; ?></div>
                    <small style="color: #888;">Ends: <?php echo $flash['Expiry_Date']; ?></small>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="color: #666; font-size: 0.9rem;">No active flash sales right now.</p>
        <?php endif; ?>
    </div>
</div>

<div class="content-card" style="margin-bottom: 25px; background: #151515; border-radius: 15px; padding: 20px;">
    <h3 style="margin-bottom: 20px;"><i class="fas fa-chart-line" style="color: #ffcc00;"></i> Weekly Sales Trend</h3>
    <canvas id="salesChart" style="max-height: 300px;"></canvas>
</div>

<script>
const ctx = document.getElementById('salesChart').getContext('2d');
const salesChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($dates); ?>,
        datasets: [{
            label: 'Daily Revenue (Rs.)',
            data: <?php echo json_encode($revenues); ?>,
            borderColor: '#ffcc00',
            backgroundColor: 'rgba(255, 204, 0, 0.1)',
            borderWidth: 3,
            tension: 0.4,
            fill: true,
            pointBackgroundColor: '#ffcc00'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: '#222' },
                ticks: { color: '#888' }
            },
            x: {
                grid: { display: false },
                ticks: { color: '#888' }
            }
        }
    }
});
</script>
<div class="content-grid">
    <!-- Expiry Alerts Section -->
    <div class="content-card">
        <h3><i class="fas fa-hourglass-half"></i> Expiry Alerts (Next 7 Days)</h3>
        <?php if($expiry_result && $expiry_result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Batch</th>
                    <th>Expiry Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $expiry_result->fetch_assoc()): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($row['Name']); ?></strong></td>
                    <td>#<?php echo htmlspecialchars($row['Batch_Number']); ?></td>
                    <td style="color: #ff4444;"><?php echo $row['Expiry_Date']; ?></td>
                    <td><span class="badge badge-red">Expiring Soon</span></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p style="color: #666; margin-top: 20px;">No near-expiry batches found.</p>
        <?php endif; ?>
    </div>

    <div class="space-y-4">
        <!-- Low Stock Section -->
        <div class="content-card" style="margin-bottom: 20px;">
            <h3><i class="fas fa-exclamation-triangle"></i> Low Stock</h3>
            <?php if($stock_result && $stock_result->num_rows > 0): ?>
                <?php while($row = $stock_result->fetch_assoc()): ?>
                    <div style="padding: 10px 0; border-bottom: 1px solid #222;">
                        <span style="font-size: 0.9rem;"><?php echo htmlspecialchars($row['Name']); ?></span>
                        <span class="badge badge-yellow" style="float: right;"><?php echo $row['Current_Stock']; ?> Left</span>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="color: #666; font-size: 0.8rem;">All items well stocked.</p>
            <?php endif; ?>
        </div>

        <!-- Top Products Section -->
        <div class="content-card">
            <h3><i class="fas fa-trophy"></i> Top Products</h3>
            <?php if($top_result && $top_result->num_rows > 0): ?>
                <?php while($row = $top_result->fetch_assoc()): ?>
                    <div style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #222;">
                        <span style="font-weight: 600;"><?php echo htmlspecialchars($row['Name']); ?></span>
                        <span style="color: var(--primary); font-weight: 800;"><?php echo $row['total_sold']; ?> sold</span>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="color: #666; font-size: 0.8rem;">No sales data available.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>