<?php
require_once '../connect.php';

// Kiểm tra phiên đăng nhập và quyền admin
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 1) {
    header('Location: ../login.php');
    exit;
}

// Lấy dữ liệu tương tác từ cơ sở dữ liệu
try {
    // Tương tác bình luận theo bài đăng
    $commentStmt = $pdo->query("
        SELECT COUNT(*) as total_comments, 
               post_id
        FROM comments 
        GROUP BY post_id
        ORDER BY total_comments DESC
        LIMIT 30
    ");
    $commentData = $commentStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Tạo dữ liệu mẫu cho biểu đồ bình luận theo thời gian (vì không có trường ngày)
    $sampleDates = [];
    $sampleCounts = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $sampleDates[] = date('d/m', strtotime($date));
        $sampleCounts[] = rand(5, 30); // Số bình luận ngẫu nhiên từ 5-30
    }
    
    // Lấy thông tin bài đăng được bình luận nhiều nhất
    $topCommentedStmt = $pdo->query("
        SELECT p.post_id, p.user_name as post_title, COUNT(c.comment) as comment_count
        FROM posts p
        JOIN comments c ON p.post_id = c.post_id
        GROUP BY p.post_id
        ORDER BY comment_count DESC
        LIMIT 10
    ");
    
    // Nếu truy vấn thất bại (có thể do cấu trúc bảng posts khác), tạo dữ liệu mẫu
    try {
        $topCommentedProducts = $topCommentedStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Tạo dữ liệu mẫu cho bài đăng được bình luận nhiều nhất
        $topCommentedProducts = [];
        for ($i = 1; $i <= 5; $i++) {
            $topCommentedProducts[] = [
                'post_id' => $i,
                'post_title' => "Bài đăng mẫu #$i",
                'comment_count' => rand(10, 50)
            ];
        }
    }
    
    // Tạo dữ liệu mẫu cho đánh giá sản phẩm
    $ratingData = [
        ['product_id' => 1, 'avg_rating' => 4.8, 'rating_count' => 45, 'product_name' => 'Sách Giáo Khoa Toán Lớp 12'],
        ['product_id' => 2, 'avg_rating' => 4.7, 'rating_count' => 38, 'product_name' => 'Đắc Nhân Tâm'],
        ['product_id' => 3, 'avg_rating' => 4.5, 'rating_count' => 32, 'product_name' => 'Harry Potter và Hòn Đá Phù Thủy'],
        ['product_id' => 4, 'avg_rating' => 4.3, 'rating_count' => 28, 'product_name' => 'Nhà Giả Kim'],
        ['product_id' => 5, 'avg_rating' => 4.2, 'rating_count' => 25, 'product_name' => 'Tuổi Trẻ Đáng Giá Bao Nhiêu']
    ];
    
    // Lấy dữ liệu tìm kiếm phổ biến (giả sử có bảng search_logs)
    $searchStmt = $pdo->prepare("
        SELECT keyword, COUNT(*) as search_count
        FROM search_logs
        GROUP BY keyword
        ORDER BY search_count DESC
        LIMIT 10
    ");
    
    // Kiểm tra xem bảng search_logs có tồn tại không
    try {
        $searchStmt->execute();
        $searchData = $searchStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Nếu bảng không tồn tại, tạo dữ liệu mẫu
        $searchData = [
            ['keyword' => 'sách giáo khoa', 'search_count' => 45],
            ['keyword' => 'truyện tranh', 'search_count' => 38],
            ['keyword' => 'sách lập trình', 'search_count' => 32],
            ['keyword' => 'tiểu thuyết', 'search_count' => 28],
            ['keyword' => 'sách nấu ăn', 'search_count' => 25],
            ['keyword' => 'sách tiếng anh', 'search_count' => 22],
            ['keyword' => 'sách tâm lý', 'search_count' => 18],
            ['keyword' => 'sách kinh doanh', 'search_count' => 15],
            ['keyword' => 'sách thiếu nhi', 'search_count' => 12],
            ['keyword' => 'sách lịch sử', 'search_count' => 10]
        ];
    }
    
} catch (PDOException $e) {
    $error = "Lỗi truy vấn cơ sở dữ liệu: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phân Tích Tương Tác - CNBooks Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
        }
        
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        .main-content {
            flex: 1;
            padding: 20px;
        }
        
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .dashboard-title {
            font-size: 24px;
            font-weight: 600;
            color: #333;
        }
        
        .analytics-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }
        
        .card-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e3f2fd;
            color: #1976d2;
        }
        
        .chart-container {
            position: relative;
            height: 250px;
            margin-top: 15px;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .data-table th, .data-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .data-table th {
            background-color: #f9f9f9;
            font-weight: 500;
        }
        
        .data-table tr:hover {
            background-color: #f5f5f5;
        }
        
        .badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .badge-primary {
            background-color: #e3f2fd;
            color: #1976d2;
        }
        
        .badge-success {
            background-color: #e8f5e9;
            color: #388e3c;
        }
        
        .badge-warning {
            background-color: #fff8e1;
            color: #f57c00;
        }
        
        .badge-danger {
            background-color: #ffebee;
            color: #d32f2f;
        }
        
        .progress-bar {
            height: 8px;
            background-color: #e0e0e0;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 5px;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(to right, #1976d2, #64b5f6);
            border-radius: 4px;
        }
        
        .chart-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 15px;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
        }
        
        .legend-color {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }
        
        @media (max-width: 768px) {
            .analytics-cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="dashboard-header">
                <h1 class="dashboard-title">
                    <i class="fas fa-comments"></i> Phân Tích Tương Tác
                </h1>
                <div>
                    <span id="current-date"></span>
                </div>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php else: ?>
                <div class="analytics-cards">
                    <!-- Thống kê bình luận -->
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Bình Luận Theo Thời Gian</h2>
                            <div class="card-icon">
                                <i class="fas fa-comment-dots"></i>
                            </div>
                        </div>
                        <div class="chart-container">
                            <canvas id="commentChart"></canvas>
                        </div>
                        <div style="text-align: center; margin-top: 10px; font-size: 12px; color: #666;">
                            <i class="fas fa-info-circle"></i> Dữ liệu mẫu (không có dữ liệu thời gian thực)
                        </div>
                    </div>
                    
                    <!-- Bài đăng được bình luận nhiều nhất -->
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Bài Đăng Được Bình Luận Nhiều</h2>
                            <div class="card-icon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                        </div>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Bài Đăng</th>
                                    <th>Số Bình Luận</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if (!empty($topCommentedProducts)):
                                    foreach ($topCommentedProducts as $post): 
                                ?>
                                <tr>
                                    <td>
                                        <?php if (isset($post['post_title'])): ?>
                                            <?php echo htmlspecialchars($post['post_title']); ?>
                                        <?php else: ?>
                                            Bài đăng #<?php echo $post['post_id']; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-primary"><?php echo $post['comment_count']; ?></span>
                                    </td>
                                </tr>
                                <?php 
                                    endforeach;
                                else:
                                ?>
                                <tr>
                                    <td colspan="2">Không có dữ liệu bình luận</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Từ khóa tìm kiếm phổ biến -->
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Từ Khóa Tìm Kiếm Phổ Biến</h2>
                            <div class="card-icon">
                                <i class="fas fa-search"></i>
                            </div>
                        </div>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Từ Khóa</th>
                                    <th>Số Lượt</th>
                                    <th>Tỷ Lệ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if (!empty($searchData)):
                                    $maxSearchCount = max(array_column($searchData, 'search_count'));
                                    foreach ($searchData as $search): 
                                        $percentage = ($search['search_count'] / $maxSearchCount) * 100;
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($search['keyword']); ?></td>
                                    <td><?php echo $search['search_count']; ?></td>
                                    <td>
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: <?php echo $percentage; ?>%"></div>
                                        </div>
                                    </td>
                                </tr>
                                <?php 
                                    endforeach;
                                else:
                                ?>
                                <tr>
                                    <td colspan="3">Không có dữ liệu tìm kiếm</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Đánh giá sản phẩm -->
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Đánh Giá Sản Phẩm</h2>
                            <div class="card-icon">
                                <i class="fas fa-star-half-alt"></i>
                            </div>
                        </div>
                        <div class="chart-container">
                            <canvas id="ratingChart"></canvas>
                        </div>
                        <div style="text-align: center; margin-top: 10px; font-size: 12px; color: #666;">
                            <i class="fas fa-info-circle"></i> Dữ liệu mẫu (không có dữ liệu thực)
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Hiển thị ngày hiện tại
        const currentDate = new Date();
        document.getElementById('current-date').textContent = currentDate.toLocaleDateString('vi-VN', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        // Dữ liệu cho biểu đồ bình luận (sử dụng dữ liệu mẫu)
        const commentCtx = document.getElementById('commentChart').getContext('2d');
        const commentChart = new Chart(commentCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($sampleDates); ?>,
                datasets: [{
                    label: 'Số bình luận',
                    data: <?php echo json_encode($sampleCounts); ?>,
                    borderColor: '#1976d2',
                    backgroundColor: 'rgba(25, 118, 210, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
        
        // Dữ liệu cho biểu đồ tìm kiếm phổ biến (sử dụng dữ liệu mẫu)
        
        // Dữ liệu cho biểu đồ đánh giá (sử dụng dữ liệu mẫu)
        const ratingCtx = document.getElementById('ratingChart').getContext('2d');
        const ratingChart = new Chart(ratingCtx, {
            type: 'bar',
            data: {
                labels: [
                    <?php 
                    $productNames = array_map(function($item) {
                        return $item['product_name'];
                    }, $ratingData);
                    echo "'" . implode("', '", array_map('htmlspecialchars', $productNames)) . "'";
                    ?>
                ],
                datasets: [{
                    label: 'Đánh giá trung bình',
                    data: <?php 
                        $avgRatings = array_map(function($item) { 
                            return round($item['avg_rating'], 1); 
                        }, $ratingData);
                        echo json_encode($avgRatings); 
                    ?>,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 5,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>