<?php
require_once __DIR__ . '/includes/bootstrap.php';

$productId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$statement = $pdo->prepare('SELECT * FROM products WHERE id = ?');
$statement->execute([$productId ?: 0]);
$product = $statement->fetch();
if (!$product) {
    http_response_code(404);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $product) {
    verify_csrf();
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
    $quantity = $quantity === false || $quantity === null ? 1 : $quantity;
    if ($quantity < 1 || $quantity > (int) $product['stock']) {
        set_flash('Số lượng bạn chọn không hợp lệ hoặc vượt quá tồn kho.');
    } else {
        $cart = $_SESSION['cart'] ?? [];
        $newQuantity = (int) ($cart[$productId] ?? 0) + $quantity;
        if ($newQuantity > (int) $product['stock']) {
            set_flash('Số lượng trong giỏ vượt quá tồn kho hiện tại.');
        } else {
            $cart[$productId] = $newQuantity;
            $_SESSION['cart'] = $cart;
            set_flash('Đã thêm sản phẩm vào giỏ hàng.');
        }
    }
    redirect('cart.php');
}

$pageTitle = $product ? $product['name'] . ' | WebPhoneS' : 'Không tìm thấy sản phẩm | WebPhoneS';
require __DIR__ . '/includes/header.php';
?>
<section class="container">
    <?php if (!$product): ?>
        <div class="empty-state" style="margin:50px 0">Sản phẩm không tồn tại hoặc đã được gỡ bỏ. <a class="text-link" href="products.php">Quay lại danh sách</a></div>
    <?php else: ?>
        <div class="detail">
            <img class="detail-image" src="<?= e($product['image_url']) ?>" alt="<?= e($product['name']) ?>">
            <div>
                <div class="eyebrow"><a href="products.php?<?= e(http_build_query(['cat' => $product['category']])) ?>"><?= e($product['category']) ?></a> · <?= e($product['brand']) ?></div>
                <h1><?= e($product['name']) ?></h1>
                <div class="price"><?= format_price($product['price']) ?></div>
                <p class="detail-description"><?= e($product['description']) ?></p>
                <p class="stock"><?= (int) $product['stock'] > 0 ? 'Còn hàng: ' . (int) $product['stock'] . ' sản phẩm' : 'Sản phẩm tạm hết hàng' ?></p>
                <?php if ((int) $product['stock'] > 0): ?>
                    <form method="post" class="inline-form">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <div class="form-field"><label for="quantity">Số lượng</label><input id="quantity" type="number" name="quantity" value="1" min="1" max="<?= (int) $product['stock'] ?>" required></div>
                        <button class="button" type="submit">Thêm vào giỏ</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
