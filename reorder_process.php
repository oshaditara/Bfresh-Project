<?php
session_start();

// 1. User log wela naththan login ekata yawanna[cite: 1, 2]
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 2. Database connection eka[cite: 1, 2]
$conn = new mysqli("localhost", "root", "", "bfresh_db");

// Connection error ekak awoth
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 3. URL eken ena Order ID eka ganna
if (isset($_GET['order_id'])) {
    $order_id = intval($_GET['order_id']); // Security ekata integer karanna
    
    // 4. 'order_item' table eken parana items tika select karanna
    // Oyaage database screenshot eka anuwa table name eka 'order_item'[cite: 3]
    $query = "SELECT Product_ID, Quantity FROM order_item WHERE Order_ID = $order_id";
    $items_res = $conn->query($query);

    if ($items_res && $items_res->num_rows > 0) {
        
        // 5. Parana cart eka clear karanna
        $_SESSION['login_cart'] = []; 

        while ($item = $items_res->fetch_assoc()) {
            $p_id = $item['Product_ID'];
            $qty = $item['Quantity'];
            
            // 6. Session ekata parana items load karanna[cite: 2]
            $_SESSION['login_cart'][$p_id] = $qty;
        }
        
        // 7. Kelinma checkout page ekata yawanna[cite: 2]
        header("Location: view_cart.php"); // Naththan oyaage checkout page ekata redirect karanna
        exit();
    } else {
        // Order ekak naththan profile ekata yawanna
        header("Location: profile.php?error=no_items");
        exit();
    }
} else {
    header("Location: profile.php");
    exit();
}
?>