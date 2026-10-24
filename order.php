<?php
require_once __DIR__ . '/includes/bootstrap.php';
if (empty($_SESSION['customer_id'])) {
    redirect('login.php');
}

$orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$orderQuery = $pdo->prepare('SELECT id, total, status, created_at FROM orders WHERE id = ? AND customer_id = ?');
$orderQuery->execute([$orderId ?: 0, (int) $_SESSION['customer_id']]);
$order = $orderQuery->fetch();
if (!$order) {
    http_response_code(404);
}
$items = [];
if ($order) {
    $itemsQuery = $pdo->prepare('SELECT product_name, unit_price, quantity FROM order_items WHERE order_id = ?');
    $itemsQuery->execute([(int) $order['id']]);
    $items = $itemsQuery->fetchAll();
}

$pageTitle = $order ? 'Đơn hàng #' . (int) $order['id'] . ' | WebPhoneS' : 'Không tìm thấy đơn hàng | WebPhoneS';
require __DIR__ . '/includes/header.php';
?>
<section class="container">
    <?php if (!$order): ?><div class="empty-state" style="margin:48px 0">Không tìm thấy đơn hàng. <a class="text-link" href="account.php">Quay lại tài khoản</a></div>
    <?php else: ?>
        <div class="page-heading"><div class="eyebrow">Chi tiết đơn hàng</div><h1>Đơn hàng #<?= (int) $order['id'] ?></h1><p>Đặt ngày <?= e(date('d/m/Y H:i', strtotime($order['created_at']))) ?> · <?= e($order['status']) ?></p></div>
        <div style="overflow-x:auto"><table class="cart-table"><thead><tr><th>Sản phẩm</th><th>Đơn giá</th><th>Số lượng</th><th>Tạm tính</th></tr></thead><tbody>
            <?php foreach ($items as $item): ?><tr><td><?= e($item['product_name']) ?></td><td><?= format_price($item['unit_price']) ?></td><td><?= (int) $item['quantity'] ?></td><td><?= format_price((float) $item['unit_price'] * (int) $item['quantity']) ?></td></tr><?php endforeach; ?>
        </tbody></table></div>
        <div class="cart-total"><div><span>Tổng cộng</span><strong class="price"><?= format_price($order['total']) ?></strong></div></div>
        <a class="button secondary" href="account.php">← Quay lại tài khoản</a>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
