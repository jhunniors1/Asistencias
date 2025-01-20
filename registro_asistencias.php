<?php
// Conexión a la base de datos
include 'db.php';

// Establecer la zona horaria a Lima, Perú (UTC -5)
date_default_timezone_set('America/Lima');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $empleado_nombre = $_POST['empleado_nombre'];
    $tipo = $_POST['tipo']; // Entrada o Salida
    $fecha = date('Y-m-d');
    $hora = date('H:i');

    // Validar campos
    if (!empty($empleado_nombre) && !empty($tipo)) {
        if ($tipo === 'Entrada') {
            // Insertar nuevo registro con hora de entrada (hora_salida será NULL por defecto)
            $sql = "INSERT INTO asistencias (empleado_nombre, fecha, hora_entrada, hora_salida) VALUES (?, ?, ?, NULL)";
            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $stmt->bind_param("sss", $empleado_nombre, $fecha, $hora);
                if ($stmt->execute()) {
                    $mensaje = "<span class='success'>Registro de entrada realizado exitosamente.</span>";
                } else {
                    $mensaje = "<span class='error'>Error: " . $stmt->error . "</span>";
                }
                $stmt->close();
            } else {
                $mensaje = "<span class='error'>Error al preparar la consulta.</span>";
            }
        } elseif ($tipo === 'Salida') {
            // Actualizar la hora de salida del registro más reciente del empleado con hora_salida NULL
            $sql = "UPDATE asistencias 
                    SET hora_salida = ? 
                    WHERE empleado_nombre = ? AND fecha = ? AND hora_salida IS NULL 
                    ORDER BY hora_entrada DESC 
                    LIMIT 1";
            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $stmt->bind_param("sss", $hora, $empleado_nombre, $fecha);
                if ($stmt->execute() && $stmt->affected_rows > 0) {
                    $mensaje = "<span class='success'>Registro de salida realizado exitosamente.</span>";
                } else {
                    $mensaje = "<span class='error'>No se encontró una entrada pendiente para este empleado.</span>";
                }
                $stmt->close();
            } else {
                $mensaje = "<span class='error'>Error al preparar la consulta.</span>";
            }
        }
    } else {
        $mensaje = "<span class='error'>Por favor, seleccione un empleado y un tipo de registro.</span>";
    }
}

// Obtener lista de empleados
$sql_empleados = "SELECT nombre FROM empleados ORDER BY nombre";
$result_empleados = $conn->query($sql_empleados);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Asistencia</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Estilos globales */
        body {
            font-family: 'Roboto', sans-serif;
            background: #f5f7fa;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 500px;
            text-align: center;
            transition: all 0.3s ease;
        }

        h1 {
            font-size: 2.4em;
            color: #2c3e50;
            margin-bottom: 30px;
            font-weight: 700;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        select, button {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
            background-color: #f8f8f8;
            color: #333;
            outline: none;
            transition: all 0.3s ease;
        }

        select:focus, button:focus {
            border-color: #3498db;
            box-shadow: 0 0 5px rgba(52, 152, 219, 0.5);
        }

        select {
            background-color: #fafafa;
        }

        button {
            background-color: #3498db;
            color: white;
            font-size: 1.2em;
            cursor: pointer;
            transition: background-color 0.3s;
            border-radius: 30px;
        }

        button:hover {
            background-color: #2980b9;
        }

        /* Mensajes de éxito y error */
        .mensaje {
            margin-top: 20px;
            font-size: 1.1em;
            font-weight: bold;
        }

        .mensaje.success {
            color: #2ecc71;
        }

        .mensaje.error {
            color: #e74c3c;
        }

        .link-buttons {
            margin-top: 20px;
        }

        .link-buttons a {
            color: #3498db;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s;
        }

        .link-buttons a:hover {
            color: #2980b9;
        }

        .back-button {
            background-color: #e74c3c;
            color: white;
            padding: 10px 20px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
            margin-top: 15px;
            transition: background-color 0.3s;
        }

        .back-button:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-calendar-check"></i> Registrar Asistencia</h1>
        <form method="post" action="">
            <select name="empleado_nombre" required>
                <option value="">Seleccione un empleado</option>
                <?php while ($row = $result_empleados->fetch_assoc()): ?>
                    <option value="<?php echo $row['nombre']; ?>"><?php echo $row['nombre']; ?></option>
                <?php endwhile; ?>
            </select>
            <select name="tipo" required>
                <option value="">Seleccione tipo</option>
                <option value="Entrada">Entrada</option>
                <option value="Salida">Salida</option>
            </select>
            <button type="submit"><i class="fas fa-save"></i> Registrar</button>
        </form>
        <?php if (isset($mensaje)): ?>
            <div class="mensaje <?php echo strpos($mensaje, 'Error') ? 'error' : 'success'; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>
</body>
</html>




