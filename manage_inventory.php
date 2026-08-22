<?php
session_start();
$conn = new mysqli("localhost", "root", "", "bfresh_db");

// ---1. When adding a batch, prices are saved along with the data ---
if(isset($_POST['add_batch'])) {
    $p_id = $_POST['product_id'];
    $batch_no = $_POST['batch_no'];
    $exp_date = $_POST['expiry_date'];
    $qty = $_POST['qty'];
    $b_price = $_POST['buying_price']; // Newly added
    $s_price = $_POST['selling_price']; // Newly added

    //Data is sent to the buying_price and selling_price columns in the database.
    $conn->query("INSERT INTO inventory_batches (Product_ID, buying_price, selling_price, Batch_Number, Received_Date, Expiry_Date, Quantity_Received, Current_Stock) 
                  VALUES ('$p_id', '$b_price', '$s_price', '$batch_no', CURDATE(), '$exp_date', '$qty', '$qty')");
    
    $conn->query("UPDATE product SET Stock_Level = Stock_Level + $qty WHERE Product_ID = $p_id");
}

$batches = $conn->query("SELECT b.*, p.Name FROM inventory_batches b JOIN product p ON b.Product_ID = p.Product_ID ORDER BY b.Expiry_Date ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Bfresh | Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        .form-control, .form-select { border-radius: 8px; border: 1px solid #ddd; }
        .table { border-radius: 15px; overflow: hidden; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top py-3" style="background-color: #010b14; border-bottom: 3px solid #FFB300;">
        <div class="container">
            <a class="navbar-brand fw-bold text-warning fs-3" href="admin_dashboard.php">Bfresh Admin</a>
            <div class="collapse navbar-collapse" id="adminNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4 gap-3">
                    <li class="nav-item"><a class="nav-link active fw-bold text-white" href="admin_dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link active fw-bold text-white" href="manage_products.php">Products</a></li>
                    <li class="nav-item"><a class="nav-link active fw-bold text-white" href="orders.php">Orders</a></li>
                    <li class="nav-item"><a class="nav-link active fw-bold text-white" href="reviews.php">View Detailed Reports</a></li>
                     <li class="nav-item"><a class="nav-link active fw-bold text-white" href="manage_promotions.php">promotions</a></li>
                    
                     <li class="nav-item"><a class="nav-link active fw-bold text-white" href="logout.php">Logout</a></li>
                

                </ul>
                <div class="d-flex align-items-center gap-3">
                    <span class="text-light small">Hi, <?php echo $_SESSION['name'] ?? 'Admin'; ?>!</span>
                    <a href="logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-4">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="mb-4">
            <a href="admin_dashboard.php" class="btn btn-sm btn-dark rounded-pill px-4 shadow-sm">
                <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
            </a>
        </div>

        <div class="card p-4 shadow-sm border-0 rounded-4 mb-4">
            <h4 class="fw-bold mb-4 text-dark"><i class="fas fa-warehouse text-warning me-2"></i> Batch Logging</h4>
            <form method="POST" class="row g-3">
                <div class="col-md-4">
                    <label class="small fw-bold">Product</label>
                    <select name="product_id" class="form-select">
                        <?php $prods = $conn->query("SELECT Product_ID, Name FROM product"); while($p = $prods->fetch_assoc()) echo "<option value='".$p['Product_ID']."'>".$p['Name']."</option>"; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="small fw-bold">Batch No</label>
                    <input type="text" name="batch_no" class="form-control" placeholder="Ex: B001" required>
                </div>
                <div class="col-md-4">
                    <label class="small fw-bold">Expiry Date</label>
                    <input type="date" name="expiry_date" class="form-control" required>
                </div>
                
                <div class="col-md-3">
                    <label class="small fw-bold">Buying Price (Per Unit)</label>
                    <input type="number" step="0.01" name="buying_price" class="form-control" placeholder="0.00" required>
                </div>
                <div class="col-md-3">
                    <label class="small fw-bold">Selling Price (Per Unit)</label>
                    <input type="number" step="0.01" name="selling_price" class="form-control" placeholder="0.00" required>
                </div>
                <div class="col-md-3">
                    <label class="small fw-bold">Quantity</label>
                    <input type="number" name="qty" class="form-control" placeholder="0" required>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" name="add_batch" class="btn btn-warning w-100 fw-bold shadow-sm">Add New Batch</button>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover bg-white shadow-sm rounded-4 overflow-hidden text-center">
                <thead class="table-dark">
                    <tr>
                        <th>Product</th>
                        <th>Batch</th>
                        <th>Buy Price</th>
                        <th>Sell Price</th>
                        <th>Expiry</th>
                        <th>Stock Left</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody class="align-middle">
                    <?php while($b = $batches->fetch_assoc()): ?>
                    <tr>
                        <td class="fw-bold"><?php echo $b['Name']; ?></td>
                        <td>#<?php echo $b['Batch_Number']; ?></td>
                        <td>Rs. <?php echo number_format($b['buying_price'], 2); ?></td>
                        <td>Rs. <?php echo number_format($b['selling_price'], 2); ?></td>
                        <td><?php echo $b['Expiry_Date']; ?></td>
                        <td class="fw-bold"><?php echo $b['Current_Stock']; ?></td>
                        <td>
                            <?php 
                            $is_expired = strtotime($b['Expiry_Date']) < time();
                            echo $is_expired ? '<span class="badge bg-danger">Expired</span>' : '<span class="badge bg-success">Fresh</span>'; 
                            ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>