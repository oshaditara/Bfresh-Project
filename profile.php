<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$conn = new mysqli("localhost", "root", "", "bfresh_db");

$uid = $_SESSION['user_id'];

// 1. User saha Customer table accurately join karala data ganna
$user_res = $conn->query("SELECT u.*, c.Address, c.Loyalty_Points FROM user u LEFT JOIN customer c ON u.User_ID = c.Customer_ID WHERE u.User_ID = $uid");
$user = $user_res->fetch_assoc();

// 2. Profile Details Update logic
if (isset($_POST['update_profile'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $address = $conn->real_escape_string($_POST['address']);
    $phone = $conn->real_escape_string($_POST['phone']);
    
    // Tables dekama accurately update karanna ona
    $conn->query("UPDATE user SET Name='$name', Email='$email', Phone_Number='$phone' WHERE User_ID = $uid");
    $conn->query("UPDATE customer SET Address='$address' WHERE Customer_ID = $uid");
    
    header("Location: profile.php?msg=Updated");
    exit();
}

// 3. User ge Order History count eka accurately ganna
$orders_res = $conn->query("SELECT * FROM `order` WHERE User_ID = $uid ORDER BY Order_Date DESC");
$total_orders = $orders_res->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bfresh | My Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #fafafa; }
    </style>
</head>
<body class="antialiased">

    <nav class="bg-white py-6 border-b border-gray-100 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-10 flex justify-between items-center">
            <a href="customer_dashboard.php" class="text-sm font-bold uppercase tracking-widest text-gray-400 hover:text-black transition">
                <i class="fas fa-arrow-left mr-2"></i> Back to Shop
            </a>
            <div class="text-2xl font-black italic tracking-tighter">B-FRESH<span class="text-yellow-400">.</span></div>
            <a href="logout.php" class="text-[10px] font-black uppercase tracking-widest text-red-500">Logout</a>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-10 py-16">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-16">
            <div class="bg-black rounded-[40px] p-10 text-white border-b-8 border-yellow-400 shadow-2xl flex items-center justify-between">
                <div>
                    <p class="text-yellow-400 text-[10px] font-black uppercase tracking-[4px] mb-2">Points Balance</p>
                    <h2 class="text-6xl font-black italic leading-none"><?php echo $user['Loyalty_Points'] ?? 0; ?></h2>
                </div>
                <div class="w-16 h-16 bg-white/10 rounded-full flex items-center justify-center text-yellow-400 text-2xl">
                    <i class="fas fa-bolt"></i>
                </div>
            </div>

            <?php if ($total_orders >= 5): ?>
            <div class="bg-yellow-400 rounded-[40px] p-10 flex items-center justify-between shadow-xl">
                <div>
                    <p class="text-black/50 text-[10px] font-black uppercase tracking-[4px] mb-2">Member Tier</p>
                    <h2 class="text-3xl font-black italic tracking-tighter uppercase">Elite Status</h2>
                    <p class="text-[10px] font-bold text-black/40 mt-1 uppercase">Earn 10 Bonus points on every order</p>
                </div>
                <div class="w-16 h-16 bg-black rounded-full flex items-center justify-center text-yellow-400 text-2xl">
                    <i class="fas fa-crown"></i>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-16">
            
            <div class="lg:col-span-5">
                <h2 class="text-4xl font-black tracking-tighter mb-10">My <span class="text-gray-300">Profile.</span></h2>
                <form action="profile.php" method="POST" class="bg-white p-10 rounded-[45px] shadow-sm border border-gray-100 space-y-6">
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2 block">Full Name</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($user['Name']); ?>" class="w-full bg-gray-50 border border-gray-100 rounded-2xl p-4 focus:outline-none focus:border-yellow-400 font-bold">
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2 block">Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['Email']); ?>" class="w-full bg-gray-50 border border-gray-100 rounded-2xl p-4 focus:outline-none focus:border-yellow-400 font-bold">
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2 block">Phone</label>
                        <input type="text" name="phone" value="<?php echo htmlspecialchars($user['Phone_Number']); ?>" class="w-full bg-gray-50 border border-gray-100 rounded-2xl p-4 focus:outline-none focus:border-yellow-400 font-bold">
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2 block">Delivery Address</label>
                        <textarea name="address" rows="3" class="w-full bg-gray-50 border border-gray-100 rounded-2xl p-4 focus:outline-none focus:border-yellow-400 font-bold"><?php echo htmlspecialchars($user['Address'] ?? ''); ?></textarea>
                    </div>
                    <button type="submit" name="update_profile" class="w-full py-5 bg-black text-white rounded-3xl font-black text-[11px] uppercase tracking-[3px] hover:bg-yellow-400 hover:text-black transition-all shadow-xl">Save Changes</button>
                </form>
            </div>

            <div class="lg:col-span-7">
                <h2 class="text-4xl font-black tracking-tighter mb-10 text-gray-300">Order <span class="text-black">History.</span></h2>
                <div class="space-y-4">
                    <?php if ($total_orders > 0): ?>
                        <?php while ($row = $orders_res->fetch_assoc()): ?>
                            <div class="bg-white p-8 rounded-[40px] border border-gray-100 shadow-sm flex justify-between items-center group hover:border-yellow-400 transition-all">
                                <div>
                                    <p class="text-[9px] font-black uppercase tracking-widest text-gray-400 mb-1">Receipt #BF-<?php echo $row['Order_ID']; ?></p>
                                    <h3 class="text-lg font-black italic"><?php echo date('M d, Y', strtotime($row['Order_Date'])); ?></h3>
                                    <span class="text-[9px] font-black uppercase tracking-widest px-3 py-1 rounded-full bg-gray-50 text-gray-400 mt-2 block w-fit"><?php echo $row['Order_Status']; ?></span>
                                </div>
                                <div class="text-right">
                                    <p class="text-2xl font-black tracking-tighter italic">Rs. <?php echo number_format($row['Total_Amount'], 0); ?></p>
                                </div>
                            </div>
                            <div class="text-right">
                                        <p class="text-2xl font-black tracking-tighter italic">Rs. <?php echo number_format($row['Total_Amount'], 0); ?></p>
                                        
                                        <button onclick="openReviewModal('<?php echo $row['Order_ID']; ?>')" 
                                                class="mt-3 text-[9px] font-black uppercase tracking-[2px] bg-yellow-400 text-black px-4 py-2 rounded-full hover:bg-black hover:text-white transition-all">
                                            Write a Review
                                        </button>
                                    </div>
                                            <!-- Order History Loop eka athule -->
                                        <div class="flex gap-2 mt-3">
                                            <button onclick="openReviewModal('<?php echo $row['Order_ID']; ?>')" 
                                                    class="text-[9px] font-black uppercase tracking-[2px] bg-yellow-400 text-black px-4 py-2 rounded-full hover:bg-black hover:text-white transition-all">
                                                Write a Review
                                            </button>
                                            
                                            <!-- Aluth Re-order Button eka -->
                                            <a href="reorder_process.php?order_id=<?php echo $row['Order_ID']; ?>" 
                                            class="text-[9px] font-black uppercase tracking-[2px] border-2 border-black text-black px-4 py-2 rounded-full hover:bg-black hover:text-white transition-all">
                                                Re-order Now
                                            </a>
                                        </div>
                                <?php endwhile; ?>
                    <?php else: ?>
                        <div class="bg-white p-20 rounded-[45px] text-center border-2 border-dashed border-gray-100 text-gray-300 font-bold uppercase tracking-widest text-xs">No orders yet.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    <div id="reviewModal" class="fixed inset-0 bg-black/50 hidden z-[60] flex items-center justify-center backdrop-blur-sm">
    <div class="bg-white p-10 rounded-[40px] max-w-md w-full mx-4 shadow-2xl">
        <h3 class="text-2xl font-black italic mb-6 uppercase tracking-tighter">Rate Your Experience<span class="text-yellow-400">.</span></h3>
        
        <form id="reviewForm" class="space-y-4">
            <input type="hidden" id="modal_product_id" value="24"> <input type="hidden" id="modal_size" value="Regular">

            <div>
                <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2 block">Rating (1-5)</label>
                <select id="modal_rating" class="w-full bg-gray-50 border border-gray-100 rounded-2xl p-4 font-bold focus:outline-none">
                    <option value="5">5 - Excellent</option>
                    <option value="4">4 - Very Good</option>
                    <option value="3">3 - Good</option>
                    <option value="2">2 - Fair</option>
                    <option value="1">1 - Poor</option>
                </select>
            </div>

            <div>
                <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2 block">Your Comment</label>
                <textarea id="modal_comment" rows="4" class="w-full bg-gray-50 border border-gray-100 rounded-2xl p-4 focus:outline-none font-bold" placeholder="How was the juice?"></textarea>
            </div>

            <div class="flex gap-4 pt-4">
                <button type="button" onclick="closeReviewModal()" class="flex-1 py-4 bg-gray-100 rounded-2xl font-black text-[10px] uppercase tracking-widest">Cancel</button>
                <button type="submit" class="flex-1 py-4 bg-black text-white rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-yellow-400 hover:text-black transition-all">Submit Review</button>
            </div>
        </form>
    </div>
</div>
<script>
function openReviewModal(orderId) {
    document.getElementById('reviewModal').classList.remove('hidden');
}

function closeReviewModal() {
    document.getElementById('reviewModal').classList.add('hidden');
}

document.getElementById('reviewForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData();
    formData.append('product_id', document.getElementById('modal_product_id').value);
    formData.append('rating', document.getElementById('modal_rating').value);
    formData.append('comment', document.getElementById('modal_comment').value);
    formData.append('product_size', document.getElementById('modal_size').value);
    formData.append('customer_name', '<?php echo $user['Name']; ?>'); // Profile data වලින් ගන්නේ
    formData.append('customer_email', '<?php echo $user['Email']; ?>');

    fetch('submit_customer_review.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        if(data.trim() === "success") {
            alert("Review submitted successfully! It will be visible after approval.");
            closeReviewModal();
        } else {
            alert("Something went wrong. Please try again.");
        }
    });
});
</script>

</body>
</html>