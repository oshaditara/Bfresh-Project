<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$conn = new mysqli("localhost", "root", "", "bfresh_db");
$uid = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bfresh | Order History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #fdfcf0; font-family: 'Outfit', sans-serif; }
        .card { border-radius: 20px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .status-badge { padding: 5px 15px; border-radius: 50px; font-weight: 700; font-size: 0.8rem; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="d-flex align-items-center mb-4">
            <a href="customer_dashboard.php" class="btn btn-dark rounded-circle me-3"><i class="fas fa-arrow-left"></i></a>
            <h2 class="fw-bold mb-0">My Order History</h2>
        </div>

        <div class="card p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr><th>Date</th><th>Total Amount</th><th>Status</th><th>Delivery Partner</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php
                        $orders = $conn->query("SELECT * FROM `order` WHERE User_ID = $uid ORDER BY Order_Date DESC");
                        while($row = $orders->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?php echo $row['Order_Date']; ?></td>
                            <td class="fw-bold">Rs. <?php echo number_format($row['Total_Amount'], 2); ?></td>
                            <td><span class="status-badge bg-warning"><?php echo $row['Order_Status']; ?></span></td>
                            <td class="text-muted"><?php echo $row['Delivery_Partner'] ?: 'Processing...'; ?></td>
                            <td><a href="reorder_process.php?order_id=<?php echo $row['Order_ID']; ?>" class="btn btn-sm btn-dark rounded-pill px-3">Re-order</a></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>