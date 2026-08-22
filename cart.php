<?php
session_start();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $name = $_POST['selected_name'];
    $price = $_POST['selected_price'];
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    // Product ID එක Key එක විදිහට පාවිච්චි කිරීමෙන් පහසුවෙන් Item එක හොයාගන්න පුළුවන්
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = [
            'id' => $product_id,
            'name' => $name,
            'price' => $price,
            'quantity' => $quantity
        ];
    }

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        echo json_encode(['status' => 'success', 'cart_count' => count($_SESSION['cart'])]);
        exit;
    }

    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}
?>