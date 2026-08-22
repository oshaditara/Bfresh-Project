<?php
include 'db_config.php';

// 1. Order Status එක සහ Item-wise Batches update කිරීම
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    $partner = $_POST['partner'];
    $item_batches = $_POST['item_batches'] ?? []; 

    // මුලින්ම Order table එකේ status සහ partner update කරනවා
    $sql = "UPDATE `order` SET Order_Status = '$status', Delivery_Partner = '$partner' WHERE Order_ID = $order_id";
    
    if ($conn->query($sql) === TRUE) {
        // දැන් හැම Product එකකටම අදාළ බැච් එක update කරනවා
        foreach ($item_batches as $order_item_id => $batch_id) {
            if (!empty($batch_id)) {
                $conn->query("UPDATE order_item SET Batch_ID = '$batch_id' WHERE OrderItem_ID = $order_item_id");

                // Status එක 'Delivered' නම් පමණක් stock අඩු කරනවා
                if ($status == 'Delivered') {
                    $item_res = $conn->query("SELECT Quantity FROM order_item WHERE OrderItem_ID = $order_item_id");
                    $item_data = $item_res->fetch_assoc();
                    $qty = $item_data['Quantity'];
                    $conn->query("UPDATE inventory_batches SET Current_Stock = Current_Stock - $qty WHERE Batch_ID = $batch_id");
                }
            }
        }
        echo "<script>alert('Order #$order_id updated!'); window.location='orders.php';</script>";
    }
}

// ඕඩර්ස් වර්ගීකරණය
$pending_orders = $conn->query("SELECT * FROM `order` WHERE Order_Status NOT IN ('Delivered', 'Cancelled') ORDER BY Order_Date DESC");
$delivered_orders = $conn->query("SELECT * FROM `order` WHERE Order_Status = 'Delivered' ORDER BY Order_Date DESC");
$cancelled_orders = $conn->query("SELECT * FROM `order` WHERE Order_Status = 'Cancelled' ORDER BY Order_Date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bfresh | Manage Orders</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --primary: #ffcc00; --bg: #8f8d7b; --dark: #010b14; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); margin: 0; padding: 20px; }
        .container { max-width: 1100px; margin: auto; }
        .section-title { border-left: 5px solid var(--primary); padding-left: 15px; margin: 30px 0 15px; color: var(--dark); font-weight: bold; }
        
        .order-card { background: #fff; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #e0e0e0; }
        .order-header { display: flex; justify-content: space-between; border-bottom: 1px dashed #ddd; padding-bottom: 10px; margin-bottom: 10px; }
        
        .item-list { width: 100%; border-collapse: collapse; margin: 10px 0; background: #fafafa; }
        .item-list th { text-align: left; background: #eee; padding: 10px; font-size: 0.85rem; }
        .item-list td { padding: 10px; border-bottom: 1px solid #eee; font-size: 0.9rem; }

        .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .status-Processing { background: #fff3cd; color: #856404; }
        .status-Dispatched { background: #cce5ff; color: #004085; }
        .status-Delivered { background: #d4edda; color: #155724; }
        .status-Cancelled { background: #f8d7da; color: #721c24; }

        .batch-select { padding: 5px; border-radius: 5px; border: 1px solid var(--primary); font-size: 0.85rem; width: 100%; }
        .controls { display: flex; flex-wrap: wrap; gap: 15px; background: #f9f9f9; padding: 15px; border-radius: 8px; margin-top: 15px; align-items: flex-end; }
        .control-group { flex: 1; min-width: 150px; }
        label { font-size: 0.8rem; font-weight: bold; display: block; margin-bottom: 5px; }
        
        .btn-update { background: var(--dark); color: var(--primary); border: none; padding: 10px 25px; border-radius: 6px; font-weight: bold; cursor: pointer; }

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

<div class="container">
    <h2><i class="fas fa-tasks"></i> Order Management</h2>

     <nav>
        <a href="admin_dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a>
           <a href="manage_inventory.php"><i class="fas fa-boxes"></i> manage_inventory</a>
            <a href="manage_batches.php"><i class="fas fa-boxes"></i> Stock batches</a>
             <a href="manage_products.php">Manage Products</a>
        <a href="reviews.php">View Detailed Reports</a>
        <a href="manage_promotions.php">promotions</a>
        <a href="logout.php">Logout</a>
        </nav>

    <h3 class="section-title">Active Orders</h3>
    <?php while($order = $pending_orders->fetch_assoc()): 
        $oid = $order['Order_ID']; ?>
        <div class="order-card">
            <form method="POST">
                <input type="hidden" name="order_id" value="<?php echo $oid; ?>">
                <div class="order-header">
                    <span><strong>Order #<?php echo $oid; ?></strong> | <?php echo $order['Order_Date']; ?></span>
                    <span class="status-badge status-<?php echo $order['Order_Status']; ?>"><?php echo $order['Order_Status']; ?></span>
                </div>
                <p><i class="fas fa-map-marker-alt"></i> <?php echo $order['Delivery_Address']; ?></p>
                
                <table class="item-list">
                    <thead>
                        <tr><th>Product</th><th>Qty</th><th width="40%">Assign Batch</th><th>Subtotal</th></tr>
                    </thead>
                    <tbody>
                        <?php 
                        $items = $conn->query("SELECT oi.*, p.Name FROM order_item oi JOIN product p ON oi.Product_ID = p.Product_ID WHERE oi.Order_ID = $oid");
                        while($item = $items->fetch_assoc()): 
                            $pid = $item['Product_ID'];
                        ?>
                            <tr>
                                <td><?php echo $item['Name']; ?></td>
                                <td>x<?php echo $item['Quantity']; ?></td>
                                <td>
                                    <select name="item_batches[<?php echo $item['OrderItem_ID']; ?>]" class="batch-select" required>
                                        <option value="">-- Select Batch --</option>
                                        <?php 
                                        $batch_res = $conn->query("SELECT Batch_ID, Batch_Number, Current_Stock FROM inventory_batches WHERE Product_ID = $pid AND Current_Stock > 0");
                                        while($b = $batch_res->fetch_assoc()):
                                            $sel = ($item['Batch_ID'] == $b['Batch_ID']) ? 'selected' : '';
                                            echo "<option value='".$b['Batch_ID']."' $sel>Batch #".$b['Batch_Number']." (Stk: ".$b['Current_Stock'].")</option>";
                                        endwhile; ?>
                                    </select>
                                </td>
                                <td>Rs. <?php echo number_format($item['Quantity'] * $item['Price'], 2); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <div class="controls">
                    <div class="control-group">
                        <label>Delivery Partner</label>
                        <input type="text" name="partner" value="<?php echo $order['Delivery_Partner']; ?>" placeholder="Driver Name">
                    </div>
                    <div class="control-group">
                        <label>Change Status</label>
                        <select name="status">
                            <option value="Processing" <?php if($order['Order_Status']=='Processing') echo 'selected'; ?>>Processing</option>
                            <option value="Dispatched" <?php if($order['Order_Status']=='Dispatched') echo 'selected'; ?>>Dispatched</option>
                            <option value="Delivered">Delivered</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>
                    <button type="submit" name="update_status" class="btn-update">UPDATE</button>
                </div>
            </form>
        </div>
    <?php endwhile; ?>

    <h3 class="section-title" style="border-color: #28a745;">Successfully Delivered</h3>
    <div class="order-card" style="padding:0; overflow:hidden;">
        <table class="item-list" style="margin:0;">
            <thead style="background: #28a745; color: white;">
                <tr><th>Order ID</th><th>Customer Address</th><th>Total</th><th>Partner</th></tr>
            </thead>
            <tbody>
                <?php while($d = $delivered_orders->fetch_assoc()): ?>
                <tr>
                    <td>#<?php echo $d['Order_ID']; ?></td>
                    <td><?php echo $d['Delivery_Address']; ?></td>
                    <td>Rs. <?php echo number_format($d['Total_Amount'], 2); ?></td>
                    <td><?php echo $d['Delivery_Partner']; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <h3 class="section-title" style="border-color: #dc3545;">Cancelled Orders</h3>
    <div class="order-card" style="padding:0; overflow:hidden;">
        <table class="item-list" style="margin:0;">
            <thead style="background: #dc3545; color: white;">
                <tr><th>Order ID</th><th>Date</th><th>Total</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php while($c = $cancelled_orders->fetch_assoc()): ?>
                <tr>
                    <td>#<?php echo $c['Order_ID']; ?></td>
                    <td><?php echo $c['Order_Date']; ?></td>
                    <td>Rs. <?php echo number_format($c['Total_Amount'], 2); ?></td>
                    <td><span class="status-badge status-Cancelled">Cancelled</span></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>