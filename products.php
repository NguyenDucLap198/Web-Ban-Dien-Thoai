<?php
require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Sản phẩm công nghệ | WebPhoneS';
$activePage = 'products';
$search = trim((string) ($_GET['q'] ?? ''));
$categories = product_categories();
$requestedCategory = trim((string) ($_GET['cat'] ?? ''));
$selectedCategory = in_array($requestedCategory, $categories, true) ? $requestedCategory : '';
$sortOptions = [
    'newest' => 'Mới nhất',
    'price_low' => 'Giá tăng dần',
    'price_high' => 'Giá giảm dần',
];
$sort = (string) ($_GET['sort'] ?? 'newest');
if (!isset($sortOptions[$sort])) $sort = 'newest';
$sortSql = [
    'newest' => 'id DESC',
    'price_low' => 'price ASC, id DESC',
    'price_high' => 'price DESC, id DESC',
];
$categoryCounts = array_fill_keys($categories, 0);
foreach ($pdo->query('SELECT category, COUNT(*) AS product_count FROM products GROUP BY category')->fetchAll() as $row) {
    if (isset($categoryCounts[$row['category']])) $categoryCounts[$row['category']] = (int) $row['product_count'];
}
$conditions = [];
$parameters = [];
if ($search !== '') {
    $conditions[] = '(name LIKE :name_query OR brand LIKE :brand_query OR category LIKE :category_query)';
    $query = '%' . $search . '%';
    $parameters += ['name_query' => $query, 'brand_query' => $query, 'category_query' => $query];
}
if ($selectedCategory !== '') {
    $conditions[] = 'category = :selected_category';
    $parameters['selected_category'] = $selectedCategory;
}
$sql = 'SELECT id, name, brand, category, price, stock, image_url FROM products';
if ($conditions) $sql .= ' WHERE ' . implode(' AND ', $conditions);
$sql .= ' ORDER BY ' . $sortSql[$sort];
$statement = $pdo->prepare($sql);
$statement->execute($parameters);
$products = $statement->fetchAll();
$categoryIcons = ['Điện thoại' => '▯', 'Máy tính bảng' => '▤', 'Laptop' => '▰', 'Đồng hồ thông minh' => '◷', 'Tai nghe & Loa' => '♫', 'Phụ kiện' => '⌁', 'Gaming' => '▧'];
require __DIR__ . '/includes/header.php';
?>
<section class="container catalog-page">
    <div class="catalog-heading">
        <div>
            <div class="eyebrow">KHÁM PHÁ CÔNG NGHỆ</div>
            <h1>Tìm món đồ công nghệ dành cho bạn</h1>
            <p>Điện thoại, laptop và phụ kiện chính hãng — chọn danh mục hoặc tìm kiếm ngay.</p>
        </div>
        <div class="catalog-heading-note"><span aria-hidden="true">✓</span> Mua sắm an tâm, hỗ trợ tận tình</div>
    </div>

    <section class="category-browser" aria-labelledby="category-heading">
        <div class="category-section-heading"><h2 id="category-heading">Danh mục sản phẩm</h2><span><?= count($categories) ?> danh mục</span></div>
        <nav class="category-list" aria-label="Lọc theo danh mục">
            <a class="category-tile <?= $selectedCategory === '' ? 'selected' : '' ?>" href="products.php<?= $search !== '' ? '?' . e(http_build_query(['q' => $search, 'sort' => $sort])) : '' ?>">
                <span class="category-icon" aria-hidden="true">✦</span><span class="category-name">Tất cả</span><span class="category-total"><?= array_sum($categoryCounts) ?></span>
            </a>
            <?php foreach ($categories as $category):
                $categoryQuery = ['cat' => $category];
                if ($search !== '') $categoryQuery['q'] = $search;
                if ($sort !== 'newest') $categoryQuery['sort'] = $sort;
                ?>
                <a class="category-tile <?= $selectedCategory === $category ? 'selected' : '' ?>" href="products.php?<?= e(http_build_query($categoryQuery)) ?>" <?= $selectedCategory === $category ? 'aria-current="page"' : '' ?>>
                    <span class="category-icon" aria-hidden="true"><?= e($categoryIcons[$category]) ?></span>
                    <span class="category-name"><?= e($category) ?></span>
                    <span class="category-total"><?= $categoryCounts[$category] ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
    </section>

    <section class="catalog-results" aria-labelledby="results-heading">
        <div class="catalog-results-heading">
            <div>
                <h2 id="results-heading"><?= $selectedCategory !== '' ? e($selectedCategory) : 'Tất cả sản phẩm' ?></h2>
                <p><?= count($products) ?> sản phẩm<?= $search !== '' ? ' phù hợp với “' . e($search) . '”' : ' dành cho bạn' ?></p>
            </div>
            <form class="catalog-search-form" method="get">
                <label class="catalog-search-input">
                    <span aria-hidden="true">⌕</span>
                    <input type="search" name="q" value="<?= e($search) ?>" placeholder="Tìm sản phẩm, thương hiệu..." aria-label="Tìm sản phẩm, thương hiệu hoặc danh mục">
                </label>
                <?php if ($selectedCategory !== ''): ?><input type="hidden" name="cat" value="<?= e($selectedCategory) ?>"><?php endif; ?>
                <label class="visually-hidden" for="catalog-sort">Sắp xếp sản phẩm</label>
                <select id="catalog-sort" name="sort" aria-label="Sắp xếp sản phẩm">
                    <?php foreach ($sortOptions as $value => $label): ?><option value="<?= e($value) ?>" <?= $sort === $value ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
                </select>
                <button class="button catalog-search-button" type="submit">Tìm kiếm</button>
            </form>
        </div>

        <?php if ($search !== '' || $selectedCategory !== ''): ?>
            <div class="active-filters">
                <?php if ($selectedCategory !== ''): ?><span><?= e($selectedCategory) ?> <a href="products.php<?= $search !== '' ? '?' . e(http_build_query(['q' => $search, 'sort' => $sort])) : '' ?>" aria-label="Bỏ lọc danh mục">×</a></span><?php endif; ?>
                <?php if ($search !== ''): ?><span>Từ khóa: <?= e($search) ?> <a href="products.php<?= $selectedCategory !== '' ? '?' . e(http_build_query(['cat' => $selectedCategory, 'sort' => $sort])) : '' ?>" aria-label="Bỏ từ khóa tìm kiếm">×</a></span><?php endif; ?>
                <a class="clear-all-filters" href="products.php">Xóa tất cả</a>
            </div>
        <?php endif; ?>

        <?php if ($products): ?>
            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                    <article class="product-card catalog-product-card">
                        <a href="product.php?id=<?= (int) $product['id'] ?>">
                            <div class="catalog-product-image-wrap">
                                <img src="<?= e($product['image_url']) ?>" alt="<?= e($product['name']) ?>" loading="lazy">
                                <?php if ((int) $product['stock'] === 0): ?><span class="catalog-stock-tag">Tạm hết hàng</span><?php endif; ?>
                            </div>
                            <div class="product-info">
                                <div class="catalog-product-meta"><span class="product-brand"><?= e($product['brand']) ?></span><span class="catalog-category-tag"><?= e($product['category']) ?></span></div>
                                <h3><?= e($product['name']) ?></h3>
                                <div class="price"><?= format_price($product['price']) ?></div>
                                <div class="product-bottom"><span class="stock"><?= (int) $product['stock'] > 0 ? 'Còn ' . (int) $product['stock'] . ' sản phẩm' : 'Tạm hết hàng' ?></span><span class="text-link">Xem chi tiết →</span></div>
                            </div>
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state catalog-empty-state"><strong>Chưa tìm thấy sản phẩm phù hợp</strong><p>Thử từ khóa khác hoặc chọn một danh mục khác để tiếp tục khám phá.</p><a class="button secondary" href="products.php">Xóa bộ lọc</a></div>
        <?php endif; ?>
    </section>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
