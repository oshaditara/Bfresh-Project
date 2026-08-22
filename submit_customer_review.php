<?php
session_start();
$conn = new mysqli("localhost", "root", "", "bfresh_db");

if (isset($_POST['product_id']) && isset($_SESSION['user_id'])) {
    $product_id = $conn->real_escape_string($_POST['product_id']);
    $user_id = $_SESSION['user_id'];
    $customer_name = $conn->real_escape_string($_POST['customer_name']);
    $customer_email = $conn->real_escape_string($_POST['customer_email']);
    $selected_size = $conn->real_escape_string($_POST['product_size']);
    $rating = $conn->real_escape_string($_POST['rating']);
    $comment = $conn->real_escape_string($_POST['comment']);
    $date = date('Y-m-d H:i:s');

    $sql = "INSERT INTO feedback (Product_ID, User_ID, Name, Email, Size, Rating, Comment, Date, Approval_Status) 
            VALUES ('$product_id', '$user_id', '$customer_name', '$customer_email', '$selected_size', '$rating', '$comment', '$date', 'Pending')";

    if ($conn->query($sql)) {
        echo "success"; // Redirect wenne na, success kiyala yawannne
    } else {
        echo "error";
    }
}
?>