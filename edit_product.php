<?php
session_start();
$conn = new mysqli("localhost", "root", "", "bfresh_db");

$id = $_GET['id'];
$res = $conn->query("SELECT * FROM product WHERE Product_ID = $id");
$p = $res->fetch_assoc();

if (isset($_POST['update_product'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $price = $_POST['price'];
    $category = $_POST['category'];
    $stock = $_POST['stock'];

    $sql = "UPDATE product SET Name='$name', Price='$price', Category='$category', Stock_Level='$stock' WHERE Product_ID=$id";
    
    if ($conn->query($sql)) {
        header("Location: manage_products.php?msg=updated");
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Edit Product | Bfresh</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="p-5 bg-light">
    <div class="container bg-white p-5 shadow rounded-4" style="max-width: 500px;">
        <h3 class="fw-bold mb-4 text-warning">Update Product</h3>
        <form method="POST">
            <div class="mb-3"><label>Product Name</label><input type="text" name="name" class="form-control" value="<?php echo $p['Name']; ?>"></div>
            <div class="mb-3"><label>Price (Rs.)</label><input type="number" name="price" class="form-control" value="<?php echo $p['Price']; ?>"></div>
            <div class="mb-3"><label>Category</label>
                <select name="category" class="form-select">
                    <option value="Fresh Fruit" <?php if($p['Category']=='Fresh Fruit') echo 'selected'; ?>>Fresh Fruit</option>
                    <option value="Organic Mix" <?php if($p['Category']=='Organic Mix') echo 'selected'; ?>>Organic Mix</option>
                </select>
            </div>
            <div class="mb-4"><label>Stock Level</label><input type="number" name="stock" class="form-control" value="<?php echo $p['Stock_Level']; ?>"></div>
            <button type="submit" name="update_product" class="btn btn-dark w-100 fw-bold rounded-pill">Save Changes</button>
            <a href="manage_products.php" class="btn btn-link w-100 mt-2 text-decoration-none text-muted">Go Back</a>
        </form>
    </div>
</body>
</html>