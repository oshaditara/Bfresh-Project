<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$conn = new mysqli("localhost", "root", "", "bfresh_db");

$cart_data = [];
$grand_total = 0;

if (isset($_SESSION['login_cart']) && !empty($_SESSION['login_cart'])) {
    foreach ($_SESSION['login_cart'] as $id => $qty) {
        $res = $conn->query("SELECT * FROM product WHERE Product_ID = $id");
        if ($res && $res->num_rows > 0) {
            $item = $res->fetch_assoc();
            $item['qty'] = $qty;
            $item['subtotal'] = $item['Price'] * $qty;
            $grand_total += $item['subtotal'];
            $cart_data[] = $item;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bfresh | Checkout</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #fafafa; }
        .payment-option:checked + label { border-color: #ffd700; background: #fffdf2; box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
          /* --- Footer --- */
        .footer-bfresh { background: var(--dark); color: #fff; padding: 120px 0 50px; }

        @media (max-width: 992px) {
            .split-hero { height: auto; }
            .slider-column { height: 50vh; }
            .text-column { padding: 80px 30px; text-align: center; }
        }
    </style>
</head>
<body class="antialiased">

    <nav class="bg-white py-6 border-b border-gray-100 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 flex justify-between items-center">
            <a href="customer_dashboard.php" class="text-sm font-bold uppercase tracking-widest text-gray-400 hover:text-black transition">
                <i class="fas fa-arrow-left mr-2"></i> Back
            </a>
            <div class="text-2xl font-black italic">B-FRESH<span class="text-yellow-400">.</span></div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-6 py-16">
        <form action="place_order_process.php" method="POST">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-16">
                
                <div class="lg:col-span-8">
                    <h2 class="text-4xl font-extrabold tracking-tighter mb-10">Review Your <span class="text-gray-300">Bag.</span></h2>

                    <?php if (!empty($cart_data)): ?>
                        <?php foreach ($cart_data as $item): ?>
                            <div class="bg-white p-8 rounded-[35px] mb-6 flex items-center gap-8 border border-gray-100 shadow-sm">
                                <div class="w-20 h-20 bg-gray-50 rounded-2xl flex items-center justify-center p-4">
                                    <img src="<?php echo $item['image']; ?>" class="h-full object-contain">
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-lg font-bold"><?php echo htmlspecialchars($item['Name']); ?></h3>
                                    <p class="text-gray-400 text-[10px] font-bold uppercase tracking-widest mt-1">Rs. <?php echo number_format($item['Price'], 2); ?></p>
                                </div>
                                
                                <div class="flex items-center bg-gray-50 rounded-xl p-1 border border-gray-100">
                                    <a href="login_cart.php?action=update&id=<?php echo $item['Product_ID']; ?>&qty=-1" class="w-8 h-8 flex items-center justify-center hover:bg-white rounded-lg transition">-</a>
                                    <span class="w-10 text-center font-bold text-sm"><?php echo $item['qty']; ?></span>
                                    <a href="login_cart.php?action=update&id=<?php echo $item['Product_ID']; ?>&qty=1" class="w-8 h-8 flex items-center justify-center hover:bg-white rounded-lg transition">+</a>
                                </div>

                                <div class="text-right min-w-[120px]">
                                    <p class="font-black text-lg">Rs. <?php echo number_format($item['subtotal'], 2); ?></p>
                                </div>
                                <a href="login_cart.php?action=remove&id=<?php echo $item['Product_ID']; ?>" class="text-gray-300 hover:text-red-500 transition px-2"><i class="fas fa-trash-can"></i></a>
                            </div>
                        <?php endforeach; ?>

                                                <div class="mt-12 mb-12">
                            <h3 class="text-xl font-bold mb-6">Delivery Address</h3>
                            <div class="bg-white p-8 rounded-[30px] border border-gray-100 shadow-sm">
                                <label class="text-[10px] font-black uppercase tracking-[3px] text-gray-400 mb-3 block">Shipping Location</label>
                                <textarea 
                                    name="delivery_address" 
                                    rows="3" 
                                    class="w-full bg-gray-50 border border-gray-100 rounded-2xl p-4 focus:outline-none focus:border-yellow-400 transition font-medium"
                                    placeholder=" accurately address ..."
                                    required><?php echo $user_data['Address'] ?? ''; ?></textarea>
                            </div>
                        </div>

                        <button type="submit" name="place_order" class="w-full py-5 bg-yellow-400 text-black rounded-3xl font-black text-[11px] uppercase tracking-[3px]">
                            Complete Order
                        </button>

                        <div class="mt-12">
                            <h3 class="text-xl font-bold mb-6">Payment Method</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <input type="radio" name="payment_method" id="cod" value="COD" class="hidden payment-option" checked>
                                    <label for="cod" class="flex items-center gap-4 p-6 border-2 border-gray-100 rounded-[25px] cursor-pointer transition-all">
                                        <div class="w-12 h-12 bg-yellow-50 rounded-full flex items-center justify-center text-yellow-600"><i class="fas fa-truck"></i></div>
                                        <div>
                                            <p class="font-bold text-sm">Cash on Delivery</p>
                                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Pay at doorstep</p>
                                        </div>
                                    </label>
                                </div>
                                <div>
                                    <input type="radio" name="payment_method" id="online" value="Online" class="hidden payment-option">
                                    <label for="online" class="flex items-center gap-4 p-6 border-2 border-gray-100 rounded-[25px] cursor-pointer transition-all">
                                        <div class="w-12 h-12 bg-black rounded-full flex items-center justify-center text-white"><i class="fas fa-credit-card"></i></div>
                                        <div>
                                            <p class="font-bold text-sm">Online Payment</p>
                                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Credit / Debit Card</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                    <?php else: ?>
                        <div class="py-20 text-center bg-white rounded-[40px] border-2 border-dashed border-gray-100">
                            <p class="text-gray-400 font-bold">Your bag is empty.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="lg:col-span-4">
                    <div class="bg-black text-white p-10 rounded-[40px] shadow-2xl sticky top-32 border-b-8 border-yellow-400">
                        <h2 class="text-2xl font-bold mb-8 italic">Order Summary</h2>
                        <div class="flex justify-between items-center mb-10">
                            <p class="text-gray-400 text-[10px] font-bold uppercase tracking-[4px]">Grand Total</p>
                            <h2 class="text-4xl font-black text-yellow-400">Rs. <?php echo number_format($grand_total, 2); ?></h2>
                        </div>

                        <input type="hidden" name="total_amount" value="<?php echo $grand_total; ?>">
                        <button type="submit" name="place_order" class="w-full py-5 bg-yellow-400 text-black rounded-3xl font-black text-[11px] uppercase tracking-[3px] hover:bg-white transition-all">
                            Complete Order
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </main>
    <footer class="footer-bfresh">
        <div class="container">
            <div class="row gy-5">
                <div class="col-md-6">
                    <h2 class="display-4 fw-bold text-white mb-4">Bfresh<span>.</span></h2>
                    <p class="text-white-50 fs-5 mb-5" style="max-width: 400px;">Pure Nature, Bottled Daily. Handcrafted with passion in Sri Lanka.</p>
                    <div class="d-flex gap-4">
                        <a href="https://www.instagram.com/bfreshbtt?igsh=MWExM2I4OW5zODZuMw==" class="text-white fs-4"><i class="fab fa-instagram"></i></a>
                        <a href="https://www.facebook.com/share/1EhiJ7xGb2/?mibextid=wwXIfr" class="text-white fs-4"><i class="fab fa-facebook-f"></i></a>
                    </div>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-white-50 small">© 2026 Bfresh Lanka (Pvt) Ltd. Built for Quality.</p>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>