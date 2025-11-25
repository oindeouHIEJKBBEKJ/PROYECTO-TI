<?php
require_once 'includes/auth.php';
?>

<?php include 'includes/header.php'; ?>

<!-- Sección de inicio (pública) -->
<section id="homeSection">
    <!-- Hero Section -->
    <div class="hero-section text-center">
        <div class="container">
            <h1 class="display-4 fw-bold mb-4">Sistema de Seguimiento de Egresados TI</h1>
            <p class="lead mb-4">Conectando a nuestros egresados con oportunidades laborales en el área de Tecnologías
                de la Información</p>
            <?php if (!isLoggedIn()): ?>
                <a href="register.php" class="btn btn-light btn-lg">Registrarse</a>
            <?php else: ?>
                <a href="dashboard.php" class="btn btn-light btn-lg">Ir al Dashboard</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Características del sistema -->
    <div class="container my-5">
        <div class="row text-center mb-5">
            <div class="col">
                <h2 class="fw-bold">Funcionalidades del Sistema</h2>
                <p class="text-muted">Diseñado específicamente para egresados de la Ingeniería en TI</p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card feature-card">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-briefcase fa-3x mb-3 text-primary"></i>
                        <h5 class="card-title">Bolsa de Trabajo</h5>
                        <p class="card-text">Consulta ofertas laborales validadas específicas para el área de TI.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card feature-card">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-user-graduate fa-3x mb-3 text-primary"></i>
                        <h5 class="card-title">Perfil Profesional</h5>
                        <p class="card-text">Crea y mantén actualizado tu perfil profesional para que las empresas te
                            encuentren.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card feature-card">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-chart-line fa-3x mb-3 text-primary"></i>
                        <h5 class="card-title">Seguimiento</h5>
                        <p class="card-text">Mantén un registro actualizado de tu trayectoria profesional.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Información para diferentes roles -->
    <div class="bg-light py-5">
        <div class="container">
            <div class="row text-center mb-4">
                <div class="col">
                    <h2 class="fw-bold">Acceso para Diferentes Usuarios</h2>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-user-graduate fa-2x mb-3 text-success"></i>
                            <h5 class="card-title">Egresados</h5>
                            <p class="card-text">Accede a ofertas laborales, actualiza tu perfil y recibe
                                notificaciones.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-chalkboard-teacher fa-2x mb-3 text-warning"></i>
                            <h5 class="card-title">Docentes</h5>
                            <p class="card-text">Publica ofertas laborales y contacta con egresados para oportunidades.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-laptop-code fa-2x mb-3 text-danger"></i>
                            <h5 class="card-title">Personal TI</h5>
                            <p class="card-text">Gestiona usuarios, ofertas y da mantenimiento al sistema.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-user-cog fa-2x mb-3 text-primary"></i>
                            <h5 class="card-title">Administradores</h5>
                            <p class="card-text">Supervisa el sistema, valida ofertas y genera reportes.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>