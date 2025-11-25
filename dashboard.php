<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
redirectIfNotLogged();
?>

<?php include 'includes/header.php'; ?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mb-4">Bienvenido, <?php echo $_SESSION['user_name']; ?></h1>
            <p class="lead">Sistema de Seguimiento de Egresados de TI - Universidad Tecnológica de Puebla</p>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Estadísticas rápidas -->
        <div class="col-md-3 mb-4">
            <div class="card dashboard-card text-white bg-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>Ofertas Activas</h4>
                            <h2>12</h2>
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
                            <h4>Mis Postulaciones</h4>
                            <h2>3</h2>
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
                            <h2>45</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-graduate fa-2x"></i>
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
                            <h4>Empresas</h4>
                            <h2>8</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-building fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones rápidas según el rol -->
    <div class="row mt-4">
        <div class="col-md-12">
            <h3>Acciones Rápidas</h3>
        </div>

        <?php if (isEgresado()): ?>
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-search fa-3x text-primary mb-3"></i>
                        <h5>Buscar Ofertas</h5>
                        <p>Encuentra oportunidades laborales</p>
                        <a href="ofertas.php" class="btn btn-primary">Explorar</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-user-edit fa-3x text-success mb-3"></i>
                        <h5>Actualizar Perfil</h5>
                        <p>Mantén tu información actualizada</p>
                        <a href="perfil.php" class="btn btn-success">Editar</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-history fa-3x text-info mb-3"></i>
                        <h5>Mis Postulaciones</h5>
                        <p>Revisa el estado de tus aplicaciones</p>
                        <a href="mis_postulaciones.php" class="btn btn-info">Ver</a>
                    </div>
                </div>
            </div>
        <?php elseif (isDocente() || isTI() || isAdmin()): ?>
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-plus-circle fa-3x text-primary mb-3"></i>
                        <h5>Publicar Oferta</h5>
                        <p>Comparte oportunidades laborales</p>
                        <a href="nueva_oferta.php" class="btn btn-primary">Crear</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-list-alt fa-3x text-success mb-3"></i>
                        <h5>Gestionar Ofertas</h5>
                        <p>Administra tus publicaciones</p>
                        <a href="mis_ofertas.php" class="btn btn-success">Administrar</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-chart-bar fa-3x text-info mb-3"></i>
                        <h5>Estadísticas</h5>
                        <p>Visualiza datos del sistema</p>
                        <a href="estadisticas.php" class="btn btn-info">Ver Reportes</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>