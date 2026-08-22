<?php
include 'db_config.php';

// 1. Update Review Status (Approve/Reject)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $status = ($_GET['action'] == 'approve') ? 'Approved' : 'Rejected';
    
    $update_sql = "UPDATE feedback SET Approval_Status = '$status' WHERE Feedback_ID = $id";
    if ($conn->query($update_sql) === TRUE) {
        header("Location: reviews.php");
        exit();
    }
}

// 2. Get All Reviews (Guest saha Register users-la okkoma ganna widiha)
// LEFT JOIN use karanne User_ID eka NULL (Guest) unath review eka pennanna
$reviews_query = "SELECT f.*, u.Name as RegName FROM feedback f 
                  LEFT JOIN user u ON f.User_ID = u.User_ID 
                  ORDER BY f.Date DESC";
$reviews = $conn->query($reviews_query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Customer Reviews - Bfresh</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Oyaage thiyena CSS okkoma methana thiyenawa */
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap');
        :root { --primary-yellow: #f1b300; --pure-white: #ffffff; --dark-black: #000000; --glass-black: rgba(20, 20, 20, 0.98); --border-color: rgba(255, 255, 255, 0.1); --transition: all 0.3s ease; }
        body { background-color: var(--dark-black); color: var(--pure-white); font-family: 'Plus Jakarta Sans', sans-serif; padding: 40px; }
        h1 { font-weight: 800; letter-spacing: -1.5px; text-transform: uppercase; color: var(--primary-yellow); }
        nav { margin-bottom: 30px; }
        nav a { color: rgba(255,255,255,0.6); text-decoration: none; font-weight: 600; margin-right: 20px; transition: var(--transition); }
        nav a:hover { color: var(--primary-yellow); }
        .table-container { background: var(--glass-black); padding: 25px; border-radius: 30px; border: 1px solid var(--border-color); margin-top: 20px; }
        table { width: 100%; border-collapse: separate; border-spacing: 0 10px; }
        th { text-align: left; padding: 15px; color: #555; font-size: 0.7rem; letter-spacing: 1px; text-transform: uppercase; }
        td { background: rgba(255, 255, 255, 0.03); padding: 20px 15px; border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); font-size: 0.85rem; vertical-align: middle; }
        td:first-child { border-left: 1px solid var(--border-color); border-radius: 20px 0 0 20px; }
        td:last-child { border-right: 1px solid var(--border-color); border-radius: 0 20px 20px 0; }
        .status-pill { padding: 5px 12px; border-radius: 50px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; }
        .rating-star { color: var(--primary-yellow); font-weight: 800; }
        .action-btn { text-decoration: none; font-weight: 700; font-size: 0.75rem; padding: 8px 15px; border-radius: 10px; transition: var(--transition); display: inline-block; }
        .btn-approve { background: var(--primary-yellow); color: black; }
        .btn-reject { border: 1px solid #ff4444; color: #ff4444; }
    </style>
</head>
<body>
    <div style="max-width: 1300px; margin: 0 auto;">
        <h1>Customer Feedback Management</h1>

        <nav>
            <a href="admin_dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a>
            <a href="manage_products.php">Manage Products</a>
            <a href="manage_inventory.php"><i class="fas fa-boxes"></i> manage_inventory</a>
           
            <a href="orders.php">Manage Orders</a>
             <a href="manage_promotions.php">promotions</a>
            <a href="logout.php">Logout</a>
        
        </nav>

        <div class="table-container shadow-lg">
            <h3>Pending & Approved Reviews</h3>
            <table>
                <thead>
                    <tr>
                        <th>Customer Details</th>
                        <th>Product Size</th>
                        <th>Rating</th>
                        <th>Comment</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $reviews->fetch_assoc()) { ?>
                    <tr>
                        <td>
                            <div style="font-weight: 800; color: var(--primary-yellow);">
                                <?php echo !empty($row['Name']) ? $row['Name'] : $row['RegName']; ?>
                            </div>
                            <div style="font-size: 0.7rem; color: #666;"><?php echo $row['Email']; ?></div>
                        </td>
                        <td>
                            <span style="color: #fff; font-weight: 600;"><?php echo $row['Size']; ?></span>
                        </td>
                        <td><span class="rating-star">★ <?php echo $row['Rating']; ?></span></td>
                        <td style="color: #ccc; font-style: italic; max-width: 250px; line-height: 1.4;">
                            "<?php echo $row['Comment']; ?>"
                        </td>
                        <td style="color: #666; font-size: 0.75rem;"><?php echo date('M d, Y', strtotime($row['Date'])); ?></td>
                        <td>
                            <?php 
                                if($row['Approval_Status'] == 'Approved') 
                                   echo "<span class='status-pill' style='background: rgba(46, 204, 113, 0.1); color: #2ecc71;'>Approved</span>";
                                elseif($row['Approval_Status'] == 'Rejected') 
                                    echo "<span class='status-pill' style='background: rgba(231, 76, 60, 0.1); color: #e74c3c;'>Rejected</span>";
                                else 
                                    echo "<span class='status-pill' style='background: rgba(241, 179, 0, 0.1); color: var(--primary-yellow);'>Pending</span>";
                            ?>
                        </td>
                        <td>
                            <div style="display: flex; gap: 8px;">
                                <?php if($row['Approval_Status'] != 'Approved') { ?>
                                    <a href="reviews.php?action=approve&id=<?php echo $row['Feedback_ID']; ?>" class="action-btn btn-approve">Approve</a>
                                <?php } ?>
                                <?php if($row['Approval_Status'] != 'Rejected') { ?>
                                    <a href="reviews.php?action=reject&id=<?php echo $row['Feedback_ID']; ?>" class="action-btn btn-reject">Reject</a>
                                <?php } ?>
                            </div>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>