<?php
session_start();

include 'functions.php'; // Linking a file with the SMS function

$conn = new mysqli("localhost", "root", "", "bfresh_db");


if (isset($_GET['new_order']) && $_GET['new_order'] == '1') {
    unset($_SESSION['cart']);
}

if ($conn->connect_error) { 
    die("Connection failed: " . $conn->connect_error); 
}

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: catalog.php");
    exit();
}

$total_price = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout | Bfresh</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap');
        
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: #ffffff; 
            color: #1a1a1a; 
        }

        .checkout-input {
            background-color: #f9f9f9;
            border: 1px solid #e5e5e5;
            color: #1a1a1a;
            transition: all 0.3s ease;
        }

        .checkout-input:focus {
            border-color: #fbbf24;
            background-color: #fff;
            outline: none;
            box-shadow: 0 0 0 4px rgba(251, 191, 36, 0.1);
        }

        .summary-card {
            background-color: #fdfdfd;
            border: 1px solid #f0f0f0;
        }

        .custom-footer {
            background: #f9f9f9;
            padding: 40px 20px;
            text-align: center;
            border-top: 1px solid #eee;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #666;
            font-weight: 700;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }

        .back-btn:hover {
            color: #000;
            transform: translateX(-5px);
        }
    </style>
</head>
<body class="min-h-screen">
    <div class="max-w-6xl mx-auto px-6 py-12">
        
        <a href="catalog.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Cart
        </a>

        <form action="process_order.php" method="POST" class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            
            <div class="space-y-8">
                <div>
                    <h2 class="text-4xl font-black uppercase tracking-tighter mb-2 text-black">Checkout</h2>
                    <p class="text-gray-500">Enter your details to complete your order.</p>
                </div>

                <div class="space-y-4">
                    <input type="text" name="name" placeholder="Full Name" required class="w-full checkout-input p-5 rounded-2xl">
                    <input type="email" name="email" placeholder="Email Address" required class="w-full checkout-input p-5 rounded-2xl">
                    <input type="text" name="phone" placeholder="Phone Number" required class="w-full checkout-input p-5 rounded-2xl">
                    <textarea name="address" placeholder="Delivery Address" required class="w-full checkout-input p-5 rounded-2xl h-32"></textarea>
                </div>

                <div class="bg-gray-50 p-6 rounded-3xl border border-gray-100">
                    <h3 class="font-bold mb-4 flex items-center gap-2 text-black">
                        <i class="fas fa-credit-card text-yellow-500"></i> Select Payment Method
                    </h3>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="payment_method" value="Cash on Delivery" checked class="peer sr-only">
                            <div class="p-4 border border-gray-200 rounded-2xl peer-checked:border-yellow-500 peer-checked:bg-yellow-50 text-center transition-all hover:border-yellow-200">
                                <i class="fas fa-truck mb-2 block text-gray-600"></i>
                                <span class="text-sm font-bold text-gray-800">Cash on Delivery</span>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="payment_method" value="Online Payment" class="peer sr-only">
                            <div class="p-4 border border-gray-200 rounded-2xl peer-checked:border-yellow-500 peer-checked:bg-yellow-50 text-center transition-all hover:border-yellow-200">
                                <i class="fas fa-globe mb-2 block text-gray-600"></i>
                                <span class="text-sm font-bold text-gray-800">Online Payment</span>
                            </div>
                        </label>
                    </div>
                </div>

                <button type="submit" name="place_order" class="w-full bg-yellow-400 text-black py-6 rounded-3xl font-black uppercase tracking-widest hover:bg-yellow-500 transition-all shadow-lg shadow-yellow-400/20">
                    Place Order Now
                </button>
            </div>

            <div class="summary-card p-8 rounded-[40px] h-fit sticky top-12 shadow-sm">
                <h3 class="text-xl font-bold mb-8 text-black border-b border-gray-100 pb-4">Order Summary</h3>
                <div class="space-y-6 mb-8">
                    <?php 
                    foreach ($_SESSION['cart'] as $item): 
                        $item_total = $item['price'] * $item['quantity'];
                        $total_price += $item_total;
                    ?>
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-yellow-50 rounded-xl flex items-center justify-center">
                                <i class="fas fa-wine-glass-alt text-yellow-600"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-sm text-gray-800"><?php echo htmlspecialchars($item['name']); ?></h4>
                                <p class="text-xs text-gray-500">Qty: <?php echo $item['quantity']; ?></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="text-sm font-bold text-black">Rs. <?php echo number_format($item_total, 2); ?></div>
                            <a href="remove_item.php?id=<?php echo $item['id']; ?>" class="text-red-500 hover:text-red-700 transition-colors">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="border-t border-gray-100 pt-6">
                    <div class="flex justify-between text-2xl font-black uppercase tracking-tighter text-black">
                        <span>Total</span>
                        <span class="text-yellow-600">Rs. <?php echo number_format($total_price, 2); ?></span>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <footer class="custom-footer">
        <div class="text-2xl font-black text-black mb-2">B<span class="text-yellow-500">fresh.</span></div>
        <p class="text-gray-500 text-sm">Pure Nature, Bottled Daily. Handcrafted with passion in Sri Lanka.</p>
    </footer>
</body>
</html>