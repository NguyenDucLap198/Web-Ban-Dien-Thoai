<?php
require_once __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $action = (string) ($_POST['action'] ?? '');
    $cart = $_SESSION['cart'] ?? [];
    if ($productId && $action === 'remove') {
        unset($cart[$productId]);
        set_flash('Đã xóa sản phẩm khỏi giỏ hàng.');
    } elseif ($productId && $action === 'update') {
        $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
        $productQuery = $pdo->prepare('SELECT stock FROM products WHERE id = ?');
        $productQuery->execute([$productId]);
        $product = $productQuery->fetch();
        if (!$product || !$quantity || $quantity < 1 || $quantity > (int) $product['stock']) {
            set_flash('Số lượng cập nhật không hợp lệ.');
        } else {
            $cart[$productId] = $quantity;
            set_flash('Đã cập nhật giỏ hàng.');
        }
    }
    $_SESSION['cart'] = $cart;
    redirect('cart.php');
}

$cart = $_SESSION['cart'] ?? [];
$items = [];
$total = 0;
if ($cart) {
    $ids = array_values(array_filter(array_map('intval', array_keys($cart))));
    if ($ids) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $query = $pdo->prepare("SELECT id, name, brand, price, stock, image_url FROM products WHERE id IN ({$placeholders})");
        $query->execute($ids);
        foreach ($query->fetchAll() as $product) {
            $quantity = (int) ($cart[$product['id']] ?? 0);
            $product['quantity'] = $quantity;
            $product['subtotal'] = (float) $product['price'] * $quantity;
            $total += $product['subtotal'];
            $items[] = $product;
        }
    }
}

$pageTitle = 'Giỏ hàng | WebPhoneS';
require __DIR__ . '/includes/header.php';
?>
<section class="container">
    <div class="page-heading"><div class="eyebrow">Đơn hàng của bạn</div><h1>Giỏ hàng</h1><p>Kiểm tra sản phẩm trước khi đặt hàng.</p></div>
    <?php if (!$items): ?>
        <div class="empty-state">Giỏ hàng của bạn đang trống.<br><a class="button" style="margin-top:18px" href="products.php">Tiếp tục mua sắm</a></div>
    <?php else: ?>
        <div style="overflow-x:auto">
            <table class="cart-table">
                <thead><tr><th>Sản phẩm</th><th>Đơn giá</th><th>Số lượng</th><th>Tạm tính</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><a class="cart-product" href="product.php?id=<?= (int) $item['id'] ?>"><img src="<?= e($item['image_url']) ?>" alt=""><span><?= e($item['name']) ?></span></a></td>
                        <td><?= format_price($item['price']) ?></td>
                        <td>
                            <form class="inline-form" method="post">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="product_id" value="<?= (int) $item['id'] ?>"><input type="hidden" name="action" value="update">
                                <div class="form-field"><label class="visually-hidden" for="qty-<?= (int) $item['id'] ?>">Số lượng</label><input id="qty-<?= (int) $item['id'] ?>" type="number" name="quantity" min="1" max="<?= (int) $item['stock'] ?>" value="<?= (int) $item['quantity'] ?>"></div>
                                <button class="button secondary small" type="submit">Cập nhật</button>
                            </form>
                        </td>
                        <td><?= format_price($item['subtotal']) ?></td>
                        <td><form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="product_id" value="<?= (int) $item['id'] ?>"><input type="hidden" name="action" value="remove"><button class="button danger small" type="submit">Xóa</button></form></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="cart-total"><div><span>Tổng cộng</span><strong class="price"><?= format_price($total) ?></strong></div></div>
        <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:40px"><a class="button secondary" href="products.php">← Tiếp tục mua</a><a class="button" href="checkout.php">Tiến hành đặt hàng →</a></div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
