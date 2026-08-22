<?php
session_start();

//1. Check if you are logged in
if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); 
    exit(); 
}

// 2.Checking the role (Optional but more secure)
if ($_SESSION['role'] !== 'Customer') {
    header("Location: admin_dashboard.php");
    exit();
}


$conn = new mysqli("localhost", "root", "", "bfresh_db");
$uid = $_SESSION['user_id'];

// User Profile Data - The error was resolved using LEFT JOIN.
$user_res = $conn->query("SELECT u.Name, c.Loyalty_Points FROM user u LEFT JOIN customer c ON u.User_ID = c.Customer_ID WHERE u.User_ID = $uid");
$user_data = $user_res->fetch_assoc();

//Name displayed on the dashboard (to avoid errors)
$display_name = isset($user_data['Name']) ? explode(' ', $user_data['Name'])[0] : "Guest";

// --- Filter Logic ---
$where_clauses = ["status = 1"];

if (isset($_GET['category']) && !empty($_GET['category'])) {
    $cat = $conn->real_escape_string($_GET['category']);
    $where_clauses[] = "Category = '$cat'";
}

if (isset($_GET['price_range']) && !empty($_GET['price_range'])) {
    $range = $_GET['price_range'];
    if ($range == 'under300') { $where_clauses[] = "Price < 300"; }
    elseif ($range == '300to500') { $where_clauses[] = "Price BETWEEN 300 AND 500"; }
    elseif ($range == 'above500') { $where_clauses[] = "Price > 500"; }
}

$final_query = "SELECT * FROM product WHERE " . implode(' AND ', $where_clauses);
$products = $conn->query($final_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bfresh | Premium Selection</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;400;600;800&family=Playfair+Display:ital,wght@1,900&display=swap');
        
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: #000000; 
            color: #ffffff; 
            overflow-x: hidden;
        }
        
        .serif-italic { font-family: 'Playfair Display', serif; font-style: italic; }

        /* Side Video Container */
        .video-sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 35%;
            height: 100vh;
            overflow: hidden;
            z-index: 10;
        }

        .video-sidebar video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: brightness(0.7);
        }

        /* Content Area Split */
        .content-area {
            margin-left: 35%;
            width: 65%;
            min-height: 100vh;
            background: #000000;
            position: relative;
            z-index: 20;
        }

        /* Modern Nav */
        .nav-dark {
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        /* --- New Modern Product Card Design --- */
        .product-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(15px);
            border-radius: 40px;
            padding: 24px;
            transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
            border: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .product-card:hover {
            transform: translateY(-10px);
            border-color: #eab308;
            box-shadow: 0 0 30px rgba(234, 179, 8, 0.2);
            background: rgba(255, 255, 255, 0.06);
        }

        .img-container {
            width: 100%;
            aspect-ratio: 1/1;
            background: rgba(0,0,0,0.2);
            border-radius: 30px;
            overflow: hidden;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .img-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.8s ease;
        }

        .product-card:hover .img-container img {
            transform: scale(1.1);
        }

        /* Category Badge */
        .cat-badge {
            background: #eab308;
            color: #000;
            font-size: 10px;
            font-weight: 900;
            padding: 4px 16px;
            border-radius: 50px;
            text-transform: uppercase;
            margin-bottom: 12px;
            letter-spacing: 1px;
        }

        /* View Details Button */
        .view-btn {
            background: #eab308;
            color: #000;
            width: 100%;
            padding: 12px;
            border-radius: 50px;
            font-weight: 800;
            font-size: 13px;
            text-transform: uppercase;
            margin-top: 20px;
            transition: 0.3s;
        }

        .view-btn:hover {
            background: #ffffff;
            transform: scale(1.02);
        }

        .filter-title { font-[800] text-[10px] uppercase tracking-[3px] text-yellow-500 mb-6 block border-b-2 border-yellow-500/20 w-fit pb-1; }
        .filter-item { display: flex; align-items: center; gap: 12px; font-size: 13px; font-weight: 600; color: #777; margin-bottom: 18px; transition: 0.3s; text-decoration: none; }
        .filter-item:hover { color: #ffffff; transform: translateX(8px); }
        .filter-item i { font-size: 14px; color: #eab308; width: 20px; }

        .modal-blur { backdrop-filter: blur(35px); background: rgba(0,0,0,0.85); }
        
        #mSideImg {
            filter: none !important;
            opacity: 1 !important;
        }

        .footer-bfresh { background: #050505; border-top: 1px solid rgba(255,255,255,0.03); color: #ffffff; padding: 100px 0 50px; }

        @media (max-width: 1024px) {
            .video-sidebar { display: none; }
            .content-area { margin-left: 0; width: 100%; }
        }
    </style>
</head>
<body class="antialiased">

    <!-- Split Screen Side Video -->
    <aside class="video-sidebar">
        <video autoplay muted loop playsinline>
            <source src="uploads/fresh-juice.mp4" type="video/mp4">
        </video>
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-black/20 to-black"></div>
        <div class="absolute bottom-20 left-12">
            <h1 class="text-7xl font-black tracking-tighter leading-none text-white italic">DRINK THE<br><span class="text-yellow-500">REAL.</span></h1>
            <p class="text-white/50 mt-6 max-w-xs font-bold uppercase tracking-widest text-xs">Authentic fruit experience delivered to your door.</p>
        </div>
    </aside>

    <div class="content-area">
        <nav class="py-8 px-12 flex justify-between items-center nav-dark sticky top-0 z-50">
            <div class="text-3xl font-black tracking-tighter italic">Bfresh<span class="text-yellow-500">.</span></div>
            <div class="flex items-center gap-10 text-[10px] font-black uppercase tracking-[3px]">
                <a href="view_cart.php" class="text-white text-decoration-none hover:text-yellow-500 transition">Bag (<?php echo isset($_SESSION['login_cart']) ? count($_SESSION['login_cart']) : '0'; ?>)</a>
                <a href="profile.php" class="bg-yellow-500 text-black px-8 py-3 rounded-full font-black text-decoration-none hover:bg-white transition shadow-xl">Profile</a>
            </div>
        </nav>

        <main class="px-12 py-16">
            <div class="flex flex-col xl:flex-row gap-16">
                
                <aside class="xl:w-64 flex-shrink-0">
                    <div class="sticky top-32">
                        <header class="mb-12">
                            <h1 class="text-5xl font-black tracking-tighter leading-[0.8] mb-4 text-white">Pure <br><span class="serif-italic text-yellow-500 text-4xl">Selection.</span></h1>
                            <p class="text-gray-600 font-bold uppercase tracking-[4px] text-[8px]">Session for <?php echo $display_name; ?></p>
                        </header>

                        <a href="customer_dashboard.php" class="inline-block mb-10 text-[10px] font-black uppercase tracking-[2px] bg-white/5 px-6 py-2 rounded-full text-white/70 hover:bg-yellow-500 hover:text-black transition text-decoration-none">Reset Filters</a>

                        <div class="mb-12">
                            <span class="filter-title">Categories</span>
                            <a href="?category=Fresh Fruit" class="filter-item"><i class="fas fa-apple-whole"></i> Fresh Fruit</a>
                            <a href="?category=Smoothies" class="filter-item"><i class="fas fa-blender"></i> Smoothies</a>
                            <a href="?category=Seasonal Specials" class="filter-item"><i class="fas fa-star"></i> Seasonal</a>
                            <a href="?category=Organic Mix" class="filter-item"><i class="fas fa-leaf"></i> Organic Mix</a>
                        </div>

                        <div>
                            <span class="filter-title">Price Range</span>
                            <a href="?price_range=under300" class="filter-item"><i class="fas fa-tags"></i> Under 300</a>
                            <a href="?price_range=300to500" class="filter-item"><i class="fas fa-tags"></i> 300 - 500</a>
                            <a href="?price_range=above500" class="filter-item"><i class="fas fa-tags"></i> Above 500</a>
                        </div>
                    </div>
                </aside>

                <div class="flex-1">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                        <?php if($products && $products->num_rows > 0): ?>
                            <?php while($row = $products->fetch_assoc()): ?>
                                <?php 
                                    $db_img = $row['image'];
                                    $popup_img = $db_img; 
                                    $feature_img = "uploads/default_feature.jpg";
                                    $long_desc = "Experience pure nature with our cold-pressed blend.";

                                    switch($row['Product_ID']) {
                                        case 24: $popup_img = "uploads/passion1.jpg"; $feature_img = "uploads/passionfruitimg.jpg"; $long_desc = "A tropical symphony of sun-ripened passion fruits, rich in Vitamin C."; break;
                                        case 25: $popup_img = "uploads/small.jpg"; $feature_img = "uploads/amberellaimg.jpeg"; $long_desc = "A crisp and tart island refresher that leaves a cooling, sophisticated tang on your palate with every sip.."; break;
                                        case 26: $popup_img = "uploads/medium2.jpg"; $feature_img = "uploads/narangg.jpg"; $long_desc = "A zesty, citrus-infused revitalizer made from hand-picked local Narang to deliver a sharp and refreshing spark.."; break;
                                        case 27: $popup_img = "uploads/passion3.jpg"; $feature_img = "uploads/pineapple.jpg"; $long_desc = "A bold tropical fusion of sweet golden pineapple finished with a subtle spicy kick for a truly unique edge.."; break;
                                        case 28: $popup_img = "uploads/7.jpg"; $feature_img = "uploads/narangg.jpg"; $long_desc = "A zesty, citrus-infused revitalizer made from hand-picked local Narang to deliver a sharp and refreshing spark.."; break;
                                        case 29: $popup_img = "uploads/4.jpg"; $feature_img = "uploads/sours.jpg"; $long_desc = "A silky-smooth, exotic blend of fresh Soursop that offers a creamy texture and a naturally calming tropical taste.."; break;
                                        case 30: $popup_img = "uploads/medium4.jpg"; $feature_img = "uploads/amberellaimg.jpeg"; $long_desc = "A crisp and tart island refresher that leaves a cooling, sophisticated tang on your palate with every sip.."; break;
                                        case 31: $popup_img = "uploads/8.jpg"; $feature_img = "uploads/tamarind.jpg"; $long_desc = " A bold, sweet-and-sour infusion that serves as the ultimate natural thirst-quencher with a deep, exotic soul.."; break;
                                        case 32: $popup_img = "uploads/diwul1.jpg"; $feature_img = "uploads/woood.jpg"; $long_desc = "A rich and nostalgic traditional classic that captures the authentic, earthy sweetness of island-grown Woodapple.."; break;
                                        case 33: $popup_img = "uploads/PASSION2.jpg"; $feature_img = "uploads/passionfruitimg.jpg"; $long_desc = "A tropical symphony of sun-ripened passion fruits, rich in Vitamin C."; break;
                                        case 34: $popup_img = "uploads/6.jpg"; $feature_img = "uploads/mangopassion.png"; $long_desc = "A velvety duo of succulent mangoes and tangy passion fruit creating the ultimate sun-kissed flavor balance.."; break;
                                        case 35: $popup_img = "uploads/chili.jpg"; $feature_img = "uploads/pineapple.jpg"; $long_desc = "A bold tropical fusion of sweet golden pineapple finished with a subtle spicy kick for a truly unique edge.."; break;
                                        case 36: $popup_img = "uploads/diwul2.jpg"; $feature_img = "uploads/woood.jpg"; $long_desc = "A rich and nostalgic traditional classic that captures the authentic, earthy sweetness of island-grown Woodapple.."; break;
                                        case 37: $popup_img = "uploads/narang1.jpg"; $feature_img = "uploads/narangg.jpg"; $long_desc = "A zesty, citrus-infused revitalizer made from hand-picked local Narang to deliver a sharp and refreshing spark.."; break;
                                        case 38: $popup_img = "uploads/medium5.jpg"; $feature_img = "uploads/sours.jpg"; $long_desc = "A silky-smooth, exotic blend of fresh Soursop that offers a creamy texture and a naturally calming tropical taste.."; break;
                                        case 39: $popup_img = "uploads/chili2.jpg"; $feature_img = "uploads/pineapple.jpg"; $long_desc = "A bold tropical fusion of sweet golden pineapple finished with a subtle spicy kick for a truly unique edge.."; break;
                                        case 40: $popup_img = "uploads/mango1.jpg"; $feature_img = "uploads/mangopassion.png"; $long_desc = "A velvety duo of succulent mangoes and tangy passion fruit creating the ultimate sun-kissed flavor balance.."; break;
                                        case 41: $popup_img = "uploads/medium3.jpg"; $feature_img = "uploads/mangopassion.png"; $long_desc = "A velvety duo of succulent mangoes and tangy passion fruit creating the ultimate sun-kissed flavor balance.."; break;
                                        case 42: $popup_img = "uploads/medium6.jpg"; $feature_img = "uploads/tamarind.jpg"; $long_desc = "A bold, sweet-and-sour infusion that serves as the ultimate natural thirst-quencher with a deep, exotic soul.."; break;
                                    }
                                ?>

                                <div class="product-card group">
                                    <div class="img-container">
                                        <img src="<?php echo $db_img; ?>" alt="">
                                    </div>
                                    <span class="cat-badge"><?php echo htmlspecialchars($row['Category']); ?></span>
                                    <h3 class="text-2xl font-black italic uppercase tracking-tight mb-2 text-white"><?php echo htmlspecialchars($row['Name']); ?></h3>
                                    <p class="text-3xl font-black text-yellow-500 tracking-tighter italic">Rs. <?php echo number_format($row['Price'], 2); ?></p>
                                    
                                    <button class="view-btn" onclick="openDetails(<?php echo htmlspecialchars(json_encode($row)); ?>, '<?php echo $popup_img; ?>', '<?php echo $feature_img; ?>', '<?php echo addslashes($long_desc); ?>')">
                                        View Details
                                    </button>
                                </div>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>

        <footer class="footer-bfresh">
            <div class="container mx-auto px-12">
                <div class="flex flex-wrap justify-between items-center gap-10">
                    <div class="w-full md:w-1/2">
                        <h2 class="text-6xl font-black text-white mb-4 italic">Bfresh<span class="text-yellow-500">.</span></h2>
                        <p class="text-white/30 text-xl mb-8 max-w-sm">Handcrafted with passion in Sri Lanka. 100% natural.</p>
                    </div>
                    <div class="w-full md:w-auto text-md-end">
                        <p class="text-white/10 text-sm">© 2026 Bfresh Lanka (Pvt) Ltd.</p>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- Product Modal -->
    <div id="detailModal" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4 md:p-10 modal-blur">
        <div class="modal-content bg-[#0a0a0a] w-full max-w-7xl h-full max-h-[90vh] rounded-[60px] border border-white/5 overflow-hidden flex flex-col md:flex-row relative text-white">
            <button onclick="closeDetails()" class="absolute top-10 right-10 z-50 w-12 h-12 flex items-center justify-center border border-white/10 rounded-full hover:bg-yellow-500 hover:text-black transition-all"><i class="fa-solid fa-xmark"></i></button>
            
            <div class="md:w-1/2 bg-[#050505] flex items-center justify-center p-10 lg:p-24 relative overflow-hidden">
                <img id="mImg" src="" class="w-full h-auto max-h-[600px] object-contain drop-shadow-2xl z-10">
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none"><h1 id="bgText" class="text-[180px] font-black text-white/[0.01] tracking-tighter uppercase italic">FRESH</h1></div>
            </div>

            <div class="md:w-1/2 p-12 lg:p-20 flex flex-col justify-center overflow-y-auto">
                <div class="max-w-md">
                    <span id="mCat" class="text-[10px] font-black uppercase tracking-[5px] text-yellow-500 mb-4 block italic">Category</span>
                    <h2 id="mName" class="text-6xl font-black tracking-tighter leading-[0.9] mb-8 italic uppercase">Name</h2>
                    <p id="mPrice" class="text-4xl font-light italic mb-10 text-yellow-500 tracking-tighter">Rs. 0</p>
                    
                    <div class="flex items-stretch bg-white/5 rounded-[35px] overflow-hidden mb-12 h-32 border border-white/5">
                        <div class="w-1/3 overflow-hidden bg-white/5">
                            <img id="mSideImg" src="" class="w-full h-full object-cover">
                        </div>
                        <div class="w-2/3 p-6 flex items-center"><p id="mDesc" class="text-gray-400 text-xs leading-relaxed font-semibold italic">Details...</p></div>
                    </div>

                    <form method="POST" action="login_cart.php">
                        <input type="hidden" name="product_id" id="mId">
                        <div class="flex gap-4">
                            <div class="w-24 bg-white/5 rounded-3xl flex items-center justify-center"><input type="number" name="quantity" value="1" min="1" class="w-full bg-transparent text-center font-black text-white text-lg focus:outline-none py-4"></div>
                            <button type="submit" name="add_to_cart" class="flex-1 bg-yellow-500 text-black py-6 rounded-3xl font-black text-[10px] uppercase tracking-[4px] shadow-xl hover:bg-white transition-all">Add to Bag +</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openDetails(p, popupImg, featureImg, longDesc) {
            const modal = document.getElementById('detailModal');
            document.getElementById('mImg').src = popupImg;
            document.getElementById('mSideImg').src = featureImg;
            document.getElementById('mName').innerText = p.Name;
            document.getElementById('mCat').innerText = p.Category;
            document.getElementById('mPrice').innerText = 'Rs. ' + parseFloat(p.Price).toLocaleString();
            document.getElementById('mId').value = p.Product_ID;
            document.getElementById('mDesc').innerText = longDesc;
            document.getElementById('bgText').innerText = p.Name.split(' ')[0];
            modal.classList.remove('hidden');
            setTimeout(() => modal.classList.add('modal-active'), 10);
            document.body.style.overflow = 'hidden';
        }

        function closeDetails() {
            const modal = document.getElementById('detailModal');
            modal.classList.remove('modal-active');
            setTimeout(() => modal.classList.add('hidden'), 500);
            document.body.style.overflow = 'auto';
        }
    </script>
</body>
</html>