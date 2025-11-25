<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
redirectIfNotLogged();

// Solo docentes, TI y admin pueden ver estadísticas
if (!isDocente() && !isTI() && !isAdmin()) {
    header("Location: dashboard.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$user_id = $_SESSION['user_id'];

// Estadísticas generales
$stats_query = "SELECT 
    COUNT(DISTINCT o.id) as total_ofertas,
    COUNT(DISTINCT p.id) as total_postulaciones,
    COUNT(DISTINCT CASE WHEN o.estado = 'aprobada' THEN o.id END) as ofertas_aprobadas,
    COUNT(DISTINCT CASE WHEN o.estado = 'pendiente' THEN o.id END) as ofertas_pendientes,
    COUNT(DISTINCT CASE WHEN o.estado = 'rechazada' THEN o.id END) as ofertas_rechazadas,
    COUNT(DISTINCT CASE WHEN o.semaforo = 'rojo' THEN o.id END) as ofertas_cerradas,
    COUNT(DISTINCT u.id) as total_egresados
FROM ofertas_laborales o
LEFT JOIN postulaciones p ON o.id = p.oferta_id
LEFT JOIN usuarios u ON u.tipo_usuario = 'egresado' AND u.activo = 1
WHERE o.usuario_publica_id = :user_id";
$stats_stmt = $db->prepare($stats_query);
$stats_stmt->bindParam(':user_id', $user_id);
$stats_stmt->execute();
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

// Ofertas por mes (últimos 6 meses)
$ofertas_mes_query = "SELECT 
    DATE_FORMAT(fecha_publicacion, '%Y-%m') as mes,
    COUNT(*) as total
FROM ofertas_laborales 
WHERE usuario_publica_id = :user_id 
AND fecha_publicacion >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
GROUP BY DATE_FORMAT(fecha_publicacion, '%Y-%m')
ORDER BY mes DESC
LIMIT 6";
$ofertas_mes_stmt = $db->prepare($ofertas_mes_query);
$ofertas_mes_stmt->bindParam(':user_id', $user_id);
$ofertas_mes_stmt->execute();
$ofertas_por_mes = $ofertas_mes_stmt->fetchAll(PDO::FETCH_ASSOC);

// Postulaciones por oferta
$postulaciones_query = "SELECT 
    o.titulo,
    COUNT(p.id) as total_postulantes
FROM ofertas_laborales o
LEFT JOIN postulaciones p ON o.id = p.oferta_id
WHERE o.usuario_publica_id = :user_id
GROUP BY o.id, o.titulo
ORDER BY total_postulantes DESC
LIMIT 10";
$postulaciones_stmt = $db->prepare($postulaciones_query);
$postulaciones_stmt->bindParam(':user_id', $user_id);
$postulaciones_stmt->execute();
$postulaciones_por_oferta = $postulaciones_stmt->fetchAll(PDO::FETCH_ASSOC);

// Egresados por especialidad
$especialidades_query = "SELECT 
    s.especialidad,
    COUNT(*) as total
FROM seguimiento_egresados s
JOIN usuarios u ON s.usuario_id = u.id
WHERE u.activo = 1
GROUP BY s.especialidad
HAVING s.especialidad IS NOT NULL AND s.especialidad != ''
ORDER BY total DESC
LIMIT 8";
$especialidades_stmt = $db->prepare($especialidades_query);
$especialidades_stmt->execute();
$egresados_por_especialidad = $especialidades_stmt->fetchAll(PDO::FETCH_ASSOC);

// Ofertas por estado
$ofertas_estado_query = "SELECT 
    estado,
    COUNT(*) as total
FROM ofertas_laborales 
WHERE usuario_publica_id = :user_id
GROUP BY estado";
$ofertas_estado_stmt = $db->prepare($ofertas_estado_query);
$ofertas_estado_stmt->bindParam(':user_id', $user_id);
$ofertas_estado_stmt->execute();
$ofertas_por_estado = $ofertas_estado_stmt->fetchAll(PDO::FETCH_ASSOC);

// Postulaciones exitosas (donde el postulante fue seleccionado)
$postulaciones_exitosas_query = "SELECT 
    COUNT(DISTINCT p.id) as total_exitosas
FROM postulaciones p
JOIN ofertas_laborales o ON p.oferta_id = o.id
WHERE p.estado = 'aceptada' 
AND o.usuario_publica_id = :user_id";
$postulaciones_exitosas_stmt = $db->prepare($postulaciones_exitosas_query);
$postulaciones_exitosas_stmt->bindParam(':user_id', $user_id);
$postulaciones_exitosas_stmt->execute();
$postulaciones_exitosas = $postulaciones_exitosas_stmt->fetch(PDO::FETCH_ASSOC);
?>

<?php include 'includes/header.php'; ?>

<div class="container my-5">
    <div class="row mb-4">
        <div class="col">
            <h2 class="fw-bold">Estadísticas del Sistema</h2>
            <p class="text-muted">Métricas y análisis de tu actividad en el sistema</p>
        </div>
        <div class="col-auto">
            <button class="btn btn-outline-primary" onclick="window.print()">
                <i class="fas fa-print me-2"></i>Imprimir Reporte
            </button>
        </div>
    </div>

    <!-- Tarjetas de estadísticas principales -->
    <div class="row mb-5">
        <div class="col-md-3 mb-4">
            <div class="card dashboard-card text-white bg-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>Ofertas Publicadas</h4>
                            <h2><?php echo $stats['total_ofertas']; ?></h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-briefcase fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card dashboard-card text-white bg-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>Ofertas Aprobadas</h4>
                            <h2><?php echo $stats['ofertas_aprobadas']; ?></h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card dashboard-card text-white bg-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>Total Postulaciones</h4>
                            <h2><?php echo $stats['total_postulaciones']; ?></h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-file-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card dashboard-card text-white bg-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>Egresados Registrados</h4>
                            <h2><?php echo $stats['total_egresados']; ?></h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-graduate fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Gráfico de ofertas por estado -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Ofertas por Estado</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 300px;">
                        <canvas id="estadoOfertasChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico de egresados por especialidad -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Egresados por Especialidad</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 300px;">
                        <canvas id="especialidadChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Tabla de ofertas más populares -->
        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Ofertas con Más Postulantes</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Oferta</th>
                                    <th>Postulantes</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($postulaciones_por_oferta) > 0): ?>
                                    <?php foreach ($postulaciones_por_oferta as $oferta): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($oferta['titulo']); ?></td>
                                            <td>
                                                <span
                                                    class="badge bg-primary"><?php echo $oferta['total_postulantes']; ?></span>
                                            </td>
                                            <td>
                                                <?php if ($oferta['total_postulantes'] >= 5): ?>
                                                    <span class="badge bg-success">Muy Popular</span>
                                                <?php elseif ($oferta['total_postulantes'] >= 3): ?>
                                                    <span class="badge bg-warning">Popular</span>
                                                <?php else: ?>
                                                    <span class="badge bg-info">Normal</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-3">
                                            No hay datos de postulaciones disponibles
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas adicionales -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Métricas Adicionales</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            Ofertas Pendientes
                            <span
                                class="badge bg-warning rounded-pill"><?php echo $stats['ofertas_pendientes']; ?></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            Ofertas Cerradas
                            <span class="badge bg-danger rounded-pill"><?php echo $stats['ofertas_cerradas']; ?></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            Ofertas Rechazadas
                            <span
                                class="badge bg-secondary rounded-pill"><?php echo $stats['ofertas_rechazadas']; ?></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            Postulaciones Exitosas
                            <span
                                class="badge bg-success rounded-pill"><?php echo $postulaciones_exitosas['total_exitosas']; ?></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            Tasa de Éxito
                            <span class="badge bg-info rounded-pill">
                                <?php
                                $tasa_exito = $stats['total_postulaciones'] > 0 ?
                                    round(($postulaciones_exitosas['total_exitosas'] / $stats['total_postulaciones']) * 100, 1) : 0;
                                echo $tasa_exito . '%';
                                ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ofertas por mes -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Ofertas por Mes (Últimos 6 meses)</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <?php if (count($ofertas_por_mes) > 0): ?>
                            <?php foreach ($ofertas_por_mes as $mes): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <?php echo date('M Y', strtotime($mes['mes'] . '-01')); ?>
                                    <span class="badge bg-primary rounded-pill"><?php echo $mes['total']; ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center text-muted py-3">
                                No hay ofertas en los últimos 6 meses
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen ejecutivo -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Resumen Ejecutivo</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Actividad Reciente</h6>
                            <ul>
                                <li>Total de ofertas publicadas: <strong><?php echo $stats['total_ofertas']; ?></strong>
                                </li>
                                <li>Ofertas activas: <strong><?php echo $stats['ofertas_aprobadas']; ?></strong></li>
                                <li>Postulaciones recibidas:
                                    <strong><?php echo $stats['total_postulaciones']; ?></strong></li>
                                <li>Egresados en el sistema: <strong><?php echo $stats['total_egresados']; ?></strong>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Efectividad</h6>
                            <ul>
                                <li>Tasa de aprobación:
                                    <strong>
                                        <?php
                                        $tasa_aprobacion = $stats['total_ofertas'] > 0 ?
                                            round(($stats['ofertas_aprobadas'] / $stats['total_ofertas']) * 100, 1) : 0;
                                        echo $tasa_aprobacion . '%';
                                        ?>
                                    </strong>
                                </li>
                                <li>Postulaciones por oferta:
                                    <strong>
                                        <?php
                                        $promedio_postulaciones = $stats['total_ofertas'] > 0 ?
                                            round($stats['total_postulaciones'] / $stats['total_ofertas'], 1) : 0;
                                        echo $promedio_postulaciones;
                                        ?>
                                    </strong>
                                </li>
                                <li>Ofertas cerradas exitosamente:
                                    <strong><?php echo $postulaciones_exitosas['total_exitosas']; ?></strong>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Incluir Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Gráfico de ofertas por estado
        const estadoOfertasCtx = document.getElementById('estadoOfertasChart').getContext('2d');
        const estadoOfertasChart = new Chart(estadoOfertasCtx, {
            type: 'doughnut',
            data: {
                labels: ['Aprobadas', 'Pendientes', 'Rechazadas', 'Cerradas'],
                datasets: [{
                    data: [
                        <?php echo $stats['ofertas_aprobadas']; ?>,
                        <?php echo $stats['ofertas_pendientes']; ?>,
                        <?php echo $stats['ofertas_rechazadas']; ?>,
                        <?php echo $stats['ofertas_cerradas']; ?>
                    ],
                    backgroundColor: [
                        '#28a745',
                        '#ffc107',
                        '#dc3545',
                        '#6c757d'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    title: {
                        display: true,
                        text: 'Distribución de Ofertas'
                    }
                }
            }
        });

        // Gráfico de egresados por especialidad
        const especialidadCtx = document.getElementById('especialidadChart').getContext('2d');
        const especialidadChart = new Chart(especialidadCtx, {
            type: 'bar',
            data: {
                labels: [
                    <?php
                    $labels = [];
                    foreach ($egresados_por_especialidad as $especialidad) {
                        $labels[] = "'" . addslashes($especialidad['especialidad']) . "'";
                    }
                    echo implode(', ', $labels);
                    ?>
                ],
                datasets: [{
                    label: 'Egresados',
                    data: [
                        <?php
                        $data = [];
                        foreach ($egresados_por_especialidad as $especialidad) {
                            $data[] = $especialidad['total'];
                        }
                        echo implode(', ', $data);
                        ?>
                    ],
                    backgroundColor: [
                        '#007bff', '#28a745', '#ffc107', '#dc3545',
                        '#6f42c1', '#e83e8c', '#fd7e14', '#20c997'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Egresados por Especialidad'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    });
</script>

<style>
    .chart-container {
        position: relative;
    }

    @media print {
        .btn {
            display: none !important;
        }

        .card {
            border: 1px solid #000 !important;
            break-inside: avoid;
        }
    }
</style>

<?php include 'includes/footer.php'; ?>