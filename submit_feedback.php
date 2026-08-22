<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "bfresh_db");

if ($conn->connect_error) { 
    die("Connection failed: " . $conn->connect_error); 
}

if (isset($_POST['submit_review'])) {
    // Form data ganna (Security ekka)
    $product_id = $conn->real_escape_string($_POST['product_id']);
    $selected_size = $conn->real_escape_string($_POST['product_size']);
    $customer_name = $conn->real_escape_string($_POST['customer_name']);
    $customer_email = $conn->real_escape_string($_POST['customer_email']);
    $rating = $conn->real_escape_string($_POST['rating']);
    $comment = $conn->real_escape_string($_POST['comment']);
    $date = date('Y-m-d H:i:s');

    // 1. Email eka register user kenekgeda balanna
    $user_check = $conn->query("SELECT User_ID FROM user WHERE Email = '$customer_email'");
    
    if ($user_check->num_rows > 0) {
        $user_row = $user_check->fetch_assoc();
        $user_id = $user_row['User_ID'];
    } else {
        $user_id = "NULL"; // Guest user kenek nam NULL
    }

    // 2. Insert Query (Aluth columns ekka)
    $sql = "INSERT INTO feedback (Product_ID, User_ID, Name, Email, Size, Rating, Comment, Date, Approval_Status) 
            VALUES ('$product_id', $user_id, '$customer_name', '$customer_email', '$selected_size', '$rating', '$comment', '$date', 'Pending')";

    if ($conn->query($sql)) {
        // 3. Email Notification
        $to = $customer_email;
        $subject = "B-FRESH Feedback Received!";
        $message = "
        <html>
        <body style='font-family: sans-serif;'>
            <h2 style='color: #ef4444;'>Hi $customer_name,</h2>
            <p>Thanks for reviewing our <b>$selected_size</b> bottle!</p>
            <p>Once approved by admin, your review will be live.</p>
            <hr>
            <p>Best Regards,<br>B-FRESH Team</p>
        </body>
        </html>";

        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $headers .= "From: no-reply@bfresh.lk\r\n";

        mail($to, $subject, $message, $headers);

        echo "<script>
                alert('Review submitted! Size: $selected_size. It will be live after approval.');
                window.location.href = 'passion.php?id=$product_id';
              </script>";
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    header("Location: catalog.php");
    exit();
}
?>