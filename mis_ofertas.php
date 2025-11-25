<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
redirectIfNotLogged();

// Solo docentes, TI y admin pueden gestionar ofertas
if (!isDocente() && !isTI() && !isAdmin()) {
    header("Location: dashboard.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$user_id = $_SESSION['user_id'];
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

// Obtener ofertas del usuario
$query = "SELECT o.*, 
                 COUNT(p.id) as total_postulantes,
                 DATE_FORMAT(o.fecha_publicacion, '%d/%m/%Y') as fecha_publicacion_format,
                 DATE_FORMAT(o.fecha_expiracion, '%d/%m/%Y') as fecha_expiracion_format
          FROM ofertas_laborales o 
          LEFT JOIN postulaciones p ON o.id = p.oferta_id 
          WHERE o.usuario_publica_id = :user_id 
          GROUP BY o.id 
          ORDER BY o.fecha_publicacion DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$ofertas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include 'includes/header.php'; ?>

<div class="container my-5">
    <div class="row mb-4">
        <div class="col">
            <h2 class="fw-bold">Mis Ofertas Publicadas</h2>
            <p class="text-muted">Gestiona tus ofertas laborales publicadas</p>
        </div>
        <div class="col-auto">
            <a href="nueva_oferta.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Nueva Oferta
            </a>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <?php if (count($ofertas) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Título</th>
                                <th>Empresa</th>
                                <th>Estado</th>
                                <th>Postulantes</th>
                                <th>Publicación</th>
                                <th>Expiración</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ofertas as $oferta): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($oferta['titulo']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($oferta['empresa']); ?></td>
                                    <td>
                                        <?php echo getEstadoBadge($oferta['estado']); ?>
                                        <br>
                                        <small>
                                            <span class="traffic-light traffic-<?php echo $oferta['semaforo']; ?> me-1"></span>
                                            <?php echo getSemaforoText($oferta['semaforo']); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span
                                            class="badge bg-<?php echo $oferta['total_postulantes'] > 0 ? 'primary' : 'secondary'; ?>">
                                            <?php echo $oferta['total_postulantes']; ?> postulantes
                                        </span>
                                    </td>
                                    <td><?php echo $oferta['fecha_publicacion_format']; ?></td>
                                    <td>
                                        <?php if (strtotime($oferta['fecha_expiracion']) < time()): ?>
                                            <span class="text-danger"><?php echo $oferta['fecha_expiracion_format']; ?></span>
                                        <?php else: ?>
                                            <?php echo $oferta['fecha_expiracion_format']; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="ver_postulantes.php?oferta_id=<?php echo $oferta['id']; ?>"
                                                class="btn btn-outline-primary" title="Ver Postulantes">
                                                <i class="fas fa-users"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-info" data-bs-toggle="modal"
                                                data-bs-target="#editModal<?php echo $oferta['id']; ?>" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php if ($oferta['estado'] != 'rechazada' && strtotime($oferta['fecha_expiracion']) >= time()): ?>
                                                <button type="button" class="btn btn-outline-danger btn-cerrar"
                                                    data-oferta-id="<?php echo $oferta['id']; ?>"
                                                    data-oferta-titulo="<?php echo htmlspecialchars($oferta['titulo']); ?>"
                                                    title="Cerrar Oferta">
                                                    <i class="fas fa-lock"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Modal para editar oferta -->
                                <div class="modal fade" id="editModal<?php echo $oferta['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Editar Oferta:
                                                    <?php echo htmlspecialchars($oferta['titulo']); ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST" action="editar_oferta.php">
                                                <div class="modal-body">
                                                    <input type="hidden" name="oferta_id" value="<?php echo $oferta['id']; ?>">

                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <label for="titulo" class="form-label">Título del Puesto</label>
                                                            <input type="text" class="form-control" id="titulo" name="titulo"
                                                                value="<?php echo htmlspecialchars($oferta['titulo']); ?>"
                                                                required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="empresa" class="form-label">Empresa</label>
                                                            <input type="text" class="form-control" id="empresa" name="empresa"
                                                                value="<?php echo htmlspecialchars($oferta['empresa']); ?>"
                                                                required>
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="descripcion" class="form-label">Descripción</label>
                                                        <textarea class="form-control" id="descripcion" name="descripcion"
                                                            rows="4"
                                                            required><?php echo htmlspecialchars($oferta['descripcion']); ?></textarea>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="requisitos" class="form-label">Requisitos</label>
                                                        <textarea class="form-control" id="requisitos" name="requisitos"
                                                            rows="4"
                                                            required><?php echo htmlspecialchars($oferta['requisitos']); ?></textarea>
                                                    </div>

                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <label for="contacto" class="form-label">Contacto</label>
                                                            <input type="text" class="form-control" id="contacto"
                                                                name="contacto"
                                                                value="<?php echo htmlspecialchars($oferta['contacto']); ?>"
                                                                required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="fecha_expiracion" class="form-label">Fecha
                                                                Expiración</label>
                                                            <input type="date" class="form-control" id="fecha_expiracion"
                                                                name="fecha_expiracion"
                                                                value="<?php echo $oferta['fecha_expiracion']; ?>" required>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Estadísticas rápidas -->
                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h4><?php echo count($ofertas); ?></h4>
                                <p>Total Ofertas</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h4><?php echo count(array_filter($ofertas, fn($o) => $o['estado'] == 'aprobada')); ?></h4>
                                <p>Ofertas Aprobadas</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h4><?php echo count(array_filter($ofertas, fn($o) => $o['estado'] == 'pendiente')); ?></h4>
                                <p>Pendientes</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h4><?php echo array_sum(array_column($ofertas, 'total_postulantes')); ?></h4>
                                <p>Total Postulantes</p>
                            </div>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-briefcase fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">No has publicado ofertas</h4>
                    <p class="text-muted">Comienza publicando tu primera oferta laboral.</p>
                    <a href="nueva_oferta.php" class="btn btn-primary">Publicar Primera Oferta</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Formulario oculto para cerrar oferta -->
<form id="cerrarForm" method="POST" action="cerrar_oferta.php" style="display: none;">
    <input type="hidden" name="oferta_id" id="cerrarOfertaId">
</form>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Manejar cierre de oferta
        document.querySelectorAll('.btn-cerrar').forEach(button => {
            button.addEventListener('click', function () {
                const ofertaId = this.getAttribute('data-oferta-id');
                const ofertaTitulo = this.getAttribute('data-oferta-titulo');

                if (confirm(`¿Estás seguro de que quieres cerrar la oferta "${ofertaTitulo}"? Esta acción no se puede deshacer.`)) {
                    document.getElementById('cerrarOfertaId').value = ofertaId;
                    document.getElementById('cerrarForm').submit();
                }
            });
        });
    });
</script>

<?php
// Funciones auxiliares
function getEstadoBadge($estado)
{
    switch ($estado) {
        case 'aprobada':
            return '<span class="badge bg-success">Aprobada</span>';
        case 'pendiente':
            return '<span class="badge bg-warning">Pendiente</span>';
        case 'rechazada':
            return '<span class="badge bg-danger">Rechazada</span>';
        default:
            return '<span class="badge bg-secondary">Desconocido</span>';
    }
}

function getSemaforoText($semaforo)
{
    switch ($semaforo) {
        case 'verde':
            return 'Disponible';
        case 'amarillo':
            return 'Con postulantes';
        case 'rojo':
            return 'Cerrada';
        default:
            return 'Desconocido';
    }
}
?>

<?php include 'includes/footer.php'; ?>