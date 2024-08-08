<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'denicrmp.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

$sql = "SELECT name, level, money FROM accounts WHERE id = ?";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        header("Location: login.php");
        exit();
    }
} else {
    die("Ошибка в подготовке запроса: " . $conn->error);
}

function showAuthorCredit() {
    return '<!-- Жесткий программист - @deni_crmp -->';
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #e0e0e0, #f9f9f9);
            color: #333;
        }
        .container {
            max-width: 500px;
            width: 100%;
            background-color: #fff;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            text-align: center;
        }
        h3 {
            color: #2c3e50;
            margin-bottom: 25px;
            font-weight: bold;
            font-size: 24px;
            font-family: 'GTA', sans-serif;
            letter-spacing: 1px;
        }
        .user-info {
            margin-bottom: 20px;
        }
        .btn-primary {
            background-color: #2c3e50;
            border: none;
            border-radius: 5px;
            padding: 10px 25px;
            font-weight: bold;
            font-size: 16px;
            color: #fff;
            transition: background-color 0.3s;
        }
        .btn-primary:hover {
            background-color: #34495e;
        }
    </style>
    <title>Профиль игрока</title>
    <?php echo base64_decode('PG1ldGEgbmFtZT0iYXV0aG9yIiBjb250ZW50PSJTb3plbGFubyBARGVuaV9jcm1wIj4='); ?>
    <?php echo showAuthorCredit(); ?>
</head>
<body>
    <div class="container">
        <h3>Добро пожаловать, <?php echo htmlspecialchars($user['name']); ?>!</h3>
        <div class="user-info">
            <p>Ваш игровой уровень: <strong><?php echo $user['level']; ?></strong></p>
            <p>Ваше кол-во денег: <strong><?php echo $user['money']; ?></strong></p>
        </div>
        <a href="logout.php" class="btn-primary">Выйти</a>
    </div>

    <script>
        const author = '%cАвтор: @deni_crmp';
        const authorStyle = 'color: #2c3e50; font-weight: bold; font-size: 14px;';
        console.log(author, authorStyle);
    </script>
</body>
</html>
