<?php
session_start();
include 'db_config.php';

if (!isset($_SESSION['last_order_id']) || !isset($_SESSION['customer_details'])) {
    header("Location: catalog.php");
    exit();
}

$order_id = $_SESSION['last_order_id'];
$customer = $_SESSION['customer_details']; // Session එකෙන් ගත්තා

$order_res = $conn->query("SELECT * FROM `order` WHERE Order_ID = $order_id");
$order_data = $order_res->fetch_assoc();
$total_price = $order_data['Total_Amount'] ?? 0;

$items_res = $conn->query("SELECT oi.*, p.Name FROM order_item oi JOIN product p ON oi.Product_ID = p.Product_ID WHERE oi.Order_ID = $order_id");
$cart_items = [];
while($row = $items_res->fetch_assoc()){
    $cart_items[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Success | Bfresh</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #ffffff; color: white; }
    </style>
</head>
<body class="bg-black text-white min-h-screen">
    <div class="max-w-lg mx-auto px-6 py-12">
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-yellow-400 rounded-full mb-6">
                <i class="fas fa-check text-black text-2xl"></i>
            </div>
            <h1 class="text-3xl font-black uppercase tracking-tighter mb-2">Order Confirmed!</h1>
            <p class="text-gray-400 text-sm">Order ID: <span class="text-white font-bold">#<?php echo $order_id; ?></span></p>
        </div>

        <div class="bg-[#0f0f0f] rounded-[35px] p-8 border border-white/5 shadow-2xl space-y-6">
            <div class="border-b border-white/10 pb-6">
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-4">Customer Info</h3>
                <div class="grid grid-cols-1 gap-3 text-sm">
                    <p><span class="text-gray-500">Name:</span> <?php echo htmlspecialchars($customer['name']); ?></p>
                    <p><span class="text-gray-500">Email:</span> <?php echo htmlspecialchars($customer['email']); ?></p>
                    <p><span class="text-gray-500">Phone:</span> <?php echo htmlspecialchars($customer['phone']); ?></p>
                    <p><span class="text-gray-500">Address:</span> <?php echo htmlspecialchars($customer['address']); ?></p>
                    <p><span class="text-gray-500">Payment:</span> <span class="text-yellow-400 font-bold"><?php echo htmlspecialchars($customer['payment']); ?></span></p>
                </div>
            </div>

            <div>
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-4">Order Items</h3>
                <div class="space-y-3">
                    <?php foreach ($cart_items as $item): ?>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-400"><?php echo $item['Quantity']; ?>x <?php echo $item['Name']; ?></span>
                        <span class="font-bold">Rs. <?php echo number_format($item['Price'] * $item['Quantity'], 2); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="border-t border-white/10 pt-6">
                <div class="flex justify-between items-center mb-6">
                    <span class="text-lg font-black uppercase">Total</span>
                    <span class="text-2xl font-black text-yellow-400">Rs. <?php echo number_format($total_price, 2); ?></span>
                </div>
                <button onclick="window.location.href='catalog.php'" class="w-full bg-white text-black py-5 rounded-2xl font-black uppercase tracking-widest hover:bg-yellow-400 transition-all">
                    Done
                </button>
            </div>
        </div>
    </div>
</body>
</html>