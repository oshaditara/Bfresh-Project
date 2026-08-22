<?php
session_start();
$conn = new mysqli("localhost", "root", "", "bfresh_db");

// 1. Login check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = "";
$discount = 0;
$promo_applied = false;

// 2.Calculating the total amount in the cart (using Session)
$subtotal = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $id => $qty) {
        // Check if the ID is a number and get the price from the database
        $res = $conn->query("SELECT Price FROM product WHERE Product_ID = " . intval($id));
        if($res && $res->num_rows > 0) {
            $product = $res->fetch_assoc();
            $subtotal += ($product['Price'] * $qty);
        }
    }
}

// 3. PROMO CODE APPLY LOGIC (Birthday Discount - Function 14.3)
if (isset($_POST['apply_promo'])) {
    $promo_code = strtoupper(trim($_POST['promo_code']));
    
    if ($promo_code === "BDAY20") {
        $today = date('m-d');
       // Check if the customer's birthday matches today's date
        $bday_check = $conn->query("SELECT DOB FROM customer WHERE Customer_ID = '$user_id'")->fetch_assoc();
        
        if ($bday_check && date('m-d', strtotime($bday_check['DOB'])) === $today) {
            $discount = $subtotal * 0.20; // 20% Offer
            $promo_applied = true;
            $msg = "<div class='alert alert-success border-0 shadow-sm'>Birthday Promo Applied! 20% Discount added.</div>";
        } else {
            $msg = "<div class='alert alert-danger border-0 shadow-sm'>Sorry, this code is only valid on your birthday!</div>";
        }
    } else {
        $msg = "<div class='alert alert-warning border-0 shadow-sm'>Invalid Promo Code.</div>";
    }
}

$final_total = $subtotal - $discount; // Final amount to be paid
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bfresh | Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        .checkout-card { background: white; border-radius: 25px; box-shadow: 0 15px 35px rgba(0,0,0,0.08); border: none; }
        .promo-section { background: #fff8e1; border-radius: 18px; padding: 25px; border: 2px dashed #ffb300; }
        .btn-pay { background: #1a1a1a; color: white; transition: 0.3s; border: none; }
        .btn-pay:hover { background: #ffb300; color: black; transform: translateY(-3px); }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="checkout-card p-5">
                <h3 class="fw-bold mb-4 text-center"><i class="fas fa-shopping-basket text-warning me-2"></i> Order Summary</h3>
                
                <?php echo $msg; ?>

                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-3 fs-5">
                        <span class="text-muted">Subtotal:</span>
                        <span class="fw-bold">Rs. <?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    
                    <?php if($promo_applied): ?>
                    <div class="d-flex justify-content-between mb-3 text-danger fs-5">
                        <span>Birthday Discount (20%):</span>
                        <span>- Rs. <?php echo number_format($discount, 2); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <hr class="my-4">
                    <div class="d-flex justify-content-between mb-4 h3 fw-bold text-success">
                        <span>Final Total:</span>
                        <span>Rs. <?php echo number_format($final_total, 2); ?></span>
                    </div>
                </div>

                <div class="promo-section mb-5 text-center">
                    <label class="small fw-bold text-muted mb-3 d-block text-uppercase">Have a birthday reward code?</label>
                    <form method="POST" class="d-flex gap-2">
                        <input type="text" name="promo_code" class="form-control rounded-pill px-4" placeholder="e.g. BDAY20">
                        <button type="submit" name="apply_promo" class="btn btn-warning rounded-pill px-4 fw-bold shadow-sm">Apply</button>
                    </form>
                </div>

                <form action="process_order.php" method="POST">
                    <input type="hidden" name="final_amount" value="<?php echo $final_total; ?>">
                    <button type="submit" class="btn btn-pay w-100 py-3 rounded-pill fw-bold shadow-lg">Pay Now (Rs. <?php echo number_format($final_total, 2); ?>)</button>
                </form>

                <div class="text-center mt-4">
                    <a href="catalog.php" class="text-muted text-decoration-none small"><i class="fas fa-arrow-left me-1"></i> Back to Catalog</a>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>