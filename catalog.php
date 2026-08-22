<?php
session_start();
$conn = new mysqli("localhost", "root", "", "bfresh_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$search = ""; 
if (isset($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
}

$where_clauses = ["status = 1"];

if (isset($_GET['category']) && !empty($_GET['category'])) {
    $cat = $conn->real_escape_string($_GET['category']);
    $where_clauses[] = "Category = '$cat'";
}

if (isset($_GET['benefit']) && !empty($_GET['benefit'])) {
    $benefit = $conn->real_escape_string($_GET['benefit']);
    $where_clauses[] = "benefits = '$benefit'";
}

if (isset($_GET['price_range']) && !empty($_GET['price_range'])) {
    $range = $_GET['price_range'];
    if ($range == 'under300') { $where_clauses[] = "Price < 300"; }
    elseif ($range == '300to500') { $where_clauses[] = "Price BETWEEN 300 AND 500"; }
    elseif ($range == 'above500') { $where_clauses[] = "Price > 500"; }
}

if ($search) {
    $where_clauses[] = "Name LIKE '%$search%'";
}

$final_query = "SELECT * FROM product WHERE " . implode(' AND ', $where_clauses);
$products = $conn->query($final_query);
$benefit_list = $conn->query("SELECT DISTINCT benefits FROM product WHERE benefits IS NOT NULL");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bfresh | Shop All Juices</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">

    <style>
        :root { --accent: #ffc107; --dark: #000000; --bg-soft: #fdfcf0; }
        body { font-family: 'Outfit', sans-serif; background-color: var(--bg-soft); color: var(--dark); }
        
        /* Background Video Styles */
        #bg-video {
            position: fixed;
            right: 0;
            bottom: 0;
            min-width: 100%;
            min-height: 100%;
            z-index: -2;
            object-fit: cover;
        }

        .video-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.7); /* Content එක පේන්න සුදු overlay එකක් */
            z-index: -1;
        }

        .navbar { background: rgba(255, 255, 255, 0.9) !important; border-bottom: 1px solid #eee; padding: 15px 0; }
        .navbar-brand { font-weight: 800; font-size: 1.8rem; }
        .filter-section { position: sticky; top: 100px; }
        .filter-title { font-weight: 800; text-transform: uppercase; letter-spacing: 1px; font-size: 0.8rem; margin-top: 25px; display: block; border-bottom: 2px solid var(--accent); width: fit-content; margin-bottom: 10px; }
        .filter-link { display: block; color: #555; text-decoration: none; padding: 5px 0; transition: 0.3s; }
        .filter-link:hover { color: var(--dark); font-weight: 700; padding-left: 5px; }
        
        .product-card { background: rgba(255, 255, 255, 0.9); border-radius: 25px; padding: 20px; transition: 0.4s; text-align: center; height: 100%; border: 1px solid #f0f0f0; }
        .product-card:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0,0,0,0.05); }
        .img-container { height: 250px; display: flex; align-items: center; justify-content: center; margin-bottom: 15px; }
        .product-img { max-height: 100%; object-fit: contain; }
        .view-btn { text-decoration: none; display: block; transition: 0.3s; }
        .view-btn:hover { background-color: var(--accent) !important; color: black !important; border-color: var(--accent) !important; }
        
        /* --- Footer --- */
        .footer-bfresh { background: var(--dark); color: #fff; padding: 120px 0 50px; }

        @media (max-width: 992px) {
            .split-hero { height: auto; }
            .slider-column { height: 50vh; }
            .text-column { padding: 80px 30px; text-align: center; }
        }
    </style>
</head>
<body>

    <!-- Background Video Added Here -->
    <video autoplay muted loop playsinline id="bg-video">
        <source src="uploads/fresh-juice.mp4" type="video/mp4">
    </video>
    <div class="video-overlay"></div>

    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand text-dark" href="index.php">Bfresh<span>.</span></a>
            <div class="d-flex align-items-center gap-4">
                <a href="index.php" class="nav-link text-dark fw-bold">Home</a>
                 <a href="login.php" class="text-dark text-decoration-none fw-bold d-none d-md-block">ACCOUNT</a>
                <a href="guest_checkout.php" class="text-dark fs-4"><i class="fas fa-shopping-bag"></i></a>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-lg-5 py-5">
        <div class="row">
            <div class="col-lg-2">
    <div class="filter-section p-3 bg-white rounded-[30px] border border-gray-100 shadow-sm">
        <a href="catalog.php" class="btn btn-dark btn-sm rounded-pill w-100 mb-4 fw-bold">
            <i class="fas fa-sync-alt me-2"></i>Reset Filters
        </a>

        <span class="filter-title">Categories</span>
        <a href="?category=Fresh Fruit" class="filter-link"><i class="fas fa-apple-whole me-2 text-warning"></i>Fresh Fruit</a>
        <a href="?category=Smoothies" class="filter-link"><i class="fas fa-blender me-2 text-warning"></i>Smoothies</a>
        <a href="?category=Seasonal Specials" class="filter-link"><i class="fas fa-star me-2 text-warning"></i>Seasonal Specials</a>
        <a href="?category=Organic Mix" class="filter-link"><i class="fas fa-leaf text-warning me-2"></i>Organic Mix</a>
        <a href="?category=Family Size" class="filter-link"><i class="fas fa-users text-warning me-2"></i>Family Size</a>

        
        <?php if($benefit_list && $benefit_list->num_rows > 0): ?>
            <?php while($b = $benefit_list->fetch_assoc()): ?>
                <a href="?benefit=<?php echo urlencode($b['benefits']); ?>" class="filter-link">
                    <i class="fas fa-check-circle me-2 text-success" style="font-size: 10px;"></i>
                    <?php echo htmlspecialchars($b['benefits']); ?>
                </a>
            <?php endwhile; ?>
        
            
        <?php endif; ?>

        <span class="filter-title">Price Range</span>
        <a href="?price_range=under300" class="filter-link">
            <div class="d-flex justify-content-between">
                <span>Under Rs. 300</span>
                <i class="fas fa-chevron-right mt-1 small opacity-50"></i>
            </div>
        </a>
        <a href="?price_range=300to500" class="filter-link">
            <div class="d-flex justify-content-between">
                <span>Rs. 300 - 500</span>
                <i class="fas fa-chevron-right mt-1 small opacity-50"></i>
            </div>
        </a>
        <a href="?price_range=above500" class="filter-link">
            <div class="d-flex justify-content-between">
                <span>Above Rs. 500</span>
                <i class="fas fa-chevron-right mt-1 small opacity-50"></i>
            </div>
        </a>
    </div>
</div>

            <div class="col-lg-10">
                <div class="row mb-5 align-items-center">
                    <div class="col-md-6"><h1 class="display-6 fw-bold">Fresh <span class="text-warning">Selections</span></h1></div>
                    <div class="col-md-6">
                        <form method="GET" class="d-flex">
                            <input type="text" name="search" class="form-control rounded-pill px-4" placeholder="Search juices..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-dark rounded-pill px-4 ms-2">Search</button>
                        </form>
                    </div>
                </div>

                <div class="row g-4">
                    <?php if ($products && $products->num_rows > 0): ?>
                        <?php while($row = $products->fetch_assoc()): ?>
                            <div class="col-md-4 col-xl-3">
                                <div class="product-card">
                                    <div class="img-container">
                                        <img src="<?php echo $row['image']; ?>" class="product-img">
                                    </div>
                                    <span class="badge bg-light text-dark mb-2"><?php echo htmlspecialchars($row['Category']); ?></span>
                                    <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($row['Name']); ?></h5>
                                    <h4 class="fw-bold text-warning mb-3">Rs. <?php echo number_format($row['Price'], 2); ?></h4>
                                    
                                    <a href="<?php echo htmlspecialchars($row['Page_URL']); ?>?id=<?php echo $row['Product_ID']; ?>" class="btn btn-dark w-100 rounded-pill py-2 fw-bold view-btn">
                                        VIEW DETAILS
                                    </a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12 text-center py-5"><h4 class="text-muted">No juices found matching your criteria.</h4></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
<footer class="footer-bfresh">
        <div class="container">
            <div class="row gy-5">
                <div class="col-md-6">
                    <h2 class="display-4 fw-bold text-white mb-4">Bfresh<span>.</span></h2>
                    <p class="text-white-50 fs-5 mb-5" style="max-width: 400px;">Pure Nature, Bottled Daily. Handcrafted with passion in Sri Lanka.</p>
                    <div class="d-flex gap-4">
                        <a href="#" class="text-white fs-4"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white fs-4"><i class="fab fa-facebook-f"></i></a>
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