<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
redirectIfNotLogged();

if (!isEgresado()) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['postulacion_id'])) {
    $postulacion_id = $_POST['postulacion_id'];
    $user_id = $_SESSION['user_id'];

    $database = new Database();
    $db = $database->getConnection();

    // Verificar que la postulación pertenece al usuario
    $check_query = "SELECT * FROM postulaciones WHERE id = :postulacion_id AND usuario_id = :user_id";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':postulacion_id', $postulacion_id);
    $check_stmt->bindParam(':user_id', $user_id);
    $check_stmt->execute();

    if ($check_stmt->rowCount() == 1) {
        $postulacion = $check_stmt->fetch(PDO::FETCH_ASSOC);

        // Eliminar la postulación
        $delete_query = "DELETE FROM postulaciones WHERE id = :postulacion_id";
        $delete_stmt = $db->prepare($delete_query);
        $delete_stmt->bindParam(':postulacion_id', $postulacion_id);

        if ($delete_stmt->execute()) {
            // Verificar si quedan postulaciones para esta oferta
            $count_query = "SELECT COUNT(*) as total FROM postulaciones WHERE oferta_id = :oferta_id";
            $count_stmt = $db->prepare($count_query);
            $count_stmt->bindParam(':oferta_id', $postulacion['oferta_id']);
            $count_stmt->execute();
            $count = $count_stmt->fetch(PDO::FETCH_ASSOC);

            // Si no hay más postulaciones, cambiar semáforo a verde
            if ($count['total'] == 0) {
                $update_query = "UPDATE ofertas_laborales SET semaforo = 'verde' WHERE id = :oferta_id";
                $update_stmt = $db->prepare($update_query);
                $update_stmt->bindParam(':oferta_id', $postulacion['oferta_id']);
                $update_stmt->execute();
            }

            $_SESSION['success'] = "Postulación cancelada correctamente";
        } else {
            $_SESSION['error'] = "Error al cancelar la postulación";
        }
    } else {
        $_SESSION['error'] = "Postulación no encontrada";
    }
}

header("Location: mis_postulaciones.php");
exit();
?>