<?php
require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'WebPhoneS - Thiết bị công nghệ chính hãng';
$activePage = 'home';
$featured = $pdo->query('SELECT id, name, brand, category, price, stock, image_url FROM products ORDER BY id DESC LIMIT 3')->fetchAll();
$categories = product_categories();
$categoryCounts = array_fill_keys($categories, 0);
foreach ($pdo->query('SELECT category, COUNT(*) AS product_count FROM products GROUP BY category')->fetchAll() as $row) {
    if (isset($categoryCounts[$row['category']])) $categoryCounts[$row['category']] = (int) $row['product_count'];
}
$categoryIcons = ['Điện thoại' => '▯', 'Máy tính bảng' => '▤', 'Laptop' => '▰', 'Đồng hồ thông minh' => '◷', 'Tai nghe & Loa' => '♫', 'Phụ kiện' => '⌁', 'Gaming' => '▧'];
require __DIR__ . '/includes/header.php';
?>
<section class="hero">
    <div class="container hero-inner">
        <div>
            <div class="eyebrow">Công nghệ trong tầm tay</div>
            <h1>Công nghệ phù hợp, trải nghiệm khác biệt.</h1>
            <p class="hero-copy">Khám phá điện thoại, máy tính và phụ kiện chính hãng — dễ dàng tìm đúng sản phẩm cho nhu cầu của bạn.</p>
            <form class="home-search-form" action="products.php" method="get">
                <label class="home-search-box"><span aria-hidden="true">⌕</span><input type="search" name="q" placeholder="Bạn đang tìm sản phẩm nào?" aria-label="Tìm sản phẩm"></label>
                <button class="button" type="submit">Tìm kiếm</button>
            </form>
            <a class="home-browse-link" href="products.php">Khám phá tất cả sản phẩm <span aria-hidden="true">→</span></a>
        </div>
        <img class="hero-image" src="https://images.unsplash.com/photo-1598327105666-5b89351aff97?auto=format&fit=crop&w=1100&q=85" alt="Điện thoại thông minh hiện đại">
    </div>
</section>
<section class="container home-category-section">
    <div class="section-head"><div><div class="eyebrow">MUA SẮM THEO NHU CẦU</div><h2>Danh mục công nghệ</h2><p>Chọn nhanh nhóm sản phẩm bạn đang quan tâm.</p></div><a class="text-link" href="products.php">Tất cả danh mục →</a></div>
    <nav class="home-category-grid" aria-label="Danh mục sản phẩm">
        <?php foreach ($categories as $category): ?>
            <a class="home-category-card" href="products.php?<?= e(http_build_query(['cat' => $category])) ?>">
                <span class="home-category-icon" aria-hidden="true"><?= e($categoryIcons[$category]) ?></span>
                <strong><?= e($category) ?></strong>
                <span><?= $categoryCounts[$category] ?> sản phẩm <b aria-hidden="true">→</b></span>
            </a>
        <?php endforeach; ?>
    </nav>
</section>
<section class="container section">
    <div class="section-head">
        <div><h2>Sản phẩm nổi bật</h2><p>Lựa chọn đáng chú ý dành cho bạn</p></div>
        <a class="text-link" href="products.php">Xem tất cả →</a>
    </div>
    <?php if ($featured): ?>
        <div class="product-grid">
            <?php foreach ($featured as $product): ?>
                <article class="product-card">
                    <a href="product.php?id=<?= (int) $product['id'] ?>">
                        <img src="<?= e($product['image_url']) ?>" alt="<?= e($product['name']) ?>">
                        <div class="product-info">
                            <div class="product-brand"><?= e($product['brand']) ?> · <?= e($product['category']) ?></div>
                            <h3><?= e($product['name']) ?></h3>
                            <div class="price"><?= format_price($product['price']) ?></div>
                            <div class="product-bottom"><span class="stock"><?= (int) $product['stock'] > 0 ? 'Còn hàng' : 'Tạm hết hàng' ?></span><span class="text-link">Chi tiết →</span></div>
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">Chưa có sản phẩm. Quản trị viên có thể thêm sản phẩm trong trang quản lý.</div>
    <?php endif; ?>
</section>
<section class="container benefits">
    <div class="benefit"><b>✓ Sản phẩm chính hãng</b><p>Thông tin rõ ràng, nguồn gốc đáng tin cậy.</p></div>
    <div class="benefit"><b>↗ Giao hàng toàn quốc</b><p>Đặt hàng đơn giản, nhận hàng tận nơi.</p></div>
    <div class="benefit"><b>♡ Hỗ trợ tận tâm</b><p>Chúng tôi luôn sẵn sàng giải đáp thắc mắc.</p></div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
