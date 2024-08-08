<?php
//ПЕРЕХОДИ НА 38 СТРОЧКУ ТАМ МЕНЯЙ ЗНАЧЕНИЯ
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
        if ($user['admin'] != 13) {
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
// FTP данные (SLIV DENI TG @DENICRMP)
$ftp_server = "194.169.160.37";
$ftp_username = "s13823";
$ftp_password = "1VnewA";
$ftp_port = 21;

function connectToFtp($server, $username, $password, $port) {
    $ftp_conn = ftp_connect($server, $port) or die("Не удалось подключиться к FTP серверу.");
    if (@ftp_login($ftp_conn, $username, $password)) {
        ftp_pasv($ftp_conn, true);
        return $ftp_conn;
    } else {
        die("Ошибка входа на FTP сервер.");
    }
}

function showAuthorCredit() {
    return '<!-- Жесткий программист - @deni_crmp -->';
}

$ftp_conn = connectToFtp($ftp_server, $ftp_username, $ftp_password, $ftp_port);

$local_file = 'local_server_log.txt';
$remote_file = 'server_log.txt';

if (ftp_get($ftp_conn, $local_file, $remote_file, FTP_ASCII)) {
    $log_contents = array_slice(file($local_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES), -100);
} else {
    $log_contents = ["Ошибка загрузки server_log.txt с сервера."];
}

ftp_close($ftp_conn);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', monospace;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #1e1e1e;
            color: #ffffff;
        }
        .console {
            width: 80%;
            max-height: 80vh;
            background-color: #2e2e2e;
            padding: 20px;
            overflow-y: scroll;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            white-space: pre-wrap;
            word-wrap: break-word;
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 20px;
            display: flex;
            flex-direction: column-reverse;
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
            text-decoration: none;
            margin-top: 10px;
        }
        .btn-primary:hover {
            background-color: #34495e;
        }
    </style>
    <title>FTP Консоль</title>
    <?php echo base64_decode('PG1ldGEgbmFtZT0iYXV0aG9yIiBjb250ZW50PSJTb3plbGFubyBARGVuaV9jcm1wIj4='); ?>
    <?php echo showAuthorCredit(); ?>
</head>
<body>
    <div class="console">
        <?php foreach (array_reverse($log_contents) as $line): ?>
            <div><?php echo htmlspecialchars($line); ?></div>
        <?php endforeach; ?>
    </div>
    <a href="admin_deni.php" class="btn-primary">Вернуться назад</a>
    <script>
        const author = '%cАвтор: @deni_crmp';
        const authorStyle = 'color: #2c3e50; font-weight: bold; font-size: 14px;';
        console.log(author, authorStyle);

        setInterval(() => {
            location.reload();
        }, 30000);
    </script>
</body>
</html>
