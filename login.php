<?php /*RUTA: sistemasclinica/login.php*/?>
<?php
// Si el usuario ya ha iniciado sesión, redirigir al dashboard
session_start();

if (isset($_SESSION['usuario']) && $_SESSION['usuario']['autenticado']) {
    header('Location: views/include/dashboard.administrador.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Inicio Sesión | Clínica</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link href="css/estiloLogin.css" rel="stylesheet" />
    <link href="css/estilos.css" rel="stylesheet" />
    <link href="css/estiloLoginFix.css" rel="stylesheet" />
    <link href="css/estiloEyeIcon.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500&display=swap" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/alertas/alertas.js"></script>
</head>

<body>
    <div class="login-wrapper">
        <div class="login-container">
            <h2>Sistema Clínica</h2>

            <!-- Formulario de inicio de sesión -->
            <form id="form-login">
                <div class="input-group">
                    <input type="text" id="inputUser" class="form-control" placeholder="Usuario" required>
                </div>
                <div class="input-group">
                    <input type="password" id="inputPassword" class="form-control" placeholder="Contraseña" required>
                    <div class="eye-icon-wrapper">
                        <i class="bi bi-eye eye-icon" id="togglePassword"></i>
                    </div>
                </div>
                <button type="submit" class="btn btn-custom">Iniciar Sesión</button>
            </form>

            <div class="footer">
                <a href="./views/register.php" class="btn btn-link">Registrar Nuevo Usuario</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script>
        // Configuración global para SweetAlert2
        const sweetAlertOptions = {
            customClass: {
                container: 'static-swal',
                popup: 'swal-popup-fixed'
            },
            allowOutsideClick: false,
            position: 'center'
        };

        document.addEventListener("DOMContentLoaded", () => {
            document.querySelector("#form-login").addEventListener("submit", (event) => {
                event.preventDefault();

                const nomuser = document.querySelector("#inputUser").value;
                const passuser = document.querySelector("#inputPassword").value;

                if (!nomuser || !passuser) {
                    Swal.fire({
                        ...sweetAlertOptions,
                        icon: 'warning',
                        title: 'Error',
                        text: 'Por favor, complete todos los campos.'
                    });
                    return;
                }

                // Mostrar pantalla de carga
                Swal.fire({
                    ...sweetAlertOptions,
                    title: 'Ingresando',
                    text: 'Espere por favor...',
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Primero intentamos login como administrador o doctor
                const paramsAdmin = new URLSearchParams();
                paramsAdmin.append("operacion", "login");
                paramsAdmin.append("nomuser", nomuser);
                paramsAdmin.append("passuser", passuser);

                fetch(`./controllers/usuario.controller.php?${paramsAdmin.toString()}`)
                    .then(response => response.json())
                    .then(acceso => {
                        if (acceso.autenticado) {
                            // Redirigir según el rol
                            if (acceso.rol === 'ADMINISTRADOR') {
                                window.location.href = './views/include/dashboard.administrador.php';
                            } else if (acceso.rol === 'DOCTOR') {
                                window.location.href = './views/include/dashboard.doctor.php';
                            }
                        } else {
                            // Si no es administrador ni doctor, intentamos como enfermero (usando email)
                            const paramsEnfermero = new URLSearchParams();
                            paramsEnfermero.append("operacion", "login_enfermero");
                            paramsEnfermero.append("email", nomuser);
                            paramsEnfermero.append("passuser", passuser);

                            return fetch(`./controllers/usuario.controller.php?${paramsEnfermero.toString()}`)
                                .then(response => response.json());
                        }
                    })
                    .then(accesoEnfermero => {
                        if (accesoEnfermero) {
                            if (accesoEnfermero.autenticado) {
                                window.location.href = './views/include/dashboard.enfermeria.php';
                            } else {
                                // Intentar como paciente (usando número de documento como usuario)
                                const paramsPaciente = new URLSearchParams();
                                paramsPaciente.append("operacion", "login_paciente");
                                paramsPaciente.append("nrodoc", nomuser);
                                paramsPaciente.append("passuser", passuser);

                                return fetch(`./controllers/usuario.controller.php?${paramsPaciente.toString()}`)
                                    .then(response => response.json());
                            }
                        }
                    })
                    .then(accesoPaciente => {
                        if (accesoPaciente) {
                            if (accesoPaciente.autenticado) {
                                window.location.href = './views/include/dashboard.paciente.php';
                            } else {
                                Swal.fire({
                                    ...sweetAlertOptions,
                                    icon: 'error',
                                    title: 'Error',
                                    text: accesoPaciente.mensaje || "Credenciales incorrectas."
                                });
                            }
                        }
                    })
                    .catch(error => {
                        console.error("Error:", error);
                        Swal.close();

                        Swal.fire({
                            ...sweetAlertOptions,
                            icon: 'error',
                            title: 'Error',
                            text: 'Error al conectar con el servidor.'
                        });
                    });
            });

            // Mostrar y ocultar contraseña
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('inputPassword');

            togglePassword.addEventListener('click', () => {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                togglePassword.classList.toggle('bi-eye-slash');
            });
        });

        // Si estás usando la función AlertaSweetAlert personalizada, sobreescríbela así:
        // Esta parte es opcional, solo si usas esa función en tu código
        if (typeof AlertaSweetAlert === 'function') {
            const originalAlertaSweetAlert = AlertaSweetAlert;
            AlertaSweetAlert = function(type, title, text, icon) {
                if (type === "loading") {
                    Swal.fire({
                        ...sweetAlertOptions,
                        title: title || 'Cargando',
                        text: text || 'Por favor espere...',
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                } else if (type === "closeLoading") {
                    Swal.close();
                } else {
                    Swal.fire({
                        ...sweetAlertOptions,
                        icon: type || 'info',
                        title: title || '',
                        text: text || ''
                    });
                }
            };
        }
    </script>
</body>

</html>