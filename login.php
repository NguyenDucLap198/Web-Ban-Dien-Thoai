<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (!empty($_SESSION['customer_id'])) {
    redirect('account.php');
}

$email = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $query = $pdo->prepare('SELECT id, name, email, password_hash FROM customers WHERE email = ?');
    $query->execute([$email]);
    $customer = $query->fetch();
    if ($customer && password_verify($password, $customer['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['customer_id'] = (int) $customer['id'];
        $_SESSION['customer_name'] = $customer['name'];
        $_SESSION['customer_email'] = $customer['email'];
        redirect('account.php');
    }
    $error = 'Email hoặc mật khẩu không đúng.';
}

$pageTitle = 'Đăng nhập | WebPhoneS';
require __DIR__ . '/includes/header.php';
?>
<section class="container">
    <form class="panel" method="post">
        <div class="eyebrow">Chào mừng bạn trở lại</div>
        <h1 style="font-size:32px">Đăng nhập khách hàng</h1>
        <p class="panel-intro">Đăng nhập để xem thông tin tài khoản và đơn hàng của bạn.</p>
        <?php if ($error): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <div class="form-field"><label for="email">Email</label><input id="email" type="email" name="email" value="<?= e($email) ?>" autocomplete="email" required></div>
        <div class="form-field"><label for="password">Mật khẩu</label><input id="password" type="password" name="password" autocomplete="current-password" required></div>
        <button class="button" type="submit">Đăng nhập</button>
        <p style="margin:18px 0 0">Chưa có tài khoản? <a class="text-link" href="register.php">Đăng ký ngay</a></p>
    </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
