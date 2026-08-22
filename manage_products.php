<?php
include 'db_config.php';

// 1. ADD NEW PRODUCT WITH IMAGE
if (isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $category = $_POST['category']; 
    $price = $_POST['price'];
    
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $image_name = basename($_FILES["product_image"]["name"]);
    $target_file = $target_dir . time() . "_" . $image_name;
    
    if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
        $sql = "INSERT INTO product (Name, Category, Price, Image, status) VALUES ('$name', '$category', '$price', '$target_file', 1)";
        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('Product added successfully!'); window.location='manage_products.php';</script>";
        }
    }
}

// --- NEW: UPDATE PRODUCT LOGIC ---
if (isset($_POST['update_product'])) {
    $id = $_POST['product_id'];
    $name = $_POST['name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    
    $sql = "UPDATE product SET Name='$name', Category='$category', Price='$price' WHERE Product_ID=$id";
    
    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Product updated successfully!'); window.location='manage_products.php';</script>";
    }
}

// 2. TOGGLE STATUS
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $new_status = ($_GET['action'] == 'deactivate') ? 0 : 1;
    $conn->query("UPDATE product SET status = $new_status WHERE Product_ID = $id");
    header("Location: manage_products.php");
}

// 3. GET DATA FOR SEPARATE TABLES
$active_products = $conn->query("SELECT * FROM product WHERE status = 1");
$inactive_products = $conn->query("SELECT * FROM product WHERE status = 0");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Bfresh - Product Management</title>
    <style>
        .product-img { width: 50px; height: 50px; object-fit: cover; }
        h2 { background: #eee; padding: 10px; }
        table { margin-bottom: 30px; border-collapse: collapse; }
        input, select, button { padding: 8px; margin-right: 5px; }

        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap');

:root {
    --primary: #f1b300;
    --success: #2ecc71;
    --danger: #e74c3c;
    --dark-bg: #090d16;
    --card-bg: rgba(30, 41, 59, 0.7);
    --text-main: #f8fafc;
    --text-dim: #94a3b8;
    --transition: all 0.3s ease;
}

body {
    background-color: var(--dark-bg);
    background-image: radial-gradient(circle at 100% 0%, rgba(241, 179, 0, 0.1) 0%, transparent 25%);
    color: var(--text-main);
    font-family: 'Plus Jakarta Sans', sans-serif;
    margin: 0;
    padding: 40px;
}

h1, h2, h3 { font-weight: 800; letter-spacing: -1px; }
h1 { color: var(--primary); text-transform: uppercase; font-size: 2.2rem; }

/* Navigation */
nav { margin-bottom: 30px; }
nav a {
    color: var(--text-dim);
    text-decoration: none;
    font-weight: 600;
    margin-right: 20px;
    transition: var(--transition);
}
nav a:hover { color: var(--primary); }

/* Form Design */
form.add-product-form {
    background: var(--card-bg);
    backdrop-filter: blur(10px);
    padding: 30px;
    border-radius: 24px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    align-items: center;
    margin-bottom: 40px;
}

input, select {
    background: rgba(15, 23, 42, 0.8) !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    color: white !important;
    padding: 12px 18px !important;
    border-radius: 12px !important;
    outline: none;
    transition: var(--transition);
}

input:focus, select:focus { border-color: var(--primary); box-shadow: 0 0 10px rgba(241, 179, 0, 0.2); }

button {
    background: var(--primary);
    color: #000;
    border: none;
    padding: 12px 25px;
    border-radius: 12px;
    font-weight: 800;
    cursor: pointer;
    transition: var(--transition);
}

button:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(241, 179, 0, 0.3); }

/* Table Design */
.table-container {
    background: var(--card-bg);
    border-radius: 24px;
    padding: 25px;
    border: 1px solid rgba(255, 255, 255, 0.05);
    margin-bottom: 40px;
}

