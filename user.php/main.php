<?php
require 'connect.php';

try {
    // Lấy dữ liệu từ bảng news
    $stmt = $pdo->query("SELECT * FROM news ORDER BY read_count DESC, RAND()");
    $newsList = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Thêm trường date cho mỗi tin tức (sử dụng ngày hiện tại)
    $current_date = date('d/m/Y');
    foreach ($newsList as &$news) {
        $news['date'] = $current_date;
    }
} catch (PDOException $e) {
    die("Lỗi kết nối CSDL: " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CINNGNBOOKS</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .news-link {
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .top-section {
            width: 100%;
            padding: 10px 15px;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            z-index: 1001;
        }

        .search-container {
            width: 250px;
        }

        .search-container form {
            display: flex;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            border-radius: 20px;
            overflow: hidden;
            background-color: white;
        }

        .search-container input[type="text"] {
            flex: 1;
            padding: 8px 12px;
            border: none;
            outline: none;
            font-size: 14px;
        }

        .search-container button {
            background: linear-gradient(to right, #6ec1e4, #4a90e2);
            color: white;
            border: none;
            padding: 8px 12px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s;
            margin-bottom: 7px;
            margin-right: 5px;
        }

        .search-container button:hover {
            background: linear-gradient(to right, #4a90e2, #3a7bc8);
        }

        .news-container {
            margin-top: 30px;
        }

        .latest-news {
            margin-bottom: 15px;
        }

        .main-news {
            display: grid;
            grid-template-columns: 65% 35%;
            gap: 10px;
            height: auto;
        }


        .large-news {
            flex: 2 1 65%;
            min-height: 400px;
            background-size: cover;
            background-position: center;
            position: relative;
            border-radius: 10px;
            overflow: hidden;
        }

        .small-news-container {
            flex: 1 1 35%;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            grid-template-rows: repeat(2, 1fr);
            gap: 10px;
            height: 100%;
            /* Đảm bảo chiếm full chiều cao cột phải */
        }


        .small-news {
            background-size: cover;
            background-position: center;
            position: relative;
            border-radius: 10px;
            overflow: hidden;
            width: 100%;
            aspect-ratio: 1 / 1;
            /* Đảm bảo luôn là hình vuông */
        }


        .news-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            padding: 10px;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.7), transparent);
            color: white;
        }

        .category {
            font-size: 12px;
            font-weight: bold;
        }

        .text-new {
            font-size: 13px;
            color: white;
        }

        .date {
            font-size: 10px;
        }

        .carousel-buttons {
            position: absolute;
            top: 50%;
            width: 100%;
            display: flex;
            justify-content: space-between;
            transform: translateY(-50%);
            padding: 0 10px;
        }

        .carousel-buttons button {
            background: rgba(0, 0, 0, 0.5);
            color: white;
            border: none;
            font-size: 24px;
            cursor: pointer;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s;
        }

        .carousel-buttons button:hover {
            background: rgba(0, 0, 0, 0.8);
        }
    </style>
</head>

<body>

    <div class="news-container">
        <div class="top-section">
            <div class="search-container">
                <form action="search.php" method="GET">
                    <input type="text" name="keyword" placeholder="Tìm kiếm sách..." required>
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>
        </div>
        <?php if (!empty($newsList)): ?>
            <div class="latest-news">
                <span class="highlight" style="color: red; font-weight: bold;">&#9889; Tin mới nhất :</span>
                <span class="news-title" style="color: orange ; font-weight: bold;">
                    <?php echo htmlspecialchars($newsList[0]['product_name']); ?>
                </span>
            </div>

            <div class="main-news">
                <a href="readdetail.php?product_name=<?php echo urlencode($newsList[0]['product_name']); ?>" class="news-link">
                    <div class="large-news" id="large-news" style="background-image: url('<?php echo htmlspecialchars($newsList[0]['image_url']); ?>');">
                        <div class="news-overlay">
                            <span class="category"><?php echo htmlspecialchars($newsList[0]['type_name']); ?></span>
                            <h2><?php echo htmlspecialchars($newsList[0]['product_name']); ?></h2>
                            <span class="date"><?php echo htmlspecialchars($newsList[0]['date']); ?></span>
                        </div>
                        <div class="carousel-buttons">
                            <button onclick="prevSlide(event)">&#10094;</button>
                            <button onclick="nextSlide(event)">&#10095;</button>
                        </div>
                    </div>
                </a>
                <div class="small-news-container">
                    <?php for ($i = 1; $i < count($newsList); $i++): ?>
                        <a href="readdetail.php?product_name=<?php echo urlencode($newsList[$i]['product_name']); ?>" class="news-link">
                            <div class="small-news" style="background-image: url('<?php echo htmlspecialchars($newsList[$i]['image_url']); ?>');">
                                <div class="news-overlay">
                                    <span class="category"><?php echo htmlspecialchars($newsList[$i]['type_name']); ?></span>
                                    <h3 class="text-new"><?php echo htmlspecialchars($newsList[$i]['product_name']); ?></h3>
                                    <span class="date"><?php echo htmlspecialchars($newsList[$i]['date']); ?></span>
                                </div>
                            </div>
                        </a>
                    <?php endfor; ?>
                </div>
            </div>
        <?php else: ?>
            <p>Không có tin tức nào để hiển thị.</p>
        <?php endif; ?>
    </div>

    <script>
        let currentIndex = 0;
        const newsList = <?php echo json_encode($newsList); ?>;
        const largeNews = document.getElementById('large-news');
        const newsLink = document.querySelector('.news-link');

        function showSlide(index) {
            largeNews.style.backgroundImage = `url('${newsList[index].image_url}')`;
            largeNews.querySelector('.category').textContent = newsList[index].type_name;
            largeNews.querySelector('h2').textContent = newsList[index].product_name;
            largeNews.querySelector('.date').textContent = newsList[index].date;

            // Cập nhật URL của liên kết
            if (newsLink) {
                newsLink.href = `readdetail.php?product_name=${encodeURIComponent(newsList[index].product_name)}`;
            }

            currentIndex = index;
        }

        function prevSlide(event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }
            currentIndex = (currentIndex - 1 + newsList.length) % newsList.length;
            showSlide(currentIndex);
        }

        function nextSlide(event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }
            currentIndex = (currentIndex + 1) % newsList.length;
            showSlide(currentIndex);
        }
    </script>
</body>

</html>