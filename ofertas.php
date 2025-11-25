<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
redirectIfNotLogged();

$database = new Database();
$db = $database->getConnection();

// Obtener ofertas aprobadas
$query = "SELECT o.*, u.nombre_completo as publicador 
          FROM ofertas_laborales o 
          LEFT JOIN usuarios u ON o.usuario_publica_id = u.id 
          WHERE o.estado = 'aprobada' AND o.fecha_expiracion >= CURDATE() 
          ORDER BY o.fecha_publicacion DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$ofertas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Procesar postulación
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['postular'])) {
    $oferta_id = $_POST['oferta_id'];
    $usuario_id = $_SESSION['user_id'];

    // Verificar si ya está postulado
    $check_query = "SELECT id FROM postulaciones WHERE oferta_id = :oferta_id AND usuario_id = :usuario_id";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':oferta_id', $oferta_id);
    $check_stmt->bindParam(':usuario_id', $usuario_id);
    $check_stmt->execute();

    if ($check_stmt->rowCount() == 0) {
        $insert_query = "INSERT INTO postulaciones (oferta_id, usuario_id) VALUES (:oferta_id, :usuario_id)";
        $insert_stmt = $db->prepare($insert_query);
        $insert_stmt->bindParam(':oferta_id', $oferta_id);
        $insert_stmt->bindParam(':usuario_id', $usuario_id);

        if ($insert_stmt->execute()) {
            // Actualizar semáforo a amarillo
            $update_query = "UPDATE ofertas_laborales SET semaforo = 'amarillo' WHERE id = :oferta_id";
            $update_stmt = $db->prepare($update_query);
            $update_stmt->bindParam(':oferta_id', $oferta_id);
            $update_stmt->execute();

            $success = "¡Postulación exitosa!";
        } else {
            $error = "Error al postularse";
        }
    } else {
        $error = "Ya te has postulado a esta oferta";
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="container my-5">
    <div class="row mb-4">
        <div class="col">
            <h2 class="fw-bold">Ofertas Laborales Disponibles</h2>
            <p class="text-muted">Oportunidades validadas para egresados de TI</p>
        </div>
        <div class="col-auto">
            <?php if (isDocente() || isTI() || isAdmin()): ?>
                <a href="nueva_oferta.php" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Publicar Oferta
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="row">
        <?php if (count($ofertas) > 0): ?>
            <?php foreach ($ofertas as $oferta): ?>
                <div class="col-md-6 mb-4">
                    <div class="card offer-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title"><?php echo htmlspecialchars($oferta['titulo']); ?></h5>
                                <span class="traffic-light traffic-<?php echo $oferta['semaforo']; ?>"
                                    title="<?php echo getSemaforoText($oferta['semaforo']); ?>"></span>
                            </div>
                            <h6 class="card-subtitle mb-2 text-muted"><?php echo htmlspecialchars($oferta['empresa']); ?></h6>
                            <p class="card-text"><?php echo truncateText($oferta['descripcion'], 150); ?></p>

                            <div class="mb-3">
                                <strong>Requisitos:</strong>
                                <p class="small"><?php echo truncateText($oferta['requisitos'], 100); ?></p>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    Publicado: <?php echo date('d/m/Y', strtotime($oferta['fecha_publicacion'])); ?><br>
                                    Por: <?php echo htmlspecialchars($oferta['publicador']); ?>
                                </small>

                                <div class="btn-group">
                                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#ofertaModal<?php echo $oferta['id']; ?>">
                                        Ver Detalles
                                    </button>

                                    <?php if (isEgresado()): ?>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="oferta_id" value="<?php echo $oferta['id']; ?>">
                                            <button type="submit" name="postular" class="btn btn-success btn-sm">
                                                Postularme
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal para detalles de oferta -->
                <div class="modal fade" id="ofertaModal<?php echo $oferta['id']; ?>" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><?php echo htmlspecialchars($oferta['titulo']); ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Empresa:</strong> <?php echo htmlspecialchars($oferta['empresa']); ?></p>
                                        <p><strong>Contacto:</strong> <?php echo htmlspecialchars($oferta['contacto']); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Publicado:</strong>
                                            <?php echo date('d/m/Y', strtotime($oferta['fecha_publicacion'])); ?></p>
                                        <p><strong>Válida hasta:</strong>
                                            <?php echo date('d/m/Y', strtotime($oferta['fecha_expiracion'])); ?></p>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <h6>Descripción del puesto:</h6>
                                    <p><?php echo nl2br(htmlspecialchars($oferta['descripcion'])); ?></p>
                                </div>

                                <div class="mb-3">
                                    <h6>Requisitos:</h6>
                                    <p><?php echo nl2br(htmlspecialchars($oferta['requisitos'])); ?></p>
                                </div>

                                <div class="mb-3">
                                    <h6>Publicado por:</h6>
                                    <p><?php echo htmlspecialchars($oferta['publicador']); ?></p>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <?php if (isEgresado()): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="oferta_id" value="<?php echo $oferta['id']; ?>">
                                        <button type="submit" name="postular" class="btn btn-success">
                                            Postularme a esta oferta
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i>
                    No hay ofertas laborales disponibles en este momento.
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Funciones auxiliares
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

function truncateText($text, $length)
{
    if (strlen($text) > $length) {
        return substr($text, 0, $length) . '...';
    }
    return $text;
}
?>

<?php include 'includes/footer.php'; ?>