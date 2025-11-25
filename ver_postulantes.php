<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
redirectIfNotLogged();

// Solo docentes, TI y admin pueden ver postulantes
if (!isDocente() && !isTI() && !isAdmin()) {
    header("Location: dashboard.php");
    exit();
}

if (!isset($_GET['oferta_id'])) {
    header("Location: mis_ofertas.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$oferta_id = $_GET['oferta_id'];
$user_id = $_SESSION['user_id'];

// Verificar que la oferta pertenece al usuario
$oferta_query = "SELECT o.*, u.nombre_completo as publicador 
                 FROM ofertas_laborales o 
                 JOIN usuarios u ON o.usuario_publica_id = u.id 
                 WHERE o.id = :oferta_id AND o.usuario_publica_id = :user_id";
$oferta_stmt = $db->prepare($oferta_query);
$oferta_stmt->bindParam(':oferta_id', $oferta_id);
$oferta_stmt->bindParam(':user_id', $user_id);
$oferta_stmt->execute();

if ($oferta_stmt->rowCount() == 0) {
    header("Location: mis_ofertas.php");
    exit();
}

$oferta = $oferta_stmt->fetch(PDO::FETCH_ASSOC);

// Obtener postulantes
$postulantes_query = "SELECT p.*, u.nombre_completo, u.email, u.telefono, u.matricula,
                             s.especialidad, s.habilidades, s.experiencia_laboral,
                             DATE_FORMAT(p.fecha_postulacion, '%d/%m/%Y %H:%i') as fecha_postulacion_format
                      FROM postulaciones p 
                      JOIN usuarios u ON p.usuario_id = u.id 
                      LEFT JOIN seguimiento_egresados s ON u.id = s.usuario_id 
                      WHERE p.oferta_id = :oferta_id 
                      ORDER BY p.fecha_postulacion DESC";
$postulantes_stmt = $db->prepare($postulantes_query);
$postulantes_stmt->bindParam(':oferta_id', $oferta_id);
$postulantes_stmt->execute();
$postulantes = $postulantes_stmt->fetchAll(PDO::FETCH_ASSOC);

// Procesar selección de postulante
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['seleccionar_postulante'])) {
    $postulacion_id = $_POST['postulacion_id'];

    // Actualizar estado de la postulación
    $update_query = "UPDATE postulaciones SET estado = 'aceptada' WHERE id = :postulacion_id";
    $update_stmt = $db->prepare($update_query);
    $update_stmt->bindParam(':postulacion_id', $postulacion_id);

    if ($update_stmt->execute()) {
        // Cambiar semáforo a rojo y rechazar otras postulaciones
        $update_oferta_query = "UPDATE ofertas_laborales SET semaforo = 'rojo' WHERE id = :oferta_id";
        $update_oferta_stmt = $db->prepare($update_oferta_query);
        $update_oferta_stmt->bindParam(':oferta_id', $oferta_id);
        $update_oferta_stmt->execute();

        // Rechazar otras postulaciones
        $rechazar_query = "UPDATE postulaciones SET estado = 'rechazada' 
                          WHERE oferta_id = :oferta_id AND id != :postulacion_id";
        $rechazar_stmt = $db->prepare($rechazar_query);
        $rechazar_stmt->bindParam(':oferta_id', $oferta_id);
        $rechazar_stmt->bindParam(':postulacion_id', $postulacion_id);
        $rechazar_stmt->execute();

        $_SESSION['success'] = "Postulante seleccionado correctamente. La oferta ha sido cerrada.";
        header("Location: ver_postulantes.php?oferta_id=" . $oferta_id);
        exit();
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="container my-5">
    <div class="row mb-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="mis_ofertas.php">Mis Ofertas</a></li>
                    <li class="breadcrumb-item active">Postulantes</li>
                </ol>
            </nav>

            <h2 class="fw-bold">Postulantes para: <?php echo htmlspecialchars($oferta['titulo']); ?></h2>
            <p class="text-muted">Empresa: <?php echo htmlspecialchars($oferta['empresa']); ?></p>
        </div>
        <div class="col-auto">
            <span class="traffic-light traffic-<?php echo $oferta['semaforo']; ?> me-2"></span>
            <span
                class="badge bg-<?php echo $oferta['semaforo'] == 'rojo' ? 'danger' : ($oferta['semaforo'] == 'amarillo' ? 'warning' : 'success'); ?>">
                <?php echo getSemaforoText($oferta['semaforo']); ?>
            </span>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success'];
        unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-12">
            <?php if (count($postulantes) > 0): ?>
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-users me-2"></i>
                            <?php echo count($postulantes); ?> Postulante(s)
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($postulantes as $postulante): ?>
                            <div class="card mb-3 <?php echo $postulante['estado'] == 'aceptada' ? 'border-success' : ''; ?>">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h5 class="card-title">
                                                <?php echo htmlspecialchars($postulante['nombre_completo']); ?>
                                                <?php if ($postulante['estado'] == 'aceptada'): ?>
                                                    <span class="badge bg-success ms-2">Seleccionado</span>
                                                <?php elseif ($postulante['estado'] == 'rechazada'): ?>
                                                    <span class="badge bg-danger ms-2">Rechazado</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning ms-2">Pendiente</span>
                                                <?php endif; ?>
                                            </h5>

                                            <p class="card-text mb-1">
                                                <strong>Email:</strong> <?php echo htmlspecialchars($postulante['email']); ?>
                                            </p>
                                            <?php if ($postulante['telefono']): ?>
                                                <p class="card-text mb-1">
                                                    <strong>Teléfono:</strong>
                                                    <?php echo htmlspecialchars($postulante['telefono']); ?>
                                                </p>
                                            <?php endif; ?>
                                            <p class="card-text mb-1">
                                                <strong>Matrícula:</strong>
                                                <?php echo htmlspecialchars($postulante['matricula']); ?>
                                            </p>
                                            <p class="card-text mb-1">
                                                <strong>Postuló el:</strong>
                                                <?php echo $postulante['fecha_postulacion_format']; ?>
                                            </p>

                                            <?php if ($postulante['especialidad']): ?>
                                                <p class="card-text mb-1">
                                                    <strong>Especialidad:</strong>
                                                    <?php echo htmlspecialchars($postulante['especialidad']); ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="d-grid gap-2">
                                                <button type="button" class="btn btn-outline-primary btn-sm"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#perfilModal<?php echo $postulante['id']; ?>">
                                                    <i class="fas fa-eye me-1"></i>Ver Perfil Completo
                                                </button>

                                                <?php if ($postulante['estado'] == 'pendiente' && $oferta['semaforo'] != 'rojo'): ?>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="postulacion_id"
                                                            value="<?php echo $postulante['id']; ?>">
                                                        <button type="submit" name="seleccionar_postulante"
                                                            class="btn btn-success btn-sm w-100"
                                                            onclick="return confirm('¿Estás seguro de seleccionar a este postulante?')">
                                                            <i class="fas fa-check me-1"></i>Seleccionar
                                                        </button>
                                                    </form>
                                                <?php endif; ?>

                                                <a href="mailto:<?php echo htmlspecialchars($postulante['email']); ?>"
                                                    class="btn btn-outline-info btn-sm">
                                                    <i class="fas fa-envelope me-1"></i>Contactar
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal para ver perfil completo -->
                            <div class="modal fade" id="perfilModal<?php echo $postulante['id']; ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Perfil de
                                                <?php echo htmlspecialchars($postulante['nombre_completo']); ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6>Información Personal</h6>
                                                    <p><strong>Nombre:</strong>
                                                        <?php echo htmlspecialchars($postulante['nombre_completo']); ?></p>
                                                    <p><strong>Email:</strong>
                                                        <?php echo htmlspecialchars($postulante['email']); ?></p>
                                                    <p><strong>Teléfono:</strong>
                                                        <?php echo htmlspecialchars($postulante['telefono'] ?? 'No especificado'); ?>
                                                    </p>
                                                    <p><strong>Matrícula:</strong>
                                                        <?php echo htmlspecialchars($postulante['matricula']); ?></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6>Información Académica</h6>
                                                    <p><strong>Especialidad:</strong>
                                                        <?php echo htmlspecialchars($postulante['especialidad'] ?? 'No especificada'); ?>
                                                    </p>
                                                    <p><strong>Postuló el:</strong>
                                                        <?php echo $postulante['fecha_postulacion_format']; ?></p>
                                                </div>
                                            </div>

                                            <?php if ($postulante['habilidades']): ?>
                                                <div class="mt-3">
                                                    <h6>Habilidades Técnicas</h6>
                                                    <p><?php echo nl2br(htmlspecialchars($postulante['habilidades'])); ?></p>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($postulante['experiencia_laboral']): ?>
                                                <div class="mt-3">
                                                    <h6>Experiencia Laboral</h6>
                                                    <p><?php echo nl2br(htmlspecialchars($postulante['experiencia_laboral'])); ?>
                                                    </p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Cerrar</button>
                                            <a href="mailto:<?php echo htmlspecialchars($postulante['email']); ?>"
                                                class="btn btn-primary">
                                                <i class="fas fa-envelope me-1"></i>Contactar
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-user-times fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">No hay postulantes</h4>
                    <p class="text-muted">Aún no hay postulantes para esta oferta.</p>
                    <a href="mis_ofertas.php" class="btn btn-primary">Volver a Mis Ofertas</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Función auxiliar
function getSemaforoText($semaforo)
{
    switch ($semaforo) {
        case 'verde':
            return 'Vacante disponible';
        case 'amarillo':
            return 'Tiene postulantes';
        case 'rojo':
            return 'Vacante cubierta';
        default:
            return 'Estado desconocido';
    }
}
?>

<?php include 'includes/footer.php'; ?>