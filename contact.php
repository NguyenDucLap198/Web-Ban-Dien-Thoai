<?php
require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Liên hệ | WebPhoneS';
$activePage = 'contact';
$errors = [];
$values = ['name' => '', 'email' => '', 'subject' => '', 'message' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    foreach ($values as $key => $value) $values[$key] = trim((string) ($_POST[$key] ?? $value));
    if ($values['name'] === '') $errors[] = 'Vui lòng nhập họ tên.';
    if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Địa chỉ email chưa hợp lệ.';
    if ($values['subject'] === '') $errors[] = 'Vui lòng nhập chủ đề.';
    if ($values['message'] === '') $errors[] = 'Vui lòng nhập nội dung.';
    if (!$errors) {
        $insert = $pdo->prepare('INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)');
        $insert->execute(array_values($values));
        set_flash('Cảm ơn bạn đã liên hệ. Chúng tôi sẽ phản hồi sớm nhất có thể.');
        redirect('contact.php');
    }
}
require __DIR__ . '/includes/header.php';
?>
<section class="container">
    <div class="page-heading"><div class="eyebrow">Chúng tôi luôn sẵn sàng lắng nghe</div><h1>Liên hệ</h1><p>Gửi câu hỏi cho WebPhoneS, đội ngũ của chúng tôi sẽ hỗ trợ bạn.</p></div>
    <div class="contact-grid">
        <div class="contact-card"><h2>Thông tin cửa hàng</h2><p><strong>Hotline</strong><br>1900 2026</p><p><strong>Email</strong><br>hello@webphones.local</p><p><strong>Giờ làm việc</strong><br>Thứ Hai – Chủ Nhật, 08:00 – 21:00</p></div>
        <form class="panel" method="post" style="margin:0">
            <?php if ($errors): ?><div class="error"><?php foreach ($errors as $error): ?><div><?= e($error) ?></div><?php endforeach; ?></div><?php endif; ?>
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <div class="form-field"><label for="name">Họ và tên</label><input id="name" name="name" value="<?= e($values['name']) ?>" required></div>
            <div class="form-field"><label for="email">Email</label><input id="email" type="email" name="email" value="<?= e($values['email']) ?>" required></div>
            <div class="form-field"><label for="subject">Chủ đề</label><input id="subject" name="subject" value="<?= e($values['subject']) ?>" required></div>
            <div class="form-field"><label for="message">Nội dung</label><textarea id="message" name="message" rows="5" required><?= e($values['message']) ?></textarea></div>
            <button class="button" type="submit">Gửi liên hệ</button>
        </form>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
