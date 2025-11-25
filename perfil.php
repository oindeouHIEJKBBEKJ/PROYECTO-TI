<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
redirectIfNotLogged();

$database = new Database();
$db = $database->getConnection();

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Obtener datos del usuario
$user_query = "SELECT * FROM usuarios WHERE id = :user_id";
$user_stmt = $db->prepare($user_query);
$user_stmt->bindParam(':user_id', $user_id);
$user_stmt->execute();
$usuario = $user_stmt->fetch(PDO::FETCH_ASSOC);

// Obtener información de seguimiento (si es egresado)
$seguimiento = null;
if (isEgresado()) {
    $seg_query = "SELECT * FROM seguimiento_egresados WHERE usuario_id = :user_id";
    $seg_stmt = $db->prepare($seg_query);
    $seg_stmt->bindParam(':user_id', $user_id);
    $seg_stmt->execute();
    $seguimiento = $seg_stmt->fetch(PDO::FETCH_ASSOC);
}

// Procesar actualización del perfil
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre_completo = $_POST['nombre_completo'] ?? '';
    $email = $_POST['email'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    
    // Actualizar información básica
    $update_query = "UPDATE usuarios SET nombre_completo = :nombre_completo, email = :email, telefono = :telefono WHERE id = :user_id";
    $update_stmt = $db->prepare($update_query);
    $update_stmt->bindParam(':nombre_completo', $nombre_completo);
    $update_stmt->bindParam(':email', $email);
    $update_stmt->bindParam(':telefono', $telefono);
    $update_stmt->bindParam(':user_id', $user_id);
    
    if ($update_stmt->execute()) {
        $_SESSION['user_name'] = $nombre_completo;
        $_SESSION['user_email'] = $email;
        $success = "Perfil actualizado correctamente";
        
        // Actualizar usuario en variable local
        $usuario['nombre_completo'] = $nombre_completo;
        $usuario['email'] = $email;
        $usuario['telefono'] = $telefono;
    } else {
        $error = "Error al actualizar el perfil";
    }
    
    // Si es egresado, actualizar información de seguimiento
    if (isEgresado()) {
        $genero = $_POST['genero'] ?? '';
        $ano_nacimiento = $_POST['ano_nacimiento'] ?? '';
        $especialidad = $_POST['especialidad'] ?? '';
        $generacion = $_POST['generacion'] ?? '';
        $experiencia_laboral = $_POST['experiencia_laboral'] ?? '';
        $habilidades = $_POST['habilidades'] ?? '';
        $areas_interes = $_POST['areas_interes'] ?? '';
        
        if ($seguimiento) {
            // Actualizar registro existente
            $seg_update = "UPDATE seguimiento_egresados SET 
                          genero = :genero, ano_nacimiento = :ano_nacimiento, especialidad = :especialidad,
                          generacion = :generacion, experiencia_laboral = :experiencia_laboral,
                          habilidades = :habilidades, areas_interes = :areas_interes 
                          WHERE usuario_id = :user_id";
        } else {
            // Crear nuevo registro
            $seg_update = "INSERT INTO seguimiento_egresados 
                          (usuario_id, genero, ano_nacimiento, especialidad, generacion, 
                           experiencia_laboral, habilidades, areas_interes) 
                          VALUES 
                          (:user_id, :genero, :ano_nacimiento, :especialidad, :generacion,
                           :experiencia_laboral, :habilidades, :areas_interes)";
        }
        
        $seg_stmt = $db->prepare($seg_update);
        $seg_stmt->bindParam(':genero', $genero);
        $seg_stmt->bindParam(':ano_nacimiento', $ano_nacimiento);
        $seg_stmt->bindParam(':especialidad', $especialidad);
        $seg_stmt->bindParam(':generacion', $generacion);
        $seg_stmt->bindParam(':experiencia_laboral', $experiencia_laboral);
        $seg_stmt->bindParam(':habilidades', $habilidades);
        $seg_stmt->bindParam(':areas_interes', $areas_interes);
        
        if ($seg_stmt) {
            if ($seguimiento) {
                $seg_stmt->bindParam(':user_id', $user_id);
            } else {
                $seg_stmt->bindParam(':user_id', $user_id);
            }
            $seg_stmt->execute();
            
            // Actualizar variable local
            $seguimiento = [
                'genero' => $genero,
                'ano_nacimiento' => $ano_nacimiento,
                'especialidad' => $especialidad,
                'generacion' => $generacion,
                'experiencia_laboral' => $experiencia_laboral,
                'habilidades' => $habilidades,
                'areas_interes' => $areas_interes
            ];
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($usuario['nombre_completo']); ?>&size=120&background=0D8ABC&color=fff" 
                         class="rounded-circle mb-3" alt="Foto de perfil">
                    <h4><?php echo htmlspecialchars($usuario['nombre_completo']); ?></h4>
                    <p class="text-muted">
                        <span class="role-badge role-<?php echo $_SESSION['user_role']; ?>">
                            <?php echo strtoupper($_SESSION['user_role']); ?>
                        </span>
                    </p>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                            Editar Perfil
                        </button>
                        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                            Cambiar Contraseña
                        </button>
                    </div>
                </div>
            </div>
            
            <?php if (isEgresado()): ?>
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Información de Seguimiento</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text"><small>Esta información es solo visible para administradores del sistema.</small></p>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Género:</span>
                                <span><?php echo $seguimiento['genero'] ?? 'No especificado'; ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Año de nacimiento:</span>
                                <span><?php echo $seguimiento['ano_nacimiento'] ?? 'No especificado'; ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Especialidad:</span>
                                <span><?php echo $seguimiento['especialidad'] ?? 'No especificado'; ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Generación:</span>
                                <span><?php echo $seguimiento['generacion'] ?? 'No especificado'; ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="col-md-8">
            <!-- Información Profesional para Egresados -->
            <?php if (isEgresado()): ?>
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Información Profesional</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label"><strong>Experiencia Laboral</strong></label>
                            <p><?php echo nl2br(htmlspecialchars($seguimiento['experiencia_laboral'] ?? 'No especificada')); ?></p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label"><strong>Habilidades Técnicas</strong></label>
                            <p><?php echo nl2br(htmlspecialchars($seguimiento['habilidades'] ?? 'No especificadas')); ?></p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label"><strong>Áreas de Interés</strong></label>
                            <p><?php echo nl2br(htmlspecialchars($seguimiento['areas_interes'] ?? 'No especificadas')); ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Información de Contacto -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Información de Contacto</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Matrícula:</strong> <?php echo htmlspecialchars($usuario['matricula']); ?></p>
                            <p><strong>CURP:</strong> <?php echo htmlspecialchars($usuario['curp']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($usuario['email']); ?></p>
                            <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($usuario['telefono'] ?? 'No especificado'); ?></p>
                        </div>
                    </div>
                    <?php if ($usuario['id_docente']): ?>
                        <p><strong>ID Docente:</strong> <?php echo htmlspecialchars($usuario['id_docente']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar perfil -->
<div class="modal fade" id="editProfileModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Perfil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="perfil.php">
                <div class="modal-body">
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="nombre_completo" class="form-label">Nombre Completo *</label>
                            <input type="text" class="form-control" id="nombre_completo" name="nombre_completo" 
                                   value="<?php echo htmlspecialchars($usuario['nombre_completo']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Correo Electrónico *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" id="telefono" name="telefono" 
                                   value="<?php echo htmlspecialchars($usuario['telefono'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <?php if (isEgresado()): ?>
                        <hr>
                        <h6>Información de Seguimiento</h6>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="genero" class="form-label">Género</label>
                                <select class="form-select" id="genero" name="genero">
                                    <option value="">Seleccionar...</option>
                                    <option value="masculino" <?php echo ($seguimiento['genero'] ?? '') == 'masculino' ? 'selected' : ''; ?>>Masculino</option>
                                    <option value="femenino" <?php echo ($seguimiento['genero'] ?? '') == 'femenino' ? 'selected' : ''; ?>>Femenino</option>
                                    <option value="otro" <?php echo ($seguimiento['genero'] ?? '') == 'otro' ? 'selected' : ''; ?>>Otro</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="ano_nacimiento" class="form-label">Año de Nacimiento</label>
                                <input type="number" class="form-control" id="ano_nacimiento" name="ano_nacimiento" 
                                       min="1950" max="2005" value="<?php echo $seguimiento['ano_nacimiento'] ?? ''; ?>">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="especialidad" class="form-label">Especialidad</label>
                                <input type="text" class="form-control" id="especialidad" name="especialidad" 
                                       value="<?php echo htmlspecialchars($seguimiento['especialidad'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="generacion" class="form-label">Generación</label>
                                <input type="text" class="form-control" id="generacion" name="generacion" 
                                       value="<?php echo htmlspecialchars($seguimiento['generacion'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="experiencia_laboral" class="form-label">Experiencia Laboral</label>
                            <textarea class="form-control" id="experiencia_laboral" name="experiencia_laboral" rows="3"><?php echo htmlspecialchars($seguimiento['experiencia_laboral'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="habilidades" class="form-label">Habilidades Técnicas</label>
                            <textarea class="form-control" id="habilidades" name="habilidades" rows="3" placeholder="Lista tus habilidades separadas por comas"><?php echo htmlspecialchars($seguimiento['habilidades'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="areas_interes" class="form-label">Áreas de Interés</label>
                            <textarea class="form-control" id="areas_interes" name="areas_interes" rows="3" placeholder="Describe las áreas de TI que más te interesan"><?php echo htmlspecialchars($seguimiento['areas_interes'] ?? ''); ?></textarea>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para cambiar contraseña -->
<div class="modal fade" id="changePasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cambiar Contraseña</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="cambiar_password.php">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Contraseña Actual</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Nueva Contraseña</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirmar Nueva Contraseña</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Cambiar Contraseña</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>