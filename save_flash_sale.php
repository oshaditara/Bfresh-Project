<?php
session_start();

// User Login වී ඇතිදැයි පරීක්ෂා කිරීම
if (!isset($_SESSION['name'])) {
    header("Location: login.php");
    exit();
}

include 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Form එකෙන් එවන දත්ත ලබා ගැනීම
    $product_id   = $_POST['product_id'];
    $discount     = $_POST['discount'];
    $promo_type   = $_POST['promo_type'];
    $start_date   = $_POST['start_date'];
    $expiry_date  = $_POST['expiry_date'];
    
    // Flash Sale එකක් සඳහා අහඹු Promo Code එකක් සෑදීම (උදා: FLASH582)
    $promo_code = "FLASH" . rand(100, 999);

    // SQL Injection වලින් ආරක්ෂා වීමට දත්ත පිරිසිදු කිරීම (Sanitize)
    $product_id  = mysqli_real_escape_string($conn, $product_id);
    $discount    = mysqli_real_escape_string($conn, $discount);
    $promo_type  = mysqli_real_escape_string($conn, $promo_type);
    $start_date  = mysqli_real_escape_string($conn, $start_date);
    $expiry_date = mysqli_real_escape_string($conn, $expiry_date);

    // Database එකට දත්ත ඇතුළත් කිරීමේ Query එක
    // Status එක 1 ලෙස දමන්නේ එය Active බව හැඟවීමටයි
    $sql = "INSERT INTO promotions (Product_ID, Promo_Code, Discount_Percent, Start_Date, Expiry_Date, Promo_Type, Status) 
            VALUES ('$product_id', '$promo_code', '$discount', '$start_date', '$expiry_date', '$promo_type', 1)";

    if ($conn->query($sql) === TRUE) {
        // සාර්ථකව ඇතුළත් වුනා නම් Dashboard එකට යවන්න
        header("Location: admin_dashboard.php?success=flash_added");
        exit();
    } else {
        // වැරැද්දක් වුනා නම් එය පෙන්වන්න
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
} else {
    // කෙලින්ම මෙම පිටුවට ඒමට උත්සාහ කළහොත් ආපහු හරවා යැවීම
    header("Location: manage_promotions.php");
    exit();
}

$conn->close();
?>