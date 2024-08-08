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

$sql = "SELECT * FROM accounts WHERE id = ?";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $currentAdminLevel = $user['admin'];
        if ($currentAdminLevel != 13) {
            header("Location: index.php");
            exit();
        }
    } else {
        header("Location: login.php");
        exit();
    }
} else {
    die("Ошибка в подготовке запроса: " . $conn->error);
}

setcookie(session_name(), session_id(), [
    'expires' => time() + 60 * 60 * 24 * 30,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Strict'
]);

function showAuthorCredit() {
    return '<!-- Жесткий программист - @deni_crmp -->';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['username'])) {
    $username = $_POST['username'];

    $sql = "SELECT * FROM accounts WHERE name = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $targetUser = $result->fetch_assoc();
            $targetAdminLevel = $targetUser['admin'];

            if ($currentAdminLevel >= $targetAdminLevel) {
                $sql = "UPDATE accounts SET admin = 0 WHERE name = ?";
                $stmt = $conn->prepare($sql);

                if ($stmt) {
                    $stmt->bind_param("s", $username);
                    if ($stmt->execute()) {
                        $success = "Уровень админа для пользователя $username успешно изменен на 0.";
                    } else {
                        $error = "Ошибка при обновлении уровня админа.";
                    }
                } else {
                    $error = "Ошибка в подготовке запроса: " . $conn->error;
                }
            } else {
                $error = "Вы не можете снять уровень администратора, так как уровень выше вашего.";
            }
        } else {
            $error = "Пользователь с именем $username не найден.";
        }
    } else {
        $error = "Ошибка в подготовке запроса: " . $conn->error;
    }
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
            height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #e0e0e0, #f9f9f9);
            color: #333;
        }
        .container {
            max-width: 400px;
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
        .form-control {
            background-color: #f7f7f7;
            color: #333;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
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
            width: 100%;
            margin-bottom: 10px;
        }
        .btn-primary:hover {
            background-color: #34495e;
        }
        footer p {
            color: #777;
            margin-top: 30px;
            font-size: 14px;
        }
    </style>
    <title>Админ Панель</title>
    <?php echo base64_decode('PG1ldGEgbmFtZT0iYXV0aG9yIiBjb250ZW50PSJTb3plbGFubyBARGVuaV9jcm1wIj4='); ?>
    <?php echo showAuthorCredit(); ?>
</head>
<body>
    <div class="container">
        <h3>Снять админа</h3>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php elseif (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="post">
            <input type="text" name="username" placeholder="Введите никнейм" class="form-control" required>
            <button type="submit" class="btn-primary">Снять уровень админа</button>
        </form>
        <a href="admin_deni.php" class="btn-primary">Назад</a>
        <footer>
            <p>&copy; 2024 Создано <a href="https://t.me/deni_crmp" target="_blank">@deni_crmp</a></p>
        </footer>
    </div>
    <script>
        const author = '%cАвтор: @deni_crmp';
        const authorStyle = 'color: #2c3e50; font-weight: bold; font-size: 14px;';
        console.log(author, authorStyle);
    </script>
</body>
</html>
