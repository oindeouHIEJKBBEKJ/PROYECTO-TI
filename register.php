<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
redirectIfLogged();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre_completo = $_POST['nombre_completo'] ?? '';
    $matricula = $_POST['matricula'] ?? '';
    $curp = $_POST['curp'] ?? '';
    $email = $_POST['email'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $tipo_usuario = $_POST['tipo_usuario'] ?? '';
    $id_docente = $_POST['id_docente'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($password !== $confirm_password) {
        $error = "Las contraseñas no coinciden";
    } else {
        $database = new Database();
        $db = $database->getConnection();

        // Verificar si el usuario ya existe
        $check_query = "SELECT id FROM usuarios WHERE username = :username OR email = :email";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':username', $username);
        $check_stmt->bindParam(':email', $email);
        $check_stmt->execute();

        if ($check_stmt->rowCount() > 0) {
            $error = "El usuario o email ya están registrados";
        } else {
            // Generar username automático si no se proporciona
            if (empty($username)) {
                $username = strtolower(str_replace(' ', '.', $nombre_completo));
            }

            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $query = "INSERT INTO usuarios (nombre_completo, matricula, curp, email, telefono, tipo_usuario, id_docente, username, password_hash) 
                     VALUES (:nombre_completo, :matricula, :curp, :email, :telefono, :tipo_usuario, :id_docente, :username, :password_hash)";

            $stmt = $db->prepare($query);
            $stmt->bindParam(':nombre_completo', $nombre_completo);
            $stmt->bindParam(':matricula', $matricula);
            $stmt->bindParam(':curp', $curp);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':telefono', $telefono);
            $stmt->bindParam(':tipo_usuario', $tipo_usuario);
            $stmt->bindParam(':id_docente', $id_docente);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password_hash', $password_hash);

            if ($stmt->execute()) {
                $success = "Registro exitoso. Ahora puedes iniciar sesión.";
            } else {
                $error = "Error al registrar el usuario";
            }
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title text-center">Registro de Usuario</h3>
                </div>
                <div class="card-body">

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="register.php">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nombre_completo" class="form-label">Nombre Completo *</label>
                                <input type="text" class="form-control" id="nombre_completo" name="nombre_completo"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label for="matricula" class="form-label">Matrícula *</label>
                                <input type="text" class="form-control" id="matricula" name="matricula" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="curp" class="form-label">CURP *</label>
                                <input type="text" class="form-control" id="curp" name="curp" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Correo Electrónico *</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" id="telefono" name="telefono">
                            </div>
                            <div class="col-md-6">
                                <label for="tipo_usuario" class="form-label">Tipo de Usuario *</label>
                                <select class="form-select" id="tipo_usuario" name="tipo_usuario" required>
                                    <option value="" selected disabled>Selecciona tu tipo de usuario</option>
                                    <option value="egresado">Egresado</option>
                                    <option value="docente">Docente</option>
                                    <option value="ti">Personal TI</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="id_docente" class="form-label">ID de Docente (solo para docentes)</label>
                                <input type="text" class="form-control" id="id_docente" name="id_docente" disabled>
                            </div>
                            <div class="col-md-6">
                                <label for="username" class="form-label">Usuario (se generará automáticamente si se deja
                                    vacío)</label>
                                <input type="text" class="form-control" id="username" name="username">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="password" class="form-label">Contraseña *</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="col-md-6">
                                <label for="confirm_password" class="form-label">Confirmar Contraseña *</label>
                                <input type="password" class="form-control" id="confirm_password"
                                    name="confirm_password" required>
                            </div>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                            <label class="form-check-label" for="terms">Acepto los términos y condiciones</label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Registrarse</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('tipo_usuario').addEventListener('change', function () {
        const docenteIdField = document.getElementById('id_docente');
        if (this.value === 'docente') {
            docenteIdField.disabled = false;
            docenteIdField.required = true;
        } else {
            docenteIdField.disabled = true;
            docenteIdField.required = false;
            docenteIdField.value = '';
        }
    });
</script>

<?php include 'includes/footer.php'; ?>