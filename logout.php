<?php
require_once __DIR__ . '/includes/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Phương thức không được hỗ trợ.');
}
verify_csrf();
unset($_SESSION['customer_id'], $_SESSION['customer_name'], $_SESSION['customer_email']);
session_regenerate_id(true);
redirect('index.php');
