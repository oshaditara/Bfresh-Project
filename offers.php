<?php
include 'db_config.php';

// දැනට තිබෙන වෙලාව ලබා ගැනීම
$current_time = date('Y-m-d H:i:s');

/** 
 * Query එක: Promotions table එක සහ Product table එක join කර, 
 * Flash Sale වර්ගයේ සහ දැනට කාලය වලංගු (Active) දත්ත පමණක් ලබා ගනී.
 */
$flash_sql = "SELECT p.Product_ID, p.Name, p.Price, pr.Discount_Percent, pr.Expiry_Date, pr.Start_Date 
              FROM promotions pr 
              INNER JOIN product p ON pr.Product_ID = p.Product_ID 
              WHERE pr.Promo_Type = 'Flash Sale' 
              AND pr.Status = 1 
              AND '$current_time' BETWEEN pr.Start_Date AND pr.Expiry_Date";

$flash_result = $conn->query($flash_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bfresh | Flash Sale Offers</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root { --accent: #ffc107; --dark: #000; --bg: #fdfcf0; }
        body { font-family: 'Outfit', sans-serif; background-color: var(--bg); margin: 0; }
        
        .btn-back {
            position: fixed; top: 20px; left: 20px; z-index: 1000;
            background: var(--accent); color: #000; padding: 10px 20px;
            border-radius: 50px; font-weight: 800; text-decoration: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .offer-header { background: var(--dark); color: #fff; padding: 100px 0 60px; text-align: center; border-bottom: 5px solid var(--accent); }
        .product-card { background: #fff; border-radius: 20px; padding: 30px; transition: 0.4s; border: 1px solid #eee; position: relative; text-align: center; }
        .discount-tag { position: absolute; top: 20px; left: 20px; background: var(--accent); color: #000; padding: 5px 12px; font-weight: 800; border-radius: 8px; }
        
        #toast-msg {
            visibility: hidden; min-width: 280px; background-color: #198754; color: #fff;
            text-align: center; border-radius: 12px; padding: 20px;
            position: fixed; z-index: 1001; right: 30px; bottom: 30px; font-weight: 600;
        }
        #toast-msg.show { visibility: visible; animation: fadein 0.5s, fadeout 0.5s 2.5s; }
        @keyframes fadein { from {bottom: 0; opacity: 0;} to {bottom: 30px; opacity: 1;} }
        @keyframes fadeout { from {bottom: 30px; opacity: 1;} to {bottom: 0; opacity: 0;} }
    </style>
</head>
<body>

    <a href="index.php" class="btn-back"><i class="fas fa-arrow-left"></i> CONTINUE SHOPPING</a>

    <div class="offer-header">
        <div class="container">
            <h1 style="color: var(--accent); font-weight: 900;">⚡ FLASH SALES</h1>
            <p>Grab these exclusive deals from our promotions table!</p>
        </div>
    </div>

    <div id="toast-msg"><i class="fas fa-check-circle me-2"></i> Item added to cart!</div>

    <div class="container py-5">
        <div class="row g-4 justify-content-center">
            <?php 
            // Database එකෙන් දත්ත තිබේදැයි පරීක්ෂා කිරීම
            if ($flash_result && $flash_result->num_rows > 0): 
                while($row = $flash_result->fetch_assoc()): 
                    // වට්ටම් සහිත මිල ගණනය කිරීම
                    $discounted_price = $row['Price'] - ($row['Price'] * ($row['Discount_Percent'] / 100));
            ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="product-card">
                            <div class="discount-tag"><?php echo $row['Discount_Percent']; ?>% OFF</div>
                            
                            <h4 class="fw-bold mt-4"><?php echo htmlspecialchars($row['Name']); ?></h4>
                            
                            <div class="mb-3">
                                <span class="text-muted text-decoration-line-through">Rs. <?php echo number_format($row['Price'], 0); ?></span><br>
                                <span class="fw-bold fs-3">Rs. <?php echo number_format($discounted_price, 2); ?></span>
                            </div>

                            <form class="ajax-cart-form">
                                <input type="hidden" name="product_id" value="<?php echo $row['Product_ID']; ?>">
                                <input type="hidden" name="selected_name" value="<?php echo htmlspecialchars($row['Name']); ?>">
                                <input type="hidden" name="selected_price" value="<?php echo $discounted_price; ?>">
                                <input type="hidden" name="quantity" value="1">
                                <input type="hidden" name="add_to_cart" value="1">
                                <button type="submit" class="btn btn-dark w-100 py-3 fw-bold" style="border-radius: 12px;">ADD TO CART</button>
                            </form>
                        </div>
                    </div>
            <?php 
                endwhile; 
            else: 
            ?>
                <!-- දත්ත කිසිවක් නැති විට පෙන්වන පණිවිඩය -->
                <div class="text-center py-5">
                    <h3>No Flash Sales active at this moment.</h3>
                    <p class="text-muted">Please check back later!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.ajax-cart-form').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    type: 'POST',
                    url: 'cart.php',
                    data: $(this).serialize(),
                    success: function() {
                        var x = $("#toast-msg");
                        x.addClass("show");
                        setTimeout(function(){ x.removeClass("show"); }, 3000);
                    }
                });
            });
        });
    </script>
</body>
</html>