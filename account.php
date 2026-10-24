<?php
require_once __DIR__ . '/includes/bootstrap.php';
if (empty($_SESSION['customer_id'])) {
    redirect('login.php');
}

$customerQuery = $pdo->prepare('SELECT name, email, phone, address FROM customers WHERE id = ?');
$customerQuery->execute([(int) $_SESSION['customer_id']]);
$customer = $customerQuery->fetch();
if (!$customer) {
    unset($_SESSION['customer_id'], $_SESSION['customer_name'], $_SESSION['customer_email']);
    redirect('login.php');
}

$ordersQuery = $pdo->prepare('SELECT id, total, status, created_at FROM orders WHERE customer_id = ? ORDER BY created_at DESC, id DESC');
$ordersQuery->execute([(int) $_SESSION['customer_id']]);
$orders = $ordersQuery->fetchAll();
$pageTitle = 'Tài khoản của tôi | WebPhoneS';
require __DIR__ . '/includes/header.php';
?>
<section class="container">
    <div class="page-heading"><div class="eyebrow">Khu vực khách hàng</div><h1>Tài khoản của tôi</h1><p>Xin chào, <?= e($customer['name']) ?>.</p></div>
    <div class="account-grid">
        <section class="contact-card"><h2>Thông tin cá nhân</h2><p><strong>Họ tên</strong><br><?= e($customer['name']) ?></p><p><strong>Email</strong><br><?= e($customer['email']) ?></p><p><strong>Điện thoại</strong><br><?= e($customer['phone']) ?></p><p><strong>Địa chỉ</strong><br><?= e($customer['address'] !== '' ? $customer['address'] : 'Chưa cập nhật') ?></p><a class="button secondary" href="products.php">Tiếp tục mua sắm</a></section>
        <section><div class="section-head"><div><h2>Đơn hàng của tôi</h2><p>Lịch sử các đơn hàng đã đặt bằng tài khoản này.</p></div></div>
            <?php if ($orders): ?><div style="overflow-x:auto"><table class="admin-table"><thead><tr><th>Mã đơn</th><th>Ngày đặt</th><th>Tổng tiền</th><th>Trạng thái</th><th></th></tr></thead><tbody>
                <?php foreach ($orders as $order): ?><tr><td>#<?= (int) $order['id'] ?></td><td><?= e(date('d/m/Y H:i', strtotime($order['created_at']))) ?></td><td><?= format_price($order['total']) ?></td><td><?= e($order['status']) ?></td><td><a class="button secondary small" href="order.php?id=<?= (int) $order['id'] ?>">Chi tiết</a></td></tr><?php endforeach; ?>
            </tbody></table></div><?php else: ?><div class="empty-state">Bạn chưa có đơn hàng nào.</div><?php endif; ?>
        </section>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