table { width: 100%; border-collapse: collapse; }
th {
    text-align: left;
    padding: 15px;
    color: var(--text-dim);
    font-size: 0.8rem;
    text-transform: uppercase;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

td { padding: 15px; border-bottom: 1px solid rgba(255, 255, 255, 0.05); }

.product-img {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    object-fit: cover;
    border: 2px solid rgba(255, 255, 255, 0.1);
}

/* Status Badges */
.status-active { color: var(--success); font-weight: 700; }
.status-inactive { color: var(--danger); font-weight: 700; }

.action-link {
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 700;
    padding: 5px 12px;
    border-radius: 8px;
    transition: var(--transition);
}
    </style>
</head>
<body>
    <div style="max-width: 1200px; margin: 0 auto;">
        <h1>Product Management</h1>
        <nav>
            <a href="admin_dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a>
           
             <a href="manage_inventory.php"><i class="fas fa-boxes"></i> manage_inventory</a>
             <a href="orders.php">Manage Orders</a>
        <a href="reviews.php">View Detailed Reports</a>
        <a href="manage_promotions.php">promotions</a>
        <a href="logout.php">Logout</a>
        </nav>

        <div class="table-container">
            <h3>Add New Beverage</h3>
            <form method="POST" enctype="multipart/form-data" class="add-product-form">
                <input type="text" name="name" placeholder="Product Name" required>
                
                <select name="category" required>
                    <option value="">-- Category --</option>
                    <option value="Fresh Fruit">Fresh Fruit</option>
                    <option value="Family Size">Family Size</option>
                    <option value="Organic Mix">Organic Mix</option>
                    <option value="Smoothies">Smoothies</option>
                    <option value="Energy Drinks">Energy Drinks</option>
                    <option value="Seasonal Specials">Seasonal Specials</option>
                </select>

                <input type="number" step="0.01" name="price" placeholder="Price (Rs)" required>
                <div style="display: flex; flex-direction: column;">
                    <label style="font-size: 0.7rem; color: var(--text-dim); margin-bottom: 5px;">Product Image</label>
                    <input type="file" name="product_image" accept="image/*" required style="padding: 8px;">
                </div>
                <button type="submit" name="add_product">Save Product</button>
            </form>
        </div>

        <div class="table-container" style="border-top: 4px solid var(--success);">
            <h2 class="status-active">✅ Active Products</h2>
            <table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $active_products->fetch_assoc()) { ?>
                    <tr>
                        <form method="POST">
                            <td><img src="<?php echo $row['image']; ?>" class="product-img"></td>
                            <td><input type="text" name="name" value="<?php echo $row['Name']; ?>" style="width: 150px;"></td>
                            <td>
                                <select name="category">
                                    <option value="Fresh Fruit" <?php if($row['Category'] == 'Fresh Fruit') echo 'selected'; ?>>Fresh Fruit</option>
                                    <option value="Family Size" <?php if($row['Category'] == 'Family Size') echo 'selected'; ?>>Family Size</option>
                                    <option value="Organic Mix" <?php if($row['Category'] == 'Organic Mix') echo 'selected'; ?>>Organic Mix</option>
                                    <option value="Smoothies" <?php if($row['Category'] == 'Smoothies') echo 'selected'; ?>>Smoothies</option>
                                    <option value="Energy Drinks" <?php if($row['Category'] == 'Energy Drinks') echo 'selected'; ?>>Energy Drinks</option>
                                    <option value="Seasonal Specials" <?php if($row['Category'] == 'Seasonal Specials') echo 'selected'; ?>>Seasonal Specials</option>
                                </select>
                            </td>
                            <td><input type="number" step="0.01" name="price" value="<?php echo $row['Price']; ?>" style="width: 100px;"></td>
                            <td>
                                <input type="hidden" name="product_id" value="<?php echo $row['Product_ID']; ?>">
                                <button type="submit" name="update_product" style="background: var(--primary); padding: 8px 15px; font-size: 0.8rem;">Update</button>
                                <a href="manage_products.php?action=deactivate&id=<?php echo $row['Product_ID']; ?>" class="action-link" style="color: var(--danger);">Deactivate</a>
                            </td>
                        </form>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <div class="table-container" style="border-top: 4px solid var(--danger); opacity: 0.8;">
            <h2 class="status-inactive">🚫 Discontinued Products</h2>
            <table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $inactive_products->fetch_assoc()) { ?>
                    <tr>
                        <td><img src="<?php echo $row['image']; ?>" class="product-img" style="filter: grayscale(1);"></td>
                        <td><?php echo $row['Name']; ?></td>
                        <td><?php echo $row['Category']; ?></td>
                        <td>Rs. <?php echo number_format($row['Price'], 2); ?></td>
                        <td>
                            <a href="manage_products.php?action=activate&id=<?php echo $row['Product_ID']; ?>" class="action-link" style="color: var(--success); border: 1px solid var(--success);">Activate Again</a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>