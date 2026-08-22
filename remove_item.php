<?php
session_start();

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Array Key එක ID එක නිසා කෙලින්ම unset කරන්න පුළුවන්
    if (isset($_SESSION['cart'][$id])) {
        unset($_SESSION['cart'][$id]);
    }
}

header("Location: guest_checkout.php");
exit();
?>