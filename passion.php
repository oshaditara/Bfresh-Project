<?php
session_start();
$conn = new mysqli("localhost", "root", "", "bfresh_db");

if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

if (isset($_GET['id'])) {
    $id = $conn->real_escape_string($_GET['id']);
    $query = "SELECT * FROM product WHERE Product_ID = '$id'";
    $result = $conn->query($query);
    if ($result->num_rows > 0) { $product = $result->fetch_assoc(); } 
    else { echo "<script>alert('Product not found!'); window.location.href='catalog.php';</script>"; exit(); }
} else { header("Location: catalog.php"); exit(); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['Name']; ?> - B-FRESH</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&family=Playfair+Display:ital,wght@1,900&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .serif-italic { font-family: 'Playfair Display', serif; font-style: italic; }
        .thumbnail.active { border: 2px solid #ef4444; background: #fff1f2; transform: translateY(-5px); }
        .glass-nav { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(0,0,0,0.05); }
    </style>
</head>
<body class="bg-[#fafafa] antialiased">

    <nav class="glass-nav sticky top-0 z-50 px-6 py-4">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-8">
                <a href="catalog.php" class="group flex items-center gap-2 text-gray-500 hover:text-black transition-all">
                    <div class="w-10 h-10 rounded-full bg-white shadow-sm flex items-center justify-center border border-gray-100 group-hover:bg-black group-hover:text-white transition-all">
                        <i class="fa-solid fa-arrow-left text-sm"></i>
                    </div>
                    <span class="font-bold text-xs uppercase tracking-widest">Back to Shop</span>
                </a>
            </div>
            
            <div class="absolute left-1/2 -translate-x-1/2">
                <h1 class="text-2xl font-black tracking-tighter italic">B<span class="text-red-600">-</span>FRESH</h1>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-6 py-12 lg:py-20">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-16 items-center">
            
            <div class="lg:col-span-6 space-y-6">
                <div class="bg-white aspect-[4/5] flex items-center justify-center p-12 rounded-[40px] shadow-sm border border-gray-100 group overflow-hidden">
                    <img src="uploads/passion1.jpg" id="mainImg" class="h-full w-full object-contain transition-transform duration-700 group-hover:scale-110">
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div class="thumbnail active cursor-pointer bg-white p-4 rounded-3xl border border-gray-100 transition-all hover:shadow-lg" 
                        onclick="updateProduct('uploads/passion1.jpg', '<?php echo $product['Name']; ?>', 'Succulent mangoes paired with sour Passionfruit offers the greatest tasting combination.', '<?php echo $product['Price']; ?>', this)">
                        <img src="uploads/passion1.jpg" class="w-full h-20 object-contain">
                    </div>

                    <div class="thumbnail cursor-pointer bg-white p-4 rounded-3xl border border-gray-100 transition-all hover:shadow-lg" 
                        onclick="updateProduct('uploads/PASSION2.jpg', 'Special Passion Delight', 'Intense tropical flavor variant with extra vitamin C boost.', '300.00', this)">
                        <img src="uploads/PASSION2.jpg" class="w-full h-20 object-contain">
                    </div>

                    <div class="thumbnail cursor-pointer bg-white p-4 rounded-3xl border border-gray-100 transition-all hover:shadow-lg" 
                        onclick="updateProduct('uploads/passion3.jpg', 'Family Passion Delight', 'Fuel your body with natural nutrients and organic passionfruit extract.', '1450.00', this)">
                        <img src="uploads/passion3.jpg" class="w-full h-20 object-contain">
                    </div>
                </div>
            </div>

            <div class="lg:col-span-6">
                <div class="mb-8">
                    <div class="flex items-center gap-3">
                        <span class="text-red-600 font-extrabold uppercase tracking-[4px] text-[10px] bg-red-50 px-4 py-2 rounded-full">Premium Selection</span>
                        <div class="h-[2px] w-12 bg-red-100"></div>
                    </div>
                    
                    <h1 id="mainTitle" class="text-6xl md:text-7xl font-extrabold mt-6 mb-2 text-slate-900 leading-[0.9]">
                        <?php 
                            $name_parts = explode(' ', $product['Name']);
                            $last = array_pop($name_parts);
                            echo implode(' ', $name_parts) . ' <br><span class="serif-italic text-red-600">'.$last.'</span>';
                        ?>
                    </h1>

                    <div class="flex items-center gap-6 my-10">
                        <div class="flex flex-col">
                            <span class="text-gray-400 text-[10px] uppercase font-black tracking-widest mb-1">Price</span>
                            <div class="text-5xl font-black tracking-tighter text-slate-900">Rs. <span id="displayPrice"><?php echo number_format($product['Price'], 2); ?></span></div>
                        </div>
                        <div class="h-14 w-[1px] bg-gray-200"></div>
                        <div class="text-gray-400 font-bold text-xs uppercase tracking-widest leading-tight italic">Natural <br>& Organic</div>
                    </div>

                    <div id="productDesc" class="text-gray-500 text-lg mb-10 leading-relaxed border-l-4 border-red-100 pl-6 italic">
                        Succulent mangoes paired with sour Passionfruit offers the greatest tasting combination.
                    </div>
                    <div class="bg-white rounded-[30px] p-4 mb-8 border border-gray-100 shadow-sm flex items-center gap-6 group hover:shadow-md transition-all">
                        <div class="w-24 h-24 rounded-2xl overflow-hidden flex-shrink-0 bg-red-50 p-2">
                            <img src="uploads/passionfruitimg.jpg" class="w-full h-full object-cover rounded-xl group-hover:scale-110 transition-transform duration-500" alt="Benefit">
                        </div>
                        
                        <div class="flex-1">
                            <h4 class="text-xs font-black uppercase tracking-widest text-red-600 mb-1">Health Benefit</h4>
                            <p class="text-sm text-gray-600 font-semibold leading-snug">
                                A vibrant blend of locally grown, sun-ripened Passion Fruit that delivers a perfect balance of tangy and sweet.
                            </p>
                        </div>
                    </div>

                    <form id="addToCartForm" class="space-y-6">
                        <input type="hidden" name="product_id" value="<?php echo $product['Product_ID']; ?>">
                        <input type="hidden" name="selected_price" id="formPrice" value="<?php echo $product['Price']; ?>">
                        <input type="hidden" name="selected_name" id="formName" value="<?php echo $product['Name']; ?>">
                        
                        <div class="flex flex-col sm:flex-row gap-4">
                            <div class="flex items-center border-2 border-gray-100 rounded-[30px] px-6 bg-white hover:border-black transition-colors">
                                <label class="text-[10px] font-black uppercase text-gray-300 mr-4">Qty</label>
                                <input type="number" name="quantity" id="qty" value="1" min="1" class="w-16 py-6 bg-transparent font-black text-xl focus:outline-none">
                            </div>
                            <button type="submit" class="flex-1 bg-black text-white py-6 rounded-[30px] font-black text-xs uppercase tracking-[4px] hover:bg-red-600 transition-all shadow-2xl shadow-red-200 active:scale-95 group">
                                <i class="fa-solid fa-cart-plus mr-2 group-hover:animate-bounce"></i> Add to Cart
                            </button>
                        </div>
                    </form>

                    <button onclick="document.getElementById('reviewModal').classList.remove('hidden')" class="w-full mt-4 border-2 border-black text-black py-5 rounded-[30px] font-black text-[10px] uppercase tracking-[4px] hover:bg-black hover:text-white transition-all active:scale-95">
                        <i class="fa-solid fa-star mr-2"></i> Write a Review
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="reviewModal" class="hidden fixed inset-0 bg-black bg-opacity-60 z-[100] flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-white w-full max-w-2xl rounded-[40px] p-10 relative overflow-y-auto max-h-[90vh] shadow-2xl">
            <button onclick="document.getElementById('reviewModal').classList.add('hidden')" class="absolute top-8 right-8 text-gray-400 hover:text-black transition-colors">
                <i class="fa-solid fa-xmark text-2xl"></i>
            </button>

            <h2 class="text-3xl font-black mb-2 italic">Share Your <span class="text-red-600">Experience</span></h2>
            <p class="text-gray-400 text-sm mb-8 font-bold uppercase tracking-widest">B-FRESH Product Review</p>

            <form action="submit_feedback.php" method="POST" class="space-y-6">
                <input type="hidden" name="product_id" value="<?php echo $product['Product_ID']; ?>">

                <div>
                    <label class="text-[10px] font-black uppercase tracking-[3px] text-gray-400 mb-3 block">Select Your Bottle Size</label>
                    <div class="grid grid-cols-3 gap-4">
                        <label class="relative cursor-pointer group">
                            <input type="radio" name="product_size" value="Passion Delight" class="peer sr-only" required>
                            <div class="p-4 text-center bg-gray-50 rounded-2xl border-2 border-transparent peer-checked:border-red-500 peer-checked:bg-red-50 transition-all">
                                <span class="block font-black text-sm">Passion Delight</span>
                                <span class="text-[10px] text-gray-400 uppercase font-bold">150</span>
                            </div>
                        </label>
                        
                        <label class="relative cursor-pointer group">
                            <input type="radio" name="product_size" value="Special Passion Delight" class="peer sr-only">
                            <div class="p-4 text-center bg-gray-50 rounded-2xl border-2 border-transparent peer-checked:border-red-500 peer-checked:bg-red-50 transition-all">
                                <span class="block font-black text-sm">Special Passion Delight</span>
                                <span class="text-[10px] text-gray-400 uppercase font-bold">300</span>
                            </div>
                        </label>

                        <label class="relative cursor-pointer group">
                            <input type="radio" name="product_size" value="Familiy Passion Delight" class="peer sr-only">
                            <div class="p-4 text-center bg-gray-50 rounded-2xl border-2 border-transparent peer-checked:border-red-500 peer-checked:bg-red-50 transition-all">
                                <span class="block font-black text-sm">Familiy Passion Delight </span>
                                <span class="text-[10px] text-gray-400 uppercase font-bold">1450</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-[3px] text-gray-400 mb-2 block">Your Name</label>
                        <input type="text" name="customer_name" required placeholder="Full Name" class="w-full p-4 bg-gray-50 rounded-2xl border-none focus:ring-2 focus:ring-red-500 font-bold">
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-[3px] text-gray-400 mb-2 block">Email Address</label>
                        <input type="email" name="customer_email" required placeholder="email@example.com" class="w-full p-4 bg-gray-50 rounded-2xl border-none focus:ring-2 focus:ring-red-500 font-bold">
                    </div>
                </div>

                <div>
                    <label class="text-[10px] font-black uppercase tracking-[3px] text-gray-400 mb-2 block">Rating</label>
                    <select name="rating" class="w-full p-4 bg-gray-50 rounded-2xl border-none font-bold text-red-600">
                        <option value="5">★★★★★ - Excellent</option>
                        <option value="4">★★★★☆ - Very Good</option>
                        <option value="3">★★★☆☆ - Good</option>
                        <option value="2">★★☆☆☆ - Average</option>
                        <option value="1">★☆☆☆☆ - Poor</option>
                    </select>
                </div>

                <div>
                    <label class="text-[10px] font-black uppercase tracking-[3px] text-gray-400 mb-2 block">Your Message</label>
                    <textarea name="comment" rows="4" required placeholder="Tell us about the taste and quality..." class="w-full p-4 bg-gray-50 rounded-2xl border-none focus:ring-2 focus:ring-red-500 font-medium"></textarea>
                </div>

                <button type="submit" name="submit_review" class="w-full bg-black text-white py-5 rounded-[25px] font-black uppercase tracking-[4px] text-xs hover:bg-red-600 transition-all shadow-xl active:scale-95">
                    Submit Feedback
                </button>
            </form>
        </div>
    </div>

    <script>
    function updateProduct(img, title, desc, price, thumb) {
        document.getElementById('mainImg').src = img;
        document.getElementById('formPrice').value = price;
        document.getElementById('formName').value = title;
        
        let words = title.split(' ');
        let lastWord = words.pop();
        let firstPart = words.join(' ');
        document.getElementById('mainTitle').innerHTML = `${firstPart} <br><span class="serif-italic text-red-600">${lastWord}</span>`;
        
        document.getElementById('displayPrice').innerText = parseFloat(price).toLocaleString(undefined, {minimumFractionDigits: 2});
        document.getElementById('productDesc').innerText = desc;

        document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
        thumb.classList.add('active');
    }

    document.getElementById('addToCartForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('add_to_cart', 'true');

        fetch('cart.php', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                Swal.fire({
                    title: 'Added to Bag!',
                    text: formData.get('selected_name') + ' has been added.',
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonText: 'Continue Shopping',
                    cancelButtonText: 'View Bag',
                    confirmButtonColor: '#ef4444', 
                    cancelButtonColor: '#000'
                }).then((result) => {
                    if (result.dismiss === Swal.DismissReason.cancel) {
                        window.location.href = 'guest_checkout.php';
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("Item added to cart!"); 
        });
    });
    </script>
</body>
</html>