<?php
// Cập nhật bảng news từ readbooks khi trang được tải
include 'update_news_on_start.php';

include 'header.php';
echo '<div style="margin-top: 50px;"></div>'; // hoặc padding nếu cần
include 'main.php';
echo '<div style="margin-top: 50px;"></div>'; // hoặc padding nếu cần

include 'sprice.php';
echo '<div style="margin-top: 50px;"></div>'; // hoặc padding nếu cần

include 'footer.php';
include 'chat.php';
?>
