<?php
session_start();

// User Login වී නැතිනම් Login පිටුවට හරවා යවයි
if (!isset($_SESSION['name'])) {
    header("Location: login.php");
    exit();
}

include 'db_config.php';

// 1. Database එකෙන් දැනට තිබෙන Active Products ලැයිස්තුව ලබා ගැනීම (Drop-down එක සඳහා)
$product_query = "SELECT Product_ID, Name FROM product WHERE status = 1";
$products_result = $conn->query($product_query);

// 2. දැනට පවතින Flash Sales ලැයිස්තුව ලබා ගැනීම (Table එකේ පෙන්වීමට)
$all_promotions_query = "SELECT pr.*, p.Name AS Product_Name 
                         FROM promotions pr 
                         LEFT JOIN product p ON pr.Product_ID = p.Product_ID 
                         ORDER BY pr.Promo_ID DESC";
$all_promotions = $conn->query($all_promotions_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bfresh Admin | Manage Flash Sales</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --primary: #ffcc00; --bg: #0a0a0a; --card-bg: #151515; --text: #eee; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); color: var(--text); margin: 0; padding: 20px; }
        
        .container { max-width: 1000px; margin: auto; }
        .back-btn { color: var(--primary); text-decoration: none; font-weight: bold; display: inline-block; margin-bottom: 20px; }
        
        .form-card { background: var(--card-bg); padding: 25px; border-radius: 15px; border-top: 4px solid var(--primary); margin-bottom: 30px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #888; font-size: 0.9rem; }
        
        select, input { 
            width: 100%; padding: 12px; background: #222; border: 1px solid #333; 
            color: #fff; border-radius: 8px; box-sizing: border-box; 
        }
        
        .btn-submit { 
            background: var(--primary); color: #000; padding: 12px 25px; border: none; 
            border-radius: 8px; font-weight: bold; cursor: pointer; width: 100%; margin-top: 10px;
        }
        
        .promo-table { width: 100%; border-collapse: collapse; background: var(--card-bg); border-radius: 15px; overflow: hidden; }
        th { text-align: left; background: #222; color: var(--primary); padding: 15px; font-size: 0.85rem; }
        td { padding: 15px; border-bottom: 1px solid #222; font-size: 0.9rem; }
        .status-active { color: #00ff88; font-weight: bold; }
        .status-expired { color: #ff4444; }
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

<div class="container">
    <a href="admin_dashboard.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>


    <h2><i class="fas fa-bolt" style="color: var(--primary);"></i> Manage Flash Sales</h2>

     <nav>
        <a href="admin_dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a>
           <a href="manage_inventory.php"><i class="fas fa-boxes"></i> manage_inventory</a>
          
            <a href="manage_products.php">Manage Products</a>
             <a href="orders.php">Manage Orders</a>
        <a href="reviews.php">View Detailed Reports</a>
        <a href="logout.php">Logout</a>
        </nav>


    <div class="form-card">
        <h3>Create New Offer</h3>
        <form action="save_flash_sale.php" method="POST">
            <div class="form-group">
                <label>Select Product</label>
                <select name="product_id" required>
                    <option value="">-- Choose a Product --</option>
                    <?php while($p = $products_result->fetch_assoc()): ?>
                        <option value="<?php echo $p['Product_ID']; ?>"><?php echo $p['Name']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Discount Percentage (%)</label>
                    <input type="number" name="discount" placeholder="e.g. 20" required min="1" max="100">
                </div>
                <div class="form-group">
                    <label>Promo Type</label>
                    <input type="text" name="promo_type" value="Flash Sale" readonly>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Start Date & Time</label>
                    <input type="datetime-local" name="start_date" required>
                </div>
                <div class="form-group">
                    <label>Expiry Date & Time</label>
                    <input type="datetime-local" name="expiry_date" required>
                </div>
            </div>

            <button type="submit" class="btn-submit">Add Flash Sale Offer</button>
        </form>
    </div>

    <h3>Existing Promotions</h3>
    <table class="promo-table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Discount</th>
                <th>Start Date</th>
                <th>Expiry Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if($all_promotions->num_rows > 0): ?>
                <?php while($row = $all_promotions->fetch_assoc()): ?>
                <tr>
                    <td><strong><?php echo $row['Product_Name'] ?? 'General'; ?></strong></td>
                    <td><?php echo $row['Discount_Percent']; ?>%</td>
                    <td><small><?php echo $row['Start_Date']; ?></small></td>
                    <td><small><?php echo $row['Expiry_Date']; ?></small></td>
                    <td>
                        <?php 
                            $now = date('Y-m-d H:i:s');
                            if($row['Status'] == 1 && $now >= $row['Start_Date'] && $now <= $row['Expiry_Date']) {
                                echo '<span class="status-active">Active</span>';
                            } else {
                                echo '<span class="status-expired">Expired / Inactive</span>';
                            }
                        ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5" style="text-align:center; color:#666;">No promotions found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>