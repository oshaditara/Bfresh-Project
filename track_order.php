<?php
session_start();
// Send to login if not logged in
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$conn = new mysqli("localhost", "root", "", "bfresh_db");
$uid = $_SESSION['user_id'];

//Get the exact Order ID from the URL
if (isset($_GET['order_id'])) {
    $order_id = $conn->real_escape_string($_GET['order_id']);

    // Join the relevant order data and user name from the database.
    $sql = "SELECT o.*, u.Name 
            FROM `order` o 
            JOIN user u ON o.User_ID = u.User_ID 
            WHERE o.Order_ID = '$order_id' AND o.User_ID = '$uid'";
            
    $order_res = $conn->query($sql);
    
    if ($order_res && $order_res->num_rows > 0) {
        $order_data = $order_res->fetch_assoc();
    } else {
        echo "<script>alert('Order Not Found!'); window.location.href='customer_dashboard.php';</script>";
        exit();
    }
} else {
    header("Location: customer_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bfresh | Track Order #<?php echo $order_id; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&family=Playfair+Display:ital,wght@1,900&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; color: #1a1a1a; }
        .serif-italic { font-family: 'Playfair Display', serif; font-style: italic; }
        .status-line { position: absolute; left: 20px; top: 0; bottom: 0; width: 2px; background: #e2e8f0; z-index: 0; }
        .active-dot { background: #fbbf24; box-shadow: 0 0 20px rgba(251, 191, 36, 0.5); border: 4px solid #fff; }
    </style>
</head>
<body class="antialiased">

    <nav class="py-8 px-12 flex justify-between items-center bg-white border-b border-gray-100">
        <div class="text-xl font-black tracking-tighter italic">Bfresh<span class="text-yellow-500">.</span></div>
        <a href="customer_dashboard.php" class="text-[10px] font-black uppercase tracking-[2px] text-gray-400 hover:text-black">Close Tracking</a>
    </nav>

    <main class="max-w-4xl mx-auto px-6 py-12">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-12 gap-6">
            <div>
                <span class="text-[10px] font-black uppercase tracking-[4px] text-yellow-500 mb-2 block italic">Live Tracking</span>
                <h1 class="text-5xl font-black tracking-tighter leading-none">Order <span class="serif-italic">#<?php echo $order_id; ?></span></h1>
            </div>
            <div class="bg-black text-white p-6 rounded-[30px] shadow-xl min-w-[200px] text-center">
                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-1">Estimated Arrival</p>
                <p class="text-2xl font-black italic text-yellow-400">25 - 35 MINS</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
            
            <div class="lg:col-span-7 bg-white p-10 rounded-[45px] shadow-sm border border-gray-100 relative overflow-hidden">
                <div class="status-line"></div>
                
                <div class="space-y-12">
                    <div class="relative flex items-start z-10">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center active-dot">
                            <i class="fas fa-check text-white text-xs"></i>
                        </div>
                        <div class="ml-6">
                            <h4 class="font-extrabold text-lg leading-tight">Order Placed Successfully</h4>
                            <p class="text-xs text-gray-400 font-semibold italic"><?php echo date('h:i A', strtotime($order_data['Order_Date'])); ?> • B-Fresh Kitchen</p>
                        </div>
                    </div>

                    <div class="relative flex items-start z-10">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center <?php echo ($order_data['Order_Status'] != 'Pending') ? 'active-dot' : 'bg-gray-100 text-gray-300'; ?>">
                            <i class="fas fa-utensils text-xs"></i>
                        </div>
                        <div class="ml-6">
                            <h4 class="font-extrabold text-lg leading-tight">Preparing your Blend</h4>
                            <p class="text-xs text-gray-400 font-semibold italic">Our chefs are picking the freshest fruits.</p>
                        </div>
                    </div>

                    <div class="relative flex items-start z-10 opacity-40">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center bg-gray-100 text-gray-300">
                            <i class="fas fa-motorcycle text-xs"></i>
                        </div>
                        <div class="ml-6">
                            <h4 class="font-extrabold text-lg leading-tight">On the way</h4>
                            <p class="text-xs text-gray-400 font-semibold italic">Our rider will be there soon.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-5 space-y-6">
                <div class="bg-slate-900 text-white p-10 rounded-[45px] shadow-2xl relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-8 opacity-10">
                        <i class="fas fa-receipt text-6xl rotate-12"></i>
                    </div>
                    <p class="text-[9px] font-black uppercase tracking-[4px] text-gray-400 mb-2">Total Amount Paid</p>
                    <h2 class="text-4xl font-black text-yellow-400 italic">Rs. <?php echo number_format($order_data['Total_Amount'], 2); ?></h2>
                    <div class="mt-6 pt-6 border-t border-white/10">
                        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-1">Payment Method</p>
                        <p class="text-xs font-black italic">Cash on Delivery</p>
                    </div>
                </div>

                <div class="bg-white p-8 rounded-[40px] border border-gray-100 shadow-sm">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-10 h-10 bg-yellow-50 text-yellow-600 rounded-2xl flex items-center justify-center">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h4 class="font-black text-xs uppercase tracking-widest">Delivery Address</h4>
                    </div>
                    <p class="text-sm text-gray-500 font-medium leading-relaxed italic">
                        <?php echo htmlspecialchars($order_data['Delivery_Address']); ?>
                    </p>
                </div>

                <button onclick="window.print()" class="w-full py-5 rounded-3xl border-2 border-gray-100 font-black text-[10px] uppercase tracking-[3px] hover:bg-black hover:text-white hover:border-black transition-all">
                    Download Receipt <i class="fas fa-download ml-2"></i>
                </button>
            </div>
        </div>

    </main>

    <footer class="py-12 text-center">
        <p class="text-[9px] font-bold uppercase tracking-[5px] text-gray-300">Bfresh Premium Experience // Order #<?php echo $order_id; ?></p>
    </footer>

</body>
</html>