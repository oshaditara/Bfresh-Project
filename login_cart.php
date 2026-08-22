<?php
session_start();

// 1. User log wela naththam login ekata yawanna
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "bfresh_db");

// --- Adding an ITEM to the CART (Add to Cart) ---
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    if (!isset($_SESSION['login_cart'])) {
        $_SESSION['login_cart'] = array();
    }

    if (isset($_SESSION['login_cart'][$product_id])) {
        $_SESSION['login_cart'][$product_id] += $quantity;
    } else {
        $_SESSION['login_cart'][$product_id] = $quantity;
    }

    header("Location: view_cart.php"); 
    exit();
}

// --- UPDATE QUANTITY in CART when pressing (+ or -) ---
if (isset($_GET['action']) && $_GET['action'] == 'update') {
    $product_id = $_GET['id'];
    $change = (int)$_GET['qty'];

    if (isset($_SESSION['login_cart'][$product_id])) {
        $_SESSION['login_cart'][$product_id] += $change;

        // Remove the item if the quantity is less than 1
        if ($_SESSION['login_cart'][$product_id] < 1) {
            unset($_SESSION['login_cart'][$product_id]);
        }
    }
    header("Location: view_cart.php");
    exit();
}

// --- Removing an ITEM from the CART (Delete / Remove) ---
if (isset($_GET['action']) && $_GET['action'] == 'remove') {
    $product_id = $_GET['id'];

    if (isset($_SESSION['login_cart'][$product_id])) {
        unset($_SESSION['login_cart'][$product_id]);
    }
    header("Location: view_cart.php");
    exit();
}

// If nothing, send it to the Dashboard
header("Location: customer_dashboard.php");
exit();
?>