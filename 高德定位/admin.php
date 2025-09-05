<?php
session_start();

// 会话验证
if (!(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true)) {
    header('Location: login.php');
    exit;
}

// 处理登出
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: login.php');
    exit;
}

// 处理跳转网址的设置
$success_message = '';
$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['redirect_url'])) {
    $redirect_url = trim($_POST['redirect_url']);
    // 简单的URL验证
    if (filter_var($redirect_url, FILTER_VALIDATE_URL)) {
        file_put_contents('redirect_url.txt', $redirect_url);
        $success_message = "跳转网址已成功更新！";
    } else {
        $error_message = "请输入有效的URL（必须包含http://或https://）";
    }
}
 
// 读取当前设置的跳转网址
$current_redirect_url = file_exists('redirect_url.txt') ? file_get_contents('redirect_url.txt') : '';

// 高德地图API Key
$amap_key = '您的高德key';

// 坐标转换函数
function convertCoordinates($longitude, $latitude, $amap_key) {
    $url = "https://restapi.amap.com/v3/assistant/coordinate/convert?locations={$longitude},{$latitude}&coordsys=gps&key={$amap_key}";
    $response = file_get_contents($url);
    if ($response) {
        $data = json_decode($response, true);
        if ($data['status'] == 1 && isset($data['locations'])) {
            $converted = explode(',', $data['locations']);
            return [
                'longitude' => $converted[0],
                'latitude' => $converted[1]
            ];
        }
    }
    return [
        'longitude' => $longitude,
        'latitude' => $latitude
    ];
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户位置信息管理面板</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3f37c9;
            --success: #4cc9f0;
            --danger: #f72585;
            --warning: #f8961e;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --border-color: #e9ecef;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: var(--dark);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 1rem;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .header h1 {
            color: var(--primary);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.5rem;
        }
        
        .btn {
            padding: 0.65rem 1.25rem;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
            gap: 0.5rem;
            white-space: nowrap;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        .btn-danger {
            background-color: var(--danger);
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #b5179e;
            transform: translateY(-2px);
        }
        
        .btn-success {
            background-color: var(--success);
            color: var(--dark);
        }
        
        .btn-success:hover {
            background-color: #4895ef;
            transform: translateY(-2px);
        }
        
        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
        }
        
        .btn-outline:hover {
            background-color: rgba(67, 97, 238, 0.1);
        }
        
        .card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            padding: 1.25rem;
            margin-bottom: 1.5rem;
            border: 1px solid var(--border-color);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
        }
        
        .card-title {
            font-size: 1.25rem;
            margin-top: 0;
            margin-bottom: 1.25rem;
            color: var(--dark);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .form-group {
            margin-bottom: 1.25rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: #f9f9f9;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
            background-color: white;
        }
        
        .table-responsive {
            overflow-x: auto;
            margin-bottom: 1rem;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            border-radius: 8px;
            overflow: hidden;
            min-width: 600px;
        }
        
        th, td {
            padding: 0.75rem 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        
        th {
            background-color: var(--primary);
            color: white;
            font-weight: 500;
            position: sticky;
            top: 0;
            white-space: nowrap;
        }
        
        tr:hover {
            background-color: rgba(67, 97, 238, 0.05);
        }
        
        .no-records {
            text-align: center;
            color: var(--gray);
            padding: 1.5rem;
            font-style: italic;
            background-color: var(--light);
        }
        
        .time-cell {
            font-family: 'Courier New', monospace;
            font-size: 0.95rem;
            color: var(--gray);
            white-space: nowrap;
        }
        
        .ip-cell {
            font-family: 'Courier New', monospace;
            white-space: nowrap;
        }
        
        .badge {
            display: inline-block;
            padding: 0.35rem 0.65rem;
            font-size: 0.75rem;
            font-weight: 500;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25rem;
        }
        
        .badge-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .badge-success {
            background-color: var(--success);
            color: var(--dark);
        }
        
        .coordinates {
            font-family: 'Courier New', monospace;
            background-color: var(--light);
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            display: inline-block;
            font-size: 0.9rem;
        }
        
        .map-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }
        
        .map-link:hover {
            text-decoration: underline;
            color: var(--primary-dark);
        }
        
        .refresh-btn {
            margin-bottom: 1.5rem;
            width: 100%;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .stat-card {
            background-color: white;
            border-radius: 10px;
            padding: 1rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border-color);
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--primary);
            margin: 0.5rem 0;
            line-height: 1.2;
        }
        
        .stat-label {
            color: var(--gray);
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .alert {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-top: 1rem;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .alert-success {
            background-color: rgba(76, 201, 240, 0.2);
            color: #2d9cdb;
            border-left: 3px solid var(--success);
        }
        
        .alert-danger {
            background-color: rgba(247, 37, 133, 0.1);
            color: var(--danger);
            border-left: 3px solid var(--danger);
        }
        
        .action-buttons {
            display: flex;
            gap: 0.75rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }
        
        /* 地图容器样式 - 响应式改进 */
        .map-container {
            width: 100%;
            height: 60vh;
            min-height: 300px;
            max-height: 600px;
            margin: 1rem 0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--border-color);
        }
        
        /* 地图模态框改进 */
        .map-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: flex-end;
            padding: 0;
            overflow: hidden;
        }
        
        .map-modal-content {
            background-color: white;
            padding: 1rem;
            border-radius: 10px 10px 0 0;
            width: 100%;
            max-width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            animation: slideUp 0.3s ease;
        }
        
        @keyframes slideUp {
            from { transform: translateY(100%); }
            to { transform: translateY(0); }
        }
        
        .map-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            background-color: white;
            z-index: 10;
        }
        
        .map-modal-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark);
        }
        
        .close-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--gray);
            padding: 0.5rem;
        }
        
        .close-btn:hover {
            color: var(--dark);
        }
        
        .location-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 0.75rem;
            margin-bottom: 1rem;
        }
        
        .detail-item {
            background-color: var(--light);
            padding: 0.75rem;
            border-radius: 8px;
            border-left: 3px solid var(--primary);
        }
        
        .detail-label {
            font-size: 0.75rem;
            color: var(--gray);
            margin-bottom: 0.25rem;
        }
        
        .detail-value {
            font-weight: 500;
            word-break: break-all;
            font-size: 0.9rem;
        }
        
        .text-muted {
            color: var(--gray);
        }
        
        /* 桌面端样式 */
        @media (min-width: 768px) {
            .container {
                padding: 2rem;
            }
            
            .header h1 {
                font-size: 1.75rem;
            }
            
            .map-modal {
                align-items: center;
            }
            
            .map-modal-content {
                width: 90%;
                max-width: 1000px;
                border-radius: 10px;
                max-height: 90vh;
                animation: fadeIn 0.3s ease;
            }
            
            .map-container {
                height: 400px;
            }
            
            .location-details {
                grid-template-columns: repeat(3, 1fr);
            }
            
            .stats-container {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
            
            .stat-value {
                font-size: 2.25rem;
            }
            
            .stat-label {
                font-size: 0.95rem;
            }
        }
        
        /* 大屏幕样式 */
        @media (min-width: 1200px) {
            .location-details {
                grid-template-columns: repeat(5, 1fr);
            }
        }
    </style>
    <!-- 引入高德地图JS API -->
    <script src="https://webapi.amap.com/maps?v=2.0&key=<?php echo $amap_key; ?>"></script>
    <script src="https://webapi.amap.com/ui/1.1/main.js"></script>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-map-marker-alt"></i> 用户位置信息</h1>
            <div class="action-buttons">
                <a href="?action=logout" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt"></i> 退出登录
                </a>
                <button class="btn btn-outline" onclick="location.reload()">
                    <i class="fas fa-sync-alt"></i> 刷新
                </button>
            </div>
        </div>
        
        <!-- 统计信息 -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-label"><i class="fas fa-database"></i> 总记录数</div>
                <div class="stat-value">
                    <?php
                    $totalRecords = file_exists('user_locations.txt') ? count(file('user_locations.txt')) : 0;
                    echo $totalRecords;
                    ?>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-label"><i class="fas fa-calendar-day"></i> 今日记录</div>
                <div class="stat-value">
                    <?php
                    $todayRecords = 0;
                    if (file_exists('user_locations.txt')) {
                        $lines = file('user_locations.txt');
                        $today = strtotime('today');
                        foreach ($lines as $line) {
                            $info = json_decode(trim($line), true);
                            if ($info && isset($info['time']) && $info['time'] >= $today) {
                                $todayRecords++;
                            }
                        }
                    }
                    echo $todayRecords;
                    ?>
                </div>
            </div>
        </div>

        <!-- 添加跳转网址设置表单 -->
        <div class="card">
            <h2 class="card-title"><i class="fas fa-link"></i> 设置跳转网址</h2>
            <form method="post">
                <div class="form-group">
                    <label for="redirect_url"><i class="fas fa-globe"></i> 跳转网址</label>
                    <input type="url" id="redirect_url" name="redirect_url" 
                           value="<?= htmlspecialchars($current_redirect_url) ?>" 
                           placeholder="https://example.com" 
                           class="form-control" required>
                </div>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> 保存设置
                </button>
            </form>
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_message) ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <h2 class="card-title"><i class="fas fa-list"></i> 用户位置记录</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th><i class="fas fa-globe"></i> IP 地址</th>
                            <th><i class="fas fa-map-marker-alt"></i> 地理位置</th>
                            <th><i class="fas fa-location-arrow"></i> 经纬度</th>
                            <th><i class="fas fa-clock"></i> 记录时间</th>
                            <th><i class="fas fa-cog"></i> 操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (file_exists('user_locations.txt')) {
                            $lines = file('user_locations.txt');
                            if (empty($lines)) {
                                echo '<tr><td colspan="5" class="no-records"><i class="fas fa-inbox"></i> 暂无记录</td></tr>';
                            } else {
                                // 反转数组，显示最新的记录在最上面
                                $lines = array_reverse($lines);
                                foreach ($lines as $line) {
                                    $info = json_decode(trim($line), true);
                                    if ($info) {
                                        // 格式化时间显示
                                        $time = isset($info['time']) ? date('Y-m-d H:i:s', $info['time']) : '未知时间';
                                        $latitude = isset($info['latitude']) ? $info['latitude'] : '未知';
                                        $longitude = isset($info['longitude']) ? $info['longitude'] : '未知';
                                        $accuracy = isset($info['accuracy']) ? $info['accuracy'] : '未知';
                                        
                                        echo '<tr>
                                                <td class="ip-cell">' . htmlspecialchars($info['ip']) . '</td>
                                                <td>' . htmlspecialchars($info['location']) . '</td>
                                                <td>';
                                        
                                        // 显示经纬度信息
                                        if ($latitude != '未知' && $longitude != '未知') {
                                            echo '<span class="coordinates" title="经度，纬度">'.htmlspecialchars($longitude).', '.htmlspecialchars($latitude).'</span>';
                                            if ($accuracy != '未知') {
                                                echo ' <span class="badge badge-primary" title="定位精度">±'.htmlspecialchars($accuracy).'m</span>';
                                            }
                                        } else {
                                            echo '<span class="text-muted">无经纬度数据</span>';
                                        }
                                        
                                        echo '</td>
                                                <td class="time-cell">' . $time . '</td>
                                                <td>';
                                        
                                        if ($latitude != '未知' && $longitude != '未知') {
                                            echo '<button class="btn btn-primary btn-sm" onclick="showLocationMap(\''.htmlspecialchars($longitude, ENT_QUOTES).'\', \''.htmlspecialchars($latitude, ENT_QUOTES).'\', \''.htmlspecialchars($accuracy, ENT_QUOTES).'\', \''.htmlspecialchars($info['location'], ENT_QUOTES).'\', \''.htmlspecialchars($info['ip'], ENT_QUOTES).'\', \''.htmlspecialchars($time, ENT_QUOTES).'\')">
                                                    <i class="fas fa-map-marked-alt"></i> 查看地图
                                                  </button>';
                                        } else {
                                            echo '<span class="text-muted">无位置数据</span>';
                                        }
                                        
                                        echo '</td>
                                              </tr>';
                                    }
                                }
                            }
                        } else {
                            echo '<tr><td colspan="5" class="no-records"><i class="fas fa-inbox"></i> 暂无记录</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- 地图模态框 -->
    <div class="map-modal" id="mapModal">
        <div class="map-modal-content">
            <div class="map-modal-header">
                <div class="map-modal-title">位置详情</div>
                <button class="close-btn" onclick="hideMapModal()">&times;</button>
            </div>
            
            <div class="location-details" id="locationDetails">
                <!-- 位置详情将通过JavaScript动态填充 -->
            </div>
            
            <div class="map-container" id="mapContainer"></div>
        </div>
    </div>
    
    <script>
        // 显示地图模态框
        function showLocationMap(longitude, latitude, accuracy, location, ip, time) {
            // 填充位置详情
            const detailsContainer = document.getElementById('locationDetails');
            detailsContainer.innerHTML = `
                <div class="detail-item">
                    <div class="detail-label">IP地址</div>
                    <div class="detail-value">${ip}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">地理位置</div>
                    <div class="detail-value">${location}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">经纬度</div>
                    <div class="detail-value">${longitude}, ${latitude}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">定位精度</div>
                    <div class="detail-value">±${accuracy}米</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">记录时间</div>
                    <div class="detail-value">${time}</div>
                </div>
            `;
            
            // 初始化地图
            const map = new AMap.Map('mapContainer', {
                zoom: 16, // 初始缩放级别
                center: [parseFloat(longitude), parseFloat(latitude)], // 初始中心点
                viewMode: '2D' // 使用2D视图
            });
            
            // 添加标记点
            const marker = new AMap.Marker({
                position: new AMap.LngLat(parseFloat(longitude), parseFloat(latitude)),
                title: location
            });
            map.add(marker);
            
            // 如果精度信息可用，绘制误差范围圆
            if (accuracy !== '未知' && !isNaN(accuracy)) {
                const accuracyRadius = parseInt(accuracy);
                const circle = new AMap.Circle({
                    center: new AMap.LngLat(parseFloat(longitude), parseFloat(latitude)),
                    radius: accuracyRadius, // 误差范围半径（米）
                    strokeColor: '#4361ee', // 边框颜色
                    strokeOpacity: 0.8, // 边框透明度
                    strokeWeight: 2, // 边框宽度
                    fillColor: '#4361ee', // 填充颜色
                    fillOpacity: 0.2 // 填充透明度
                });
                map.add(circle);
                
                // 调整缩放级别以显示整个误差范围
                map.setFitView([circle]);
            } else {
                // 没有精度信息，只调整到标记点
                map.setFitView([marker]);
            }
            
            // 显示模态框
            document.getElementById('mapModal').style.display = 'flex';
            
            // 禁止页面滚动（移动端）
            document.body.style.overflow = 'hidden';
        }
        
        // 隐藏地图模态框
        function hideMapModal() {
            document.getElementById('mapModal').style.display = 'none';
            // 恢复页面滚动
            document.body.style.overflow = '';
            // 销毁地图实例，避免内存泄漏
            const mapContainer = document.getElementById('mapContainer');
            mapContainer.innerHTML = '';
        }
        
        // 点击模态框外部关闭（仅限桌面端）
        window.onclick = function(event) {
            const modal = document.getElementById('mapModal');
            if (window.innerWidth >= 768 && event.target === modal) {
                hideMapModal();
            }
        }
        
        // 响应式调整：手机端表格滚动
        function setupTableScroll() {
            const tables = document.querySelectorAll('.table-responsive');
            tables.forEach(table => {
                const parent = table.parentElement;
                if (parent.scrollWidth > parent.clientWidth) {
                    table.style.width = '100%';
                    table.style.overflowX = 'auto';
                }
            });
        }
        
        // 初始加载和窗口大小变化时调整
        window.addEventListener('load', setupTableScroll);
        window.addEventListener('resize', setupTableScroll);
    </script>
</body>
</html>