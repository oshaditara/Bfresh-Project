<?php
session_start();
include 'functions.php'; // SMS function එක පාවිච්චි කිරීමට මෙය අනිවාර්ය වේ

if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); 
    exit(); 
}

$conn = new mysqli("localhost", "root", "", "bfresh_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['place_order'])) {
    $uid = $_SESSION['user_id'];
    $total_amount = $_POST['total_amount'];
    $delivery_address = $conn->real_escape_string($_POST['delivery_address']);
    $order_date = date("Y-m-d H:i:s");
    $status = "Pending";

    // 1. Order table එකට ඇණවුම ඇතුළත් කිරීම
    $order_query = "INSERT INTO `order` (User_ID, Order_Date, Total_Amount, Order_Status, Delivery_Address) 
                    VALUES ('$uid', '$order_date', '$total_amount', '$status', '$delivery_address')";
    
    if ($conn->query($order_query)) {
        $order_id = $conn->insert_id;

        // 2. ඇණවුම් කළ අයිතම (Order Items) එකින් එක ඇතුළත් කිරීම
        if (isset($_SESSION['login_cart'])) {
            foreach ($_SESSION['login_cart'] as $p_id => $qty) {
                
                // එක් එක් බීම වර්ගයේ මිල Database එකෙන් ලබා ගැනීම (Error එක වළක්වා ගැනීමට)
                $res_product = $conn->query("SELECT Price FROM product WHERE Product_ID = $p_id");
                if ($res_product && $res_product->num_rows > 0) {
                    $product_data = $res_product->fetch_assoc();
                    $unit_price = $product_data['Price'];

                    $item_sql = "INSERT INTO order_item (Order_ID, Product_ID, Quantity, Price) 
                                 VALUES ('$order_id', '$p_id', '$qty', '$unit_price')";
                    $conn->query($item_sql);
                }
            }
        }

        // 3. Loyalty Points ගණනය කිරීම සහ යාවත්කාලීන කිරීම[cite: 4]
        $order_count_res = $conn->query("SELECT COUNT(*) as total_orders FROM `order` WHERE User_ID = $uid");
        $order_count_data = $order_count_res->fetch_assoc();
        $total_orders = $order_count_data['total_orders'];

        $earned_points = floor($total_amount / 100);
        if ($total_orders > 5) {
            $earned_points += 10;
        }

        $conn->query("UPDATE customer SET Loyalty_Points = Loyalty_Points + $earned_points WHERE Customer_ID = $uid");

       
           // 4. පරිශීලකයාට SMS එකක් යැවීම
$user_res = $conn->query("SELECT Name, Phone_Number FROM user WHERE User_ID = $uid");
if ($user_res && $user_res->num_rows > 0) {
    $user_data = $user_res->fetch_assoc();
    $name = $user_data['Name'];
    $phone = $user_data['Phone_Number'];

    $formattedName = ucwords(strtolower($name)); 
    
    // පණිවිඩය මුලින්ම සකස් කරගත යුතුය
    $smsMessage = "Hi $formattedName, your Bfresh order #$order_id was placed successfully! Total: Rs. " . number_format($total_amount, 2) . ". Thank you!";
    
    // ඉන්පසු function එක කැඳවන්න
    sendLiveSMS($phone, $smsMessage); 
}
        // 5. Cart එක හිස් කර සාර්ථක වූ පිටුවට යොමු කිරීම
        unset($_SESSION['login_cart']);
        header("Location: track_order.php?order_id=$order_id");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>