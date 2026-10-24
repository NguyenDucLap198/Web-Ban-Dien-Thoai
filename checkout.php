<?php
require_once __DIR__ . '/includes/bootstrap.php';

$cart = $_SESSION['cart'] ?? [];
$successId = filter_input(INPUT_GET, 'success', FILTER_VALIDATE_INT);
if (!$cart && !$successId) {
    redirect('cart.php');
}

$errors = [];
$values = [
    'customer_name' => (string) ($_SESSION['customer_name'] ?? ''),
    'phone' => '',
    'email' => (string) ($_SESSION['customer_email'] ?? ''),
    'address' => '',
    'note' => '',
];
if (!empty($_SESSION['customer_id'])) {
    $customerQuery = $pdo->prepare('SELECT phone, address FROM customers WHERE id = ?');
    $customerQuery->execute([(int) $_SESSION['customer_id']]);
    $customer = $customerQuery->fetch();
    if ($customer) {
        $values['phone'] = $customer['phone'];
        $values['address'] = $customer['address'];
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    foreach ($values as $key => $value) {
        $values[$key] = trim((string) ($_POST[$key] ?? $value));
    }
    if ($values['customer_name'] === '') $errors[] = 'Vui lòng nhập họ tên.';
    if (!preg_match('/^[0-9+\s().-]{9,20}$/', $values['phone'])) $errors[] = 'Số điện thoại chưa hợp lệ.';
    if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Địa chỉ email chưa hợp lệ.';
    if ($values['address'] === '') $errors[] = 'Vui lòng nhập địa chỉ nhận hàng.';

    if (!$errors) {
        try {
            $pdo->beginTransaction();
            $productIds = array_values(array_filter(array_map('intval', array_keys($cart))));
            $placeholders = implode(',', array_fill(0, count($productIds), '?'));
            $productsQuery = $pdo->prepare("SELECT id, name, price, stock FROM products WHERE id IN ({$placeholders}) FOR UPDATE");
            $productsQuery->execute($productIds);
            $products = $productsQuery->fetchAll();
            $byId = [];
            foreach ($products as $product) $byId[(int) $product['id']] = $product;

            $total = 0;
            foreach ($cart as $id => $quantity) {
                $id = (int) $id;
                $quantity = (int) $quantity;
                if (!isset($byId[$id]) || $quantity < 1 || $quantity > (int) $byId[$id]['stock']) {
                    throw new RuntimeException('Sản phẩm trong giỏ vừa hết hàng hoặc không đủ số lượng. Vui lòng kiểm tra lại.');
                }
                $total += (float) $byId[$id]['price'] * $quantity;
            }

            $orderQuery = $pdo->prepare('INSERT INTO orders (customer_id, customer_name, phone, email, address, note, total) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $orderQuery->execute([$_SESSION['customer_id'] ?? null, $values['customer_name'], $values['phone'], $values['email'], $values['address'], $values['note'], $total]);
            $orderId = (int) $pdo->lastInsertId();
            $itemQuery = $pdo->prepare('INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity) VALUES (?, ?, ?, ?, ?)');
            $stockQuery = $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?');
            foreach ($cart as $id => $quantity) {
                $product = $byId[(int) $id];
                $itemQuery->execute([$orderId, $product['id'], $product['name'], $product['price'], $quantity]);
                $stockQuery->execute([$quantity, $product['id'], $quantity]);
                if ($stockQuery->rowCount() !== 1) {
                    throw new RuntimeException('Tồn kho vừa thay đổi. Vui lòng kiểm tra lại giỏ hàng.');
                }
            }
            $pdo->commit();
            unset($_SESSION['cart']);
            redirect('checkout.php?success=' . $orderId);
        } catch (RuntimeException $exception) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $errors[] = $exception->getMessage();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $exception;
        }
    }
}

$pageTitle = 'Thanh toán | WebPhoneS';
require __DIR__ . '/includes/header.php';
?>
<section class="container">
    <?php if ($successId): ?>
        <?php $orderQuery = $pdo->prepare('SELECT id, total FROM orders WHERE id = ?'); $orderQuery->execute([$successId]); $order = $orderQuery->fetch(); ?>
        <?php if ($order): ?>
            <div class="panel" style="text-align:center"><div class="eyebrow">Đặt hàng thành công</div><h1>Cảm ơn bạn đã mua hàng!</h1><p class="panel-intro">Mã đơn hàng của bạn là <strong>#<?= (int) $order['id'] ?></strong>. Tổng giá trị: <strong><?= format_price($order['total']) ?></strong>.</p><a class="button" href="products.php">Tiếp tục mua sắm</a></div>
        <?php endif; ?>
    <?php else: ?>
        <div class="page-heading"><div class="eyebrow">Bước cuối cùng</div><h1>Thông tin nhận hàng</h1><p>Điền thông tin để chúng tôi giao hàng đến bạn.</p></div>
        <div class="contact-grid">
            <form class="panel" method="post" style="margin:0">
                <?php if ($errors): ?><div class="error"><?php foreach ($errors as $error): ?><div><?= e($error) ?></div><?php endforeach; ?></div><?php endif; ?>
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <div class="form-field"><label for="customer_name">Họ và tên</label><input id="customer_name" name="customer_name" value="<?= e($values['customer_name']) ?>" required></div>
                <div class="form-field"><label for="phone">Số điện thoại</label><input id="phone" name="phone" value="<?= e($values['phone']) ?>" required></div>
                <div class="form-field"><label for="email">Email</label><input id="email" type="email" name="email" value="<?= e($values['email']) ?>" required></div>
                <div class="form-field"><label for="address">Địa chỉ nhận hàng</label><input id="address" name="address" value="<?= e($values['address']) ?>" required></div>
                <div class="form-field"><label for="note">Ghi chú (không bắt buộc)</label><textarea id="note" name="note" rows="3"><?= e($values['note']) ?></textarea></div>
                <button class="button" type="submit">Xác nhận đặt hàng</button>
            </form>
            <div class="contact-card"><h2>Đơn hàng</h2>
                <?php $total = 0; foreach ($cart as $id => $quantity): ?>
                    <?php $itemQuery = $pdo->prepare('SELECT name, price FROM products WHERE id = ?'); $itemQuery->execute([(int) $id]); $item = $itemQuery->fetch(); if (!$item) continue; $subtotal = (float) $item['price'] * (int) $quantity; $total += $subtotal; ?>
                    <p><?= e($item['name']) ?> × <?= (int) $quantity ?><br><strong><?= format_price($subtotal) ?></strong></p>
                <?php endforeach; ?>
                <hr><strong>Tổng cộng: <?= format_price($total) ?></strong><p style="margin:12px 0 0;color:var(--muted)">Thanh toán khi nhận hàng.</p>
            </div>
        </div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
