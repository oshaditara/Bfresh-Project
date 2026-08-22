<?php
session_start();
include 'functions.php'; // SMS function එක ඇති file එක මෙහිදී link කළ යුතුය

$conn = new mysqli("localhost", "root", "", "bfresh_db");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['place_order'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $address = $conn->real_escape_string($_POST['address']);
    $payment = $conn->real_escape_string($_POST['payment_method']);
    
    // පසු පිටුවට පෙන්වීමට Session එකේ විස්තර සේව් කිරීම
    $_SESSION['customer_details'] = [
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'address' => $address,
        'payment' => $payment
    ];

    $total_amount = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total_amount += ($item['price'] * $item['quantity']);
    }

    // ඇණවුම Database එකට ඇතුළත් කිරීම
    $sql = "INSERT INTO `order` (User_ID, Order_Date, Total_Amount, Delivery_Address, Order_Status) 
            VALUES (3, NOW(), '$total_amount', '$address', 'Order Placed')";
    
    if ($conn->query($sql)) {
        $_SESSION['last_order_id'] = $conn->insert_id;
        $order_id = $_SESSION['last_order_id'];

        foreach ($_SESSION['cart'] as $item) {
            $p_id = $item['id'];
            $qty = $item['quantity'];
            $price = $item['price'];
            $item_sql = "INSERT INTO order_item (Order_ID, Product_ID, Quantity, Price) 
                         VALUES ('$order_id', '$p_id', '$qty', '$price')";
            $conn->query($item_sql);
        }

        // --- SMS පණිවිඩය යැවීමේ කොටස ---
        $formattedName = ucwords(strtolower($name)); 
        $smsMessage = "Hi $formattedName, your Bfresh order #$order_id has been placed successfully! Total: Rs. " . number_format($total_amount, 2) . ". Thank you!";
        
        sendLiveSMS($phone, $smsMessage); // functions.php හි ඇති function එක කැඳවීම
        // --------------------------------

        header("Location: order_success.php");
        exit();
    }
}
?>