<?php
// Inside the PHP block at the top of index.php, add this:
$conn = new mysqli("localhost", "root", "", "bfresh_db");




// 2. Manage Products (Logic unchanged)
$products = $conn->query("SELECT * FROM product ORDER BY Product_ID ASC");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bfresh | Nature's Freshness, Delivered</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Playfair+Display:wght@700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">

    <style>
        :root { 
            --accent: #ffc107; 
            --deep-yellow: #f1b300;
            --soft-orange: #ff9800; 
            --dark: #000000; 
            --bg-ivory: #fdfcf0;
            --glass: rgba(255, 255, 255, 0.75);
            --transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        body { 
            font-family: 'Outfit', sans-serif; 
            background-color: var(--bg-ivory); 
            color: var(--dark);
            overflow-x: hidden;
        }

        /* --- High-end Navbar --- */
        .navbar { 
            padding: 15px 0; 
            backdrop-filter: blur(20px); 
            background: var(--glass) !important;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            transition: var(--transition);
        }
        .navbar-brand { font-weight: 800; font-size: 2.2rem; color: var(--dark) !important; letter-spacing: -1.5px; }
        .navbar-brand span { color: var(--deep-yellow); }

        /* --- Split Hero Section --- */
        .split-hero { display: flex; flex-wrap: wrap; height: 100vh; width: 100%; border-bottom: 10px solid var(--dark); }
        
        .slider-column { 
            flex: 1.2; min-width: 50%; height: 100%; position: relative; 
            background-color: var(--dark); overflow: hidden;
        }
        
        .slide {
            position: absolute; width: 100%; height: 100%;
            background-size: cover; background-position: center;
            opacity: 0; transform: scale(1.1);
            transition: opacity 1.8s ease-in-out, transform 3s ease-in-out;
        }
        .slide.active { opacity: 1; transform: scale(1); }

        .text-column { 
            flex: 1; min-width: 50%; height: 100%; 
            display: flex; align-items: center; justify-content: center;
            padding: 5% 8%; background: white; position: relative;
        }
        
        .clean-text h1 { 
            font-family: 'Playfair Display', serif; font-size: clamp(3.5rem, 7vw, 6.5rem); 
            font-weight: 900; line-height: 0.85; margin-bottom: 35px; letter-spacing: -3px;
        }
        
        .btn-premium {
            padding: 20px 50px; border-radius: 0; background: var(--dark);
            color: white; font-weight: 800; text-decoration: none;
            display: inline-block; transition: var(--transition);
            border: 2px solid var(--dark); letter-spacing: 2px;
        }
        .btn-premium:hover { background: var(--deep-yellow); color: var(--dark); border-color: var(--deep-yellow); transform: translateY(-5px); }

        /* --- Journey Section (Floating Bottle) --- */
        .floating-bottle {
            animation: float 4s ease-in-out infinite;
            filter: drop-shadow(0 20px 40px rgba(0,0,0,0.15));
        }

        @keyframes float {
            0% { transform: translateY(0px) scale(1.2) rotate(-2deg); }
            50% { transform: translateY(-20px) scale(1.2) rotate(2deg); }
            100% { transform: translateY(0px) scale(1.2) rotate(-2deg); }
        }

        .step-num {
            font-size: 4.5rem; font-weight: 900; 
            color: transparent; -webkit-text-stroke: 1px var(--deep-yellow);
            line-height: 1; transition: var(--transition); opacity: 0.3;
        }
        .journey-step {
            padding: 25px; border-radius: 20px; transition: var(--transition);
            margin-bottom: 10px;
        }
        .journey-step:hover { background: white; box-shadow: 0 20px 50px rgba(0,0,0,0.05); transform: translateX(20px); }
        .journey-step:hover .step-num { opacity: 1; color: var(--deep-yellow); }

        /* --- Product Grid (Updated for larger images) --- */
        .product-item { 
            background: white; border-radius: 0; padding: 25px 20px; 
            text-align: center; transition: var(--transition);
            border: 1px solid #eee; margin: 15px;
            overflow: hidden;
        }
        .product-item:hover { transform: translateY(-15px); box-shadow: 0 40px 70px rgba(0,0,0,0.1); border-color: var(--deep-yellow); }
        
        .product-img { 
           /* The height has been increased and scale has been used to enlarge the image */
            height: 320px; 
            width: 100%;
            object-fit: contain; 
            margin-bottom: 20px; 
            transform: scale(1.1);/* First made a little bigger */
            transition: var(--transition); 
            filter: drop-shadow(0 15px 25px rgba(0,0,0,0.15));
        }
        .product-item:hover .product-img { 
            transform: scale(1.25) rotate(-5deg); /* Gets bigger when hovered */
        }
        
        .btn-cart-add { 
            background: var(--dark); color: white; border-radius: 0; 
            font-weight: 800; width: 100%; padding: 18px; border: none; 
            transition: 0.3s; margin-top: 15px; letter-spacing: 1px;
        }
        .btn-cart-add:hover { background: var(--deep-yellow); color: var(--dark); }

        /* --- Reveal Animation --- */
        .reveal { opacity: 0; transform: translateY(50px); transition: 1.2s all ease; }
        .reveal.active { opacity: 1; transform: translateY(0); }

        /* --- Footer --- */
        .footer-bfresh { background: var(--dark); color: #fff; padding: 120px 0 50px; }

        @media (max-width: 992px) {
            .split-hero { height: auto; }
            .slider-column { height: 50vh; }
            .text-column { padding: 80px 30px; text-align: center; }
        }
        .video-wrapper {
    max-width: 900px;
    margin: 0 auto;
    overflow: hidden;
    position: relative;
    transition: var(--transition);
}

.video-wrapper video {
    transition: var(--transition);
}

.video-wrapper:hover {
    transform: scale(1.02);
}
.hero-video-section {
    position: relative;
    height: 90vh;/* 90% of the screen height */
    width: 100%;
    overflow: hidden;
    display: flex;
    align-items: center;
    background: #000;
}

.hero-bg-video {
    position: absolute;
    top: 50%;
    left: 50%;
    min-width: 100%;
    min-height: 100%;
    width: auto;
    height: auto;
    z-index: 0;
    transform: translate(-50%, -50%);
    filter: brightness(0.7); /* The video was darkened a bit to make the text more visible */
}

.hero-content-wrapper {
    position: relative;
    z-index: 1;
    display: flex;
    justify-content: flex-start; /* Take the card to the left */
}

.hero-card {
    background: rgba(255, 255, 255, 0.9); /* Look like glass (Glassmorphism) */
    backdrop-filter: blur(10px);
    padding: 50px;
    border-radius: 30px;
    max-width: 600px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
}

.badge-premium {
    background: #000000;
    color: white;
    padding: 8px 20px;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 2px;
    display: inline-block;
    margin-bottom: 20px;
}
.hero-card {
  
    background: rgba(255, 255, 255, 0.15); 
    backdrop-filter: blur(15px); 
    -webkit-backdrop-filter: blur(15px);
    
    padding: 50px;
    border-radius: 30px;
    max-width: 600px;
    
   
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
}


.hero-card h1 {
    font-family: 'Playfair Display', serif;
    font-weight: 900;
    color: #ffffff; /* සුදු පාට */
    margin-bottom: 20px;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3); /* Text එක කැපිලා පේන්න */
}

.hero-desc {
    font-size: 1.1rem;
    color: #f8f9fa; /* ලා අළු/සුදු පාට */
    line-height: 1.8;
    margin-bottom: 35px;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
}

.hero-card .text-success {
    color: #f3db07 !important; 
}


@media (max-width: 768px) {
    .hero-content-wrapper {
        justify-content: center;
    }
    .hero-card {
        padding: 30px;
        text-align: center;
    }
}
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand animate__animated animate__fadeIn" href="index.php">Bfresh<span>.</span></a>
            <div class="ms-auto d-flex align-items-center gap-4">
                <a href="offers.php" class="text-dark text-decoration-none fw-bold">OFFERS ⚡</a>
                 <a href="guest_checkout.php" class="text-dark fs-4"><i class="fas fa-shopping-bag"></i></a>
                <a href="login.php" class="text-dark text-decoration-none fw-bold d-none d-md-block">ACCOUNT</a>
                <a href="catalog.php" class="btn-premium py-2 px-4" style="padding: 12px 30px !important; font-size: 0.8rem;">SHOP NOW</a>
            </div>
        </div>
    </nav>
    <section class="hero-video-section">
    <video autoplay muted loop playsinline class="hero-bg-video">
        <source src="uploads/fresh-juice.mp4" type="video/mp4">
    </video>

    <div class="container">
        <div class="hero-content-wrapper">
            <div class="hero-card animate__animated animate__fadeInUp">
                <span class="badge-premium">100% Organic</span>
                <h1 class="display-4 fw-bold">DRINK THE <span class="text-success">REAL.</span></h1>
                <p class="hero-desc">The true taste of nature, without any artificial sweeteners. Experience 100% pure fruit juice now at your home.</p>
                
                <div class="hero-btns">
                    <a href="catalog.php" class="btn btn-success btn-lg rounded-pill px-5 py-3 shadow">SHOP NOW</a>
                    <a href="offers.php" class="btn btn-outline-warning btn-lg rounded-pill px-5 py-3 shadow ms-2">VIEW OFFERS</a>
                   
                </div>
            </div>
        </div>
    </div>
</section>

   <section class="split-hero">
       
        
        <div class="text-column">
            <div class="clean-text animate__animated animate__fadeIn">
                <span class="fw-bold text-uppercase mb-3 d-block" style="letter-spacing: 5px; color: var(--deep-yellow); font-size: 0.9rem;">OUR Story</span>
                <h1><span style="color: var(--soft-orange)">Welcome to the House of BFresh</span></h1>
                <p class="lead text-muted mb-5" style="max-width: 500px; line-height: 1.8;">Why <b>BFresh?</b> Because we put the ‘real’ in real good! Enjoy the season’s freshest picks, exotic and farm-fresh ingredients whenever you like, with <b>BFresh..</b><br> It’s literally all-natural goodness, minus all the bad stuff!
<br>The <b>BFresh</b> journey started in a simple kitchen and is now enjoyed across Sri Lanka and in Global markets...</p>
                <div class="d-flex gap-3 justify-content-center justify-content-lg-start">
                   
                </div>
            </div>
        </div>
        <div class="slider-column">
           <video autoplay muted loop playsinline class="hero-bg-video">
        <source src="uploads/IMG_4029.mp4" type="video/mp4">
    </video>

        </div>
        
    </section>

    <section id="journey" class="py-5 overflow-hidden" style="background: var(--bg-ivory);">
        <div class="container py-5">
            <div class="row align-items-center">
                <div class="col-lg-6 reveal">
                    <div class="position-relative text-center p-4">
                        <div class="bg-dark position-absolute" style="width: 80%; height: 90%; top: 40px; left: 10%; z-index: 0; opacity: 0.05; filter: blur(50px);"></div>
                        <img src="https://images.unsplash.com/photo-1613478223719-2ab802602423?q=80&w=1000&auto=format&fit=crop" 
                             class="img-fluid position-relative floating-bottle" 
                             alt="Fresh Juice Bottle" 
                             style="z-index: 1; width: 85%;">
                    </div>
                </div>
                
                <div class="col-lg-5 offset-lg-1 mt-5 mt-lg-0 reveal">
                    <h2 class="display-4 fw-bold mb-5" style="font-family: 'Playfair Display', serif;">Crafting <span style="color: var(--soft-orange)">Freshness.</span></h2>
                    
                    <div class="journey-step d-flex align-items-center">
                        <div class="step-num me-4">01</div>
                        <div>
                            <h5 class="fw-bold mb-0 fs-4">Eco-Selection</h5>
                            <p class="text-muted small fw-bold text-uppercase">Direct from organic estates.</p>
                        </div>
                    </div>

                    <div class="journey-step d-flex align-items-center">
                        <div class="step-num me-4">02</div>
                        <div>
                            <h5 class="fw-bold mb-0 fs-4">Cold-Extraction</h5>
                            <p class="text-muted small fw-bold text-uppercase">No heat, 100% Vitamins.</p>
                        </div>
                    </div>

                    <div class="journey-step d-flex align-items-center">
                        <div class="step-num me-4">03</div>
                        <div>
                            <h5 class="fw-bold mb-0 fs-4">Iced-Delivery</h5>
                            <p class="text-muted small fw-bold text-uppercase">Chilled to your doorstep.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
    <div class="container">
        <div class="owl-carousel owl-theme bestseller-slider reveal">
            <?php 
            // Here we import all the items (Active and Inactive)
            $query = "SELECT * FROM product ORDER BY Product_ID DESC";
            $products = $conn->query($query);

            if($products && $products->num_rows > 0): 
                while($row = $products->fetch_assoc()): 
                   // If the status is 0, we identify it as Deactivated
                    $is_deactivated = ($row['status'] == 0);
            ?>
                <a href="catalog.php" class="text-decoration-none text-dark">
                    <div class="product-item" style="position: relative; <?php echo $is_deactivated ? 'opacity: 0.6;' : ''; ?>">
                        
                        <?php if($is_deactivated): ?>
                            <div style="position: absolute; top: 20px; right: 20px; background: #ef4444; color: white; padding: 5px 15px; border-radius: 50px; font-size: 10px; font-weight: 800; z-index: 10; letter-spacing: 1px;">
                                DEACTIVATED
                            </div>
                        <?php endif; ?>

                        <img src="<?php echo $row['image']; ?>" class="product-img" alt="<?php echo htmlspecialchars($row['Name']); ?>">
                        
                        <h5 class="fw-bold text-uppercase mb-1"><?php echo htmlspecialchars($row['Name']); ?></h5>
                        <p class="text-muted small mb-3">RAW • 250ML</p>
                        
                        <h4 class="fw-bold" style="color: var(--dark)">
                            Rs. <?php echo number_format($row['Price'], 2); ?>
                        </h4>
                    </div>
                </a>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center w-100">
                    <p class="text-muted">No products found in the database.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<!-- Partners Image Section -->
<section class="partners-image-section py-4" style="background-color: #ffffff;">
    <div class="container text-center">
        <h1> OUR PARTNERS </h1>
        <img src="uploads/now.jpg" alt="Our Partners" style="width: 100%; max-width: 1000px; height: auto; display: block; margin: 0 auto;">
    </div>
</section>

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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
    
    <script>
        const slides = document.querySelectorAll('.slide');
        let current = 0;
        setInterval(() => {
            slides[current].classList.remove('active');
            current = (current + 1) % slides.length;
            slides[current].classList.add('active');
        }, 5000);

        $(document).ready(function(){
            $(".bestseller-slider").owlCarousel({
                loop: true,
                margin: 30,
                autoplay: true,
                smartSpeed: 1000,
                responsive:{ 0:{ items:1 }, 768:{ items:2 }, 1100:{ items:4 } }
            });
        });

        function reveal() {
            var reveals = document.querySelectorAll(".reveal");
            for (var i = 0; i < reveals.length; i++) {
                var windowHeight = window.innerHeight;
                var elementTop = reveals[i].getBoundingClientRect().top;
                if (elementTop < windowHeight - 100) { reveals[i].classList.add("active"); }
            }
        }
        window.addEventListener("scroll", reveal);
        window.onload = reveal;
    </script>
</body>
</html>