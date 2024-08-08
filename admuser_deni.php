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
        if ($currentAdminLevel == 0) {
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

function showAuthorCredit() {
    return '<!-- Жесткий программист - @deni_crmp -->';
}

$admins = [];
$sql = "SELECT id, name, admin FROM accounts WHERE admin > 0";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $admins[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['removeAdminId'])) {
        $adminId = $_POST['removeAdminId'];

        $sql = "UPDATE accounts SET admin = 0 WHERE id = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("i", $adminId);
            if ($stmt->execute()) {
                header("Location: admuser_deni.php");
                exit();
            } else {
                $error = "Ошибка при удалении админа.";
            }
        } else {
            $error = "Ошибка в подготовке запроса: " . $conn->error;
        }
    } elseif (isset($_POST['username'])) {
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
            max-width: 800px;
            width: 100%;
            background-color: #fff;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
        }
        h3 {
            color: #2c3e50;
            margin-bottom: 25px;
            font-weight: bold;
            font-size: 24px;
            font-family: 'GTA', sans-serif;
            letter-spacing: 1px;
        }
        .table-responsive {
            max-width: 100%;
            overflow-x: auto;
        }
        .table th, .table td {
            text-align: center;
            vertical-align: middle;
        }
        .btn-danger {
            background-color: #e74c3c;
            border: none;
            border-radius: 5px;
            padding: 5px 15px;
            font-weight: bold;
            font-size: 14px;
            color: #fff;
            transition: background-color 0.3s;
        }
        .btn-danger:hover {
            background-color: #c0392b;
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
            margin-top: 20px;
            margin-bottom: 20px;
        }
        .btn-primary:hover {
            background-color: #34495e;
        }
        .form-control {
            background-color: #f7f7f7;
            color: #333;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 20px;
        }
    </style>
    <title>Админ Панель и Список Админов</title>
    <?php echo base64_decode('PG1ldGEgbmFtZT0iYXV0aG9yIiBjb250ZW50PSJTb3plbGFubyBARGVuaV9jcm1wIj4='); ?>
    <?php echo showAuthorCredit(); ?>
</head>
<body>
    <div class="container">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php elseif (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <h3 class="mt-5">Список Админов</h3>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Никнейм</th>
                        <th>Уровень Админа</th>
                        <th>Действие</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($admins as $admin): ?>
                        <tr>
                            <td><?php echo $admin['id']; ?></td>
                            <td><?php echo htmlspecialchars($admin['name']); ?></td>
                            <td><?php echo $admin['admin']; ?></td>
                            <td>
                                <button class="btn btn-danger" data-toggle="modal" data-target="#confirmModal" data-id="<?php echo $admin['id']; ?>" data-name="<?php echo htmlspecialchars($admin['name']); ?>">
                                    Удалить
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <a href="admin_deni.php" class="btn-primary">Вернуться назад</a>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">Подтверждение удаления</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Вы точно хотите снять пользователя <span id="adminName"></span> с поста Администратора?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                    <form method="post">
                        <input type="hidden" name="removeAdminId" id="removeAdminId">
                        <button type="submit" class="btn btn-danger">Да</button>
                    </form>
                                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script>
        $('#confirmModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var adminId = button.data('id');
            var adminName = button.data('name');

            var modal = $(this);
            modal.find('#adminName').text(adminName);
            modal.find('#removeAdminId').val(adminId);
        });

        const author = '%cАвтор: @deni_crmp';
        const authorStyle = 'color: #2c3e50; font-weight: bold; font-size: 14px;';
        console.log(author, authorStyle);
    </script>
</body>
</html>

