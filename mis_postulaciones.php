<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
redirectIfNotLogged();

if (!isEgresado()) {
    header("Location: dashboard.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$user_id = $_SESSION['user_id'];

// Obtener postulaciones del usuario
$query = "SELECT p.*, o.titulo, o.empresa, o.semaforo, o.contacto, 
                 DATE_FORMAT(p.fecha_postulacion, '%d/%m/%Y') as fecha_postulacion_formatted
          FROM postulaciones p 
          JOIN ofertas_laborales o ON p.oferta_id = o.id 
          WHERE p.usuario_id = :user_id 
          ORDER BY p.fecha_postulacion DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$postulaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include 'includes/header.php'; ?>

<div class="container my-5">
    <div class="row mb-4">
        <div class="col">
            <h2 class="fw-bold">Mis Postulaciones</h2>
            <p class="text-muted">Seguimiento de tus aplicaciones a ofertas laborales</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <?php if (count($postulaciones) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Puesto</th>
                                <th>Empresa</th>
                                <th>Fecha de Postulación</th>
                                <th>Estado de la Oferta</th>
                                <th>Mi Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($postulaciones as $postulacion): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($postulacion['titulo']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($postulacion['empresa']); ?></td>
                                    <td><?php echo $postulacion['fecha_postulacion_formatted']; ?></td>
                                    <td>
                                        <span class="traffic-light traffic-<?php echo $postulacion['semaforo']; ?> me-2"></span>
                                        <?php echo getSemaforoText($postulacion['semaforo']); ?>
                                    </td>
                                    <td>
                                        <?php echo getEstadoPostulacionBadge($postulacion['estado']); ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal"
                                                data-bs-target="#detalleModal<?php echo $postulacion['id']; ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if ($postulacion['estado'] == 'pendiente' && $postulacion['semaforo'] != 'rojo'): ?>
                                                <button type="button" class="btn btn-outline-danger btn-cancelar"
                                                    data-postulacion-id="<?php echo $postulacion['id']; ?>"
                                                    data-oferta-titulo="<?php echo htmlspecialchars($postulacion['titulo']); ?>">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Modal para detalles -->
                                <div class="modal fade" id="detalleModal<?php echo $postulacion['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Detalles de Postulación</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <p><strong>Puesto:</strong>
                                                            <?php echo htmlspecialchars($postulacion['titulo']); ?></p>
                                                        <p><strong>Empresa:</strong>
                                                            <?php echo htmlspecialchars($postulacion['empresa']); ?></p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p><strong>Fecha de Postulación:</strong>
                                                            <?php echo $postulacion['fecha_postulacion_formatted']; ?></p>
                                                        <p><strong>Contacto:</strong>
                                                            <?php echo htmlspecialchars($postulacion['contacto']); ?></p>
                                                    </div>
                                                </div>

                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <p><strong>Estado de la Oferta:</strong></p>
                                                        <span
                                                            class="traffic-light traffic-<?php echo $postulacion['semaforo']; ?> me-2"></span>
                                                        <?php echo getSemaforoText($postulacion['semaforo']); ?>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p><strong>Estado de tu Postulación:</strong></p>
                                                        <?php echo getEstadoPostulacionBadge($postulacion['estado']); ?>
                                                    </div>
                                                </div>

                                                <?php if ($postulacion['estado'] != 'pendiente'): ?>
                                                    <div class="alert alert-info">
                                                        <strong>Actualización:</strong><br>
                                                        <?php if ($postulacion['estado'] == 'aceptada'): ?>
                                                            ¡Felicidades! Tu postulación ha sido aceptada. Por favor contacta a la
                                                            empresa para los siguientes pasos.
                                                        <?php else: ?>
                                                            Lamentablemente tu postulación no fue seleccionada en esta ocasión. Te
                                                            invitamos a seguir aplicando a otras ofertas.
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Cerrar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Estadísticas -->
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h4><?php echo count(array_filter($postulaciones, fn($p) => $p['estado'] == 'pendiente')); ?>
                                </h4>
                                <p>Postulaciones Pendientes</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h4><?php echo count(array_filter($postulaciones, fn($p) => $p['estado'] == 'aceptada')); ?>
                                </h4>
                                <p>Postulaciones Aceptadas</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h4><?php echo count(array_filter($postulaciones, fn($p) => $p['semaforo'] == 'rojo')); ?>
                                </h4>
                                <p>Ofertas Cerradas</p>
                            </div>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-file-alt fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">No tienes postulaciones</h4>
                    <p class="text-muted">Aún no te has postulado a ninguna oferta laboral.</p>
                    <a href="ofertas.php" class="btn btn-primary">Explorar Ofertas</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Formulario oculto para cancelar postulación -->
<form id="cancelForm" method="POST" action="cancelar_postulacion.php" style="display: none;">
    <input type="hidden" name="postulacion_id" id="cancelPostulacionId">
</form>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Manejar cancelación de postulación
        document.querySelectorAll('.btn-cancelar').forEach(button => {
            button.addEventListener('click', function () {
                const postulacionId = this.getAttribute('data-postulacion-id');
                const ofertaTitulo = this.getAttribute('data-oferta-titulo');

                if (confirm(`¿Estás seguro de que quieres cancelar tu postulación a "${ofertaTitulo}"?`)) {
                    document.getElementById('cancelPostulacionId').value = postulacionId;
                    document.getElementById('cancelForm').submit();
                }
            });
        });
    });
</script>

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

function getEstadoPostulacionBadge($estado)
{
    switch ($estado) {
        case 'pendiente':
            return '<span class="badge bg-warning">Pendiente</span>';
        case 'aceptada':
            return '<span class="badge bg-success">Aceptada</span>';
        case 'rechazada':
            return '<span class="badge bg-danger">Rechazada</span>';
        default:
            return '<span class="badge bg-secondary">Desconocido</span>';
    }
}
?>

<?php include 'includes/footer.php'; ?>