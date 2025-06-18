<?php
require_once '../../include/header.administrador.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Registrar Especialidad</title>
    <link rel="stylesheet" href="../../../css/registrarEspecialidad.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>

<body>
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Registrar Nueva Especialidad</h3>
                    </div>
                    <div class="card-body">
                        <form id="formRegistrarEspecialidad">
                            <div class="mb-4">
                                <label for="especialidad" class="form-label">Nombre de la Especialidad</label>
                                <input type="text" class="form-control" id="especialidad" name="especialidad" required>
                                <div class="invalid-feedback">
                                    Por favor ingrese el nombre de la especialidad.
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="precioatencion" class="form-label">Precio de Atención (S/.)</label>
                                <div class="input-group">
                                    <span class="input-group-text">S/.</span>
                                    <input type="number" class="form-control" id="precioatencion" name="precioatencion" step="0.01" min="0" required>
                                    <div class="invalid-feedback">
                                        Por favor ingrese un precio válido.
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <a href="../ListarEspecialidad/listarEspecialidad.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Volver al Listado
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Guardar Especialidad
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            // Evento para enviar el formulario
            $('#formRegistrarEspecialidad').on('submit', function(e) {
                e.preventDefault();
                
                // Validar formulario
                if (!validarFormulario()) {
                    return;
                }

                // Recoger datos del formulario
                const especialidad = $('#especialidad').val();
                const precioatencion = $('#precioatencion').val();

                // Mostrar spinner de carga
                Swal.fire({
                    title: 'Procesando',
                    text: 'Registrando especialidad...',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Enviar solicitud AJAX
                $.ajax({
                    url: '../../../controllers/especialidad.controller.php?op=registrar',
                    type: 'POST',
                    data: {
                        especialidad: especialidad,
                        precioatencion: precioatencion
                    },
                    dataType: 'json',
                    success: function(response) {
                        Swal.close();

                        if (response.status) {
                            // Mostrar mensaje de éxito
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: response.mensaje,
                                confirmButtonColor: '#3085d6'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    // Redireccionar al listado
                                    window.location.href = '../ListarEspecialidad/listarEspecialidad.php';
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.mensaje,
                                confirmButtonColor: '#3085d6'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.close();
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de Conexión',
                            text: 'No se pudo registrar la especialidad. Detalles: ' + error,
                            confirmButtonColor: '#3085d6'
                        });
                    }
                });
            });

            // Función para validar el formulario
            function validarFormulario() {
                let valido = true;
                
                // Validar campo de especialidad
                const especialidad = $('#especialidad').val().trim();
                if (!especialidad) {
                    $('#especialidad').addClass('is-invalid').removeClass('is-valid');
                    valido = false;
                } else {
                    $('#especialidad').addClass('is-valid').removeClass('is-invalid');
                }

                // Validar campo de precio
                const precioatencion = $('#precioatencion').val();
                if (!precioatencion || parseFloat(precioatencion) <= 0) {
                    $('#precioatencion').addClass('is-invalid').removeClass('is-valid');
                    valido = false;
                } else {
                    $('#precioatencion').addClass('is-valid').removeClass('is-invalid');
                }

                // Mostrar alerta si no es válido
                if (!valido) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Campos Incompletos',
                        text: 'Por favor complete todos los campos requeridos correctamente.',
                        confirmButtonColor: '#3085d6'
                    });
                }

                return valido;
            }

            // Evento para limpiar clases de validación al cambiar input
            $(document).on('input', '.form-control', function() {
                $(this).removeClass('is-invalid');
                if ($(this).val()) {
                    $(this).addClass('is-valid');
                } else {
                    $(this).removeClass('is-valid');
                }
            });
        });
    </script>

    <style>
        /* Estilos para la tarjeta principal */
        .card {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1) !important;
        }

        .card-header {
            padding: 1.2rem 1.5rem;
        }

        .card-body {
            padding: 2rem;
        }

        /* Estilos para los campos del formulario */
        .form-label {
            font-weight: 500;
            color: #495057;
            margin-bottom: 0.5rem;
        }

        .form-control,
        .form-select {
            border-radius: 6px;
            padding: 0.6rem 0.75rem;
            border: 1px solid #ced4da;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        /* Estilos para los botones */
        .btn {
            border-radius: 6px;
            padding: 0.6rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }

        .btn-secondary:hover {
            background-color: #5c636a;
            border-color: #565e64;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Estilos para validación de formularios */
        .is-invalid {
            border-color: #dc3545 !important;
            padding-right: calc(1.5em + 0.75rem);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }

        .is-valid {
            border-color: #198754 !important;
            padding-right: calc(1.5em + 0.75rem);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }

        /* Mejoras para responsive */
        @media (max-width: 768px) {
            .card-body {
                padding: 1.5rem;
            }
            
            .container-fluid {
                padding-left: 15px;
                padding-right: 15px;
            }
        }
    </style>
</body>

</html>