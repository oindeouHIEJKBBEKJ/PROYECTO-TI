<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
redirectIfNotLogged();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    $user_id = $_SESSION['user_id'];

    if ($new_password !== $confirm_password) {
        $_SESSION['error'] = "Las contraseñas no coinciden";
        header("Location: perfil.php");
        exit();
    }

    $database = new Database();
    $db = $database->getConnection();

    // Verificar contraseña actual
    $query = "SELECT password_hash FROM usuarios WHERE id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();

    if ($stmt->rowCount() == 1) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (password_verify($current_password, $user['password_hash'])) {
            // Actualizar contraseña
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

            $update_query = "UPDATE usuarios SET password_hash = :password_hash WHERE id = :user_id";
            $update_stmt = $db->prepare($update_query);
            $update_stmt->bindParam(':password_hash', $new_password_hash);
            $update_stmt->bindParam(':user_id', $user_id);

            if ($update_stmt->execute()) {
                $_SESSION['success'] = "Contraseña actualizada correctamente";
            } else {
                $_SESSION['error'] = "Error al actualizar la contraseña";
            }
        } else {
            $_SESSION['error'] = "La contraseña actual es incorrecta";
        }
    }
}

header("Location: perfil.php");
exit();
?>