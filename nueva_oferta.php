<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
redirectIfNotLogged();

if (!(isDocente() || isTI() || isAdmin())) {
    // Usuarios sin permiso son redirigidos
    header('Location: ofertas.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $empresa = trim($_POST['empresa'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $requisitos = trim($_POST['requisitos'] ?? '');
    $contacto = trim($_POST['contacto'] ?? '');
    $fecha_expiracion = trim($_POST['fecha_expiracion'] ?? '');

    if ($titulo === '') $errors[] = 'El título es obligatorio.';
    if ($empresa === '') $errors[] = 'El nombre de la empresa es obligatorio.';
    if ($descripcion === '') $errors[] = 'La descripción es obligatoria.';
    if ($fecha_expiracion === '') $errors[] = 'La fecha de expiración es obligatoria.';

    if (empty($errors)) {
        try {
            $query = "INSERT INTO ofertas_laborales (titulo, empresa, descripcion, requisitos, contacto, fecha_publicacion, fecha_expiracion, semaforo, usuario_publica_id, estado) VALUES (:titulo, :empresa, :descripcion, :requisitos, :contacto, CURDATE(), :fecha_expiracion, 'verde', :usuario_id, 'aprobada')";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':titulo', $titulo);
            $stmt->bindParam(':empresa', $empresa);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':requisitos', $requisitos);
            $stmt->bindParam(':contacto', $contacto);
            $stmt->bindParam(':fecha_expiracion', $fecha_expiracion);
            $stmt->bindParam(':usuario_id', $_SESSION['user_id']);

            if ($stmt->execute()) {
                $success = 'Oferta publicada correctamente.';
                // Limpiar valores del formulario
                $titulo = $empresa = $descripcion = $requisitos = $contacto = $fecha_expiracion = '';
            } else {
                $errors[] = 'Error al guardar la oferta. Intente nuevamente.';
            }
        } catch (PDOException $e) {
            $errors[] = 'Error de base de datos: ' . $e->getMessage();
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="container my-5">
    <div class="row mb-4">
        <div class="col">
            <h2 class="fw-bold">Publicar Nueva Oferta</h2>
            <p class="text-muted">Ingrese los datos de la oferta laboral</p>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $err): ?>
                    <li><?php echo htmlspecialchars($err); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Título</label>
            <input type="text" name="titulo" class="form-control" value="<?php echo htmlspecialchars($titulo ?? ''); ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Empresa</label>
            <input type="text" name="empresa" class="form-control" value="<?php echo htmlspecialchars($empresa ?? ''); ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Descripción</label>
            <textarea name="descripcion" class="form-control" rows="6"><?php echo htmlspecialchars($descripcion ?? ''); ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Requisitos</label>
            <textarea name="requisitos" class="form-control" rows="4"><?php echo htmlspecialchars($requisitos ?? ''); ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Contacto</label>
            <input type="text" name="contacto" class="form-control" value="<?php echo htmlspecialchars($contacto ?? ''); ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Fecha de expiración</label>
            <input type="date" name="fecha_expiracion" class="form-control" value="<?php echo htmlspecialchars($fecha_expiracion ?? ''); ?>">
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Publicar Oferta</button>
            <a href="ofertas.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>

<!--
SQL recomendada para crear la tabla `ofertas_laborales` si no existe:

CREATE TABLE `ofertas_laborales` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) NOT NULL,
  `empresa` varchar(255) NOT NULL,
  `descripcion` text NOT NULL,
  `requisitos` text DEFAULT NULL,
  `contacto` varchar(255) DEFAULT NULL,
  `fecha_publicacion` date DEFAULT NULL,
  `fecha_expiracion` date DEFAULT NULL,
  `semaforo` enum('verde','amarillo','rojo') DEFAULT 'verde',
  `usuario_publica_id` int DEFAULT NULL,
  `estado` varchar(50) DEFAULT 'pendiente',
  PRIMARY KEY (`id`)
);

-- Asegúrate que la tabla `postulaciones` y `usuarios` existan según el uso del sistema.
-- Ejecuta este SQL en tu gestor (phpMyAdmin o MySQL CLI).
-->
