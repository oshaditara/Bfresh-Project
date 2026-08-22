<?php
session_start();
$conn = new mysqli("localhost", "root", "", "bfresh_db");

if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

if (isset($_POST['place_order'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $address = $conn->real_escape_string($_POST['address']);
    $total = $_POST['total_amount'];

    // 1. Inserting data into the User table
    $user_query = "INSERT INTO user (Name, Email, Phone, Address, Role) VALUES ('$name', '$email', '$phone', '$address', 'Guest')";
    
    if ($conn->query($user_query)) {
        $user_id = $conn->insert_id;
        $_SESSION['user_id'] = $user_id;

        // 2. Entering the Order (According to your Table Columns)
        $order_query = "INSERT INTO `order` (User_ID, Total_Amount, Delivery_Address, Order_Status) 
                        VALUES ($user_id, $total, '$address', 'Pending')";
        
        if ($conn->query($order_query)) {
            $order_id = $conn->insert_id;

            // 3. Emptying the Cart
            unset($_SESSION['cart']);

            // 4. Successfully completed and sent to the Success page
            header("Location: order_success.php?order_id=" . $order_id);
            exit();
        } else {
            echo "Order Error: " . $conn->error;
        }
    } else {
        echo "User Error: " . $conn->error;
    }
} else {
    header("Location: guest_checkout.php");
    exit();
}
?>