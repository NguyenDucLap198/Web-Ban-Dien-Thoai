<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (!empty($_SESSION['customer_id'])) {
    redirect('account.php');
}

$values = ['name' => '', 'email' => '', 'phone' => '', 'address' => ''];
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    foreach (array_keys($values) as $key) {
        $values[$key] = trim((string) ($_POST[$key] ?? ''));
    }
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    if ($values['name'] === '') $errors[] = 'Vui lòng nhập họ tên.';
    if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Địa chỉ email chưa hợp lệ.';
    if (!preg_match('/^[0-9+\s().-]{9,20}$/', $values['phone'])) $errors[] = 'Số điện thoại chưa hợp lệ.';
    if (strlen($password) < 8) $errors[] = 'Mật khẩu phải có ít nhất 8 ký tự.';
    if ($password !== $confirmPassword) $errors[] = 'Mật khẩu xác nhận không trùng khớp.';

    if (!$errors) {
        try {
            $pdo->beginTransaction();
            $insert = $pdo->prepare('INSERT INTO customers (name, email, phone, address, password_hash) VALUES (?, ?, ?, ?, ?)');
            $insert->execute([
                $values['name'],
                $values['email'],
                $values['phone'],
                $values['address'],
                password_hash($password, PASSWORD_DEFAULT),
            ]);
            $customerId = (int) $pdo->lastInsertId();
            $pdo->commit();

            session_regenerate_id(true);
            $_SESSION['customer_id'] = $customerId;
            $_SESSION['customer_name'] = $values['name'];
            $_SESSION['customer_email'] = $values['email'];
            redirect('account.php');
        } catch (PDOException $exception) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            if ($exception->getCode() === '23000') {
                $errors[] = 'Email này đã được đăng ký. Vui lòng đăng nhập hoặc dùng email khác.';
            } else {
                throw $exception;
            }
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $exception;
        }
    }
}

$pageTitle = 'Đăng ký | WebPhoneS';
require __DIR__ . '/includes/header.php';
?>
<section class="container">
    <form class="panel" method="post">
        <div class="eyebrow">Tạo tài khoản mới</div>
        <h1 style="font-size:32px">Đăng ký khách hàng</h1>
        <p class="panel-intro">Tạo tài khoản để theo dõi đơn hàng và mua sắm thuận tiện hơn.</p>
        <?php if ($errors): ?><div class="error"><?php foreach ($errors as $error): ?><div><?= e($error) ?></div><?php endforeach; ?></div><?php endif; ?>
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <div class="form-field"><label for="name">Họ và tên</label><input id="name" name="name" value="<?= e($values['name']) ?>" autocomplete="name" required></div>
        <div class="form-field"><label for="email">Email</label><input id="email" type="email" name="email" value="<?= e($values['email']) ?>" autocomplete="email" required></div>
        <div class="form-field"><label for="phone">Số điện thoại</label><input id="phone" name="phone" value="<?= e($values['phone']) ?>" autocomplete="tel" required></div>
        <div class="form-field"><label for="address">Địa chỉ (không bắt buộc)</label><input id="address" name="address" value="<?= e($values['address']) ?>" autocomplete="street-address"></div>
        <div class="form-field"><label for="password">Mật khẩu</label><input id="password" type="password" name="password" minlength="8" autocomplete="new-password" required></div>
        <div class="form-field"><label for="confirm_password">Nhập lại mật khẩu</label><input id="confirm_password" type="password" name="confirm_password" minlength="8" autocomplete="new-password" required></div>
        <button class="button" type="submit">Tạo tài khoản</button>
        <p style="margin:18px 0 0">Đã có tài khoản? <a class="text-link" href="login.php">Đăng nhập</a></p>
    </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
