<?php
require_once './config/config.php';

// Procesar eliminación si se solicita
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        // Verificar si el proveedor está siendo utilizado en la tabla de precios
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM price_products_supplier WHERE codSupplier = (SELECT codSupplier FROM supplier WHERE idsupplier = :id)");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $result = $stmt->fetch();
        
        if ($result['count'] > 0) {
            $error = "No se puede eliminar el proveedor porque está siendo utilizado en la tabla de precios.";
        } else {
            // Eliminar el proveedor
            $stmt = $conn->prepare("DELETE FROM supplier WHERE idsupplier = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $message = "Proveedor eliminado correctamente.";
        }
    } catch(PDOException $e) {
        $error = "Error al eliminar el proveedor: " . $e->getMessage();
    }
}

// Obtener proveedor para editar
$supplier_edit = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $id = $_GET['edit'];
    try {
        $stmt = $conn->prepare("SELECT * FROM supplier WHERE idsupplier = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $supplier_edit = $stmt->fetch();
        
        if (!$supplier_edit) {
            $error = "Proveedor no encontrado.";
        }
    } catch(PDOException $e) {
        $error = "Error al obtener el proveedor: " . $e->getMessage();
    }
}

// Procesar el formulario si se envió
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar si es una actualización o una inserción
    if (isset($_POST['idsupplier']) && !empty($_POST['idsupplier'])) {
        // Es una actualización
        try {
            $stmt = $conn->prepare("UPDATE supplier SET 
                                   codSupplier = :codSupplier,
                                   nomSupplier = :nomSupplier, 
                                   directionSupplier = :directionSupplier, 
                                   tienda = :tienda,
                                   telSupplier = :telSupplier,
                                   telSupplier2 = :telSupplier2,
                                   telSupplier3 = :telSupplier3,
                                   emailSupplier = :emailSupplier,
                                   webSiteSupplier = :webSiteSupplier,
                                   tipVenta = :tipVenta,
                                   envios = :envios
                                   WHERE idsupplier = :idsupplier");
            
            $stmt->bindParam(':idsupplier', $_POST['idsupplier']);
            $stmt->bindParam(':codSupplier', $_POST['codSupplier']);
            $stmt->bindParam(':nomSupplier', $_POST['nomSupplier']);
            $stmt->bindParam(':directionSupplier', $_POST['directionSupplier']);
            $stmt->bindParam(':tienda', $_POST['tienda']);
            $stmt->bindParam(':telSupplier', $_POST['telSupplier']);
            $stmt->bindParam(':telSupplier2', $_POST['telSupplier2']);
            $stmt->bindParam(':telSupplier3', $_POST['telSupplier3']);
            $stmt->bindParam(':emailSupplier', $_POST['emailSupplier']);
            $stmt->bindParam(':webSiteSupplier', $_POST['webSiteSupplier']);
            $stmt->bindParam(':tipVenta', $_POST['tipVenta']);
            $stmt->bindParam(':envios', $_POST['envios']);
            
            $stmt->execute();
            
            $message = "Proveedor actualizado correctamente.";
            // Redirigir para evitar reenvío del formulario
            header("Location: suppliers.php?message=" . urlencode($message));
            exit;
        } catch(PDOException $e) {
            $error = "Error al actualizar el proveedor: " . $e->getMessage();
        }
    } else {
        // Es una inserción
        try {
            $stmt = $conn->prepare("INSERT INTO supplier (codSupplier, nomSupplier, directionSupplier, tienda, 
                                    telSupplier, telSupplier2, telSupplier3, emailSupplier, webSiteSupplier, 
                                    tipVenta, envios) 
                                   VALUES (:codSupplier, :nomSupplier, :directionSupplier, :tienda, 
                                    :telSupplier, :telSupplier2, :telSupplier3, :emailSupplier, :webSiteSupplier, 
                                    :tipVenta, :envios)");
            
            $stmt->bindParam(':codSupplier', $_POST['codSupplier']);
            $stmt->bindParam(':nomSupplier', $_POST['nomSupplier']);
            $stmt->bindParam(':directionSupplier', $_POST['directionSupplier']);
            $stmt->bindParam(':tienda', $_POST['tienda']);
            $stmt->bindParam(':telSupplier', $_POST['telSupplier']);
            $stmt->bindParam(':telSupplier2', $_POST['telSupplier2']);
            $stmt->bindParam(':telSupplier3', $_POST['telSupplier3']);
            $stmt->bindParam(':emailSupplier', $_POST['emailSupplier']);
            $stmt->bindParam(':webSiteSupplier', $_POST['webSiteSupplier']);
            $stmt->bindParam(':tipVenta', $_POST['tipVenta']);
            $stmt->bindParam(':envios', $_POST['envios']);
            
            $stmt->execute();
            
            $message = "Proveedor agregado correctamente.";
        } catch(PDOException $e) {
            $error = "Error al agregar el proveedor: " . $e->getMessage();
        }
    }
}

// Obtener mensaje de la URL si existe
if (isset($_GET['message'])) {
    $message = $_GET['message'];
}

// Obtener todos los proveedores
$stmt = $conn->query("SELECT * FROM supplier ORDER BY idsupplier DESC");
$suppliers = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Proveedores</title>
    <link rel="stylesheet" href="./css/home.css">
    <script>
        function confirmarEliminar(id, nombre) {
            return confirm('¿Estás seguro de que deseas eliminar el proveedor "' + nombre + '"?');
        }
    </script>
</head>
<body>
    <div class="container">
        <header>
            <h1>Gestión de Proveedores</h1>
            <nav>
                <ul>
                    <li><a href="index.php">Inicio</a></li>
                    <li><a href="products.php">Productos</a></li>
                    <li><a href="suppliers.php" class="active">Proveedores</a></li>
                    <li><a href="prices.php">Precios</a></li>
                </ul>
            </nav>
        </header>
        
        <main>
            <section class="form-section">
                <h2><?php echo $supplier_edit ? 'Editar Proveedor' : 'Agregar Nuevo Proveedor'; ?></h2>
                
                <?php if (isset($message)): ?>
                    <div class="alert success"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form action="suppliers.php" method="post">
                    <?php if ($supplier_edit): ?>
                        <input type="hidden" name="idsupplier" value="<?php echo $supplier_edit['idsupplier']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="codSupplier">Código del Proveedor:</label>
                        <input type="text" id="codSupplier" name="codSupplier" required 
                               value="<?php echo $supplier_edit ? $supplier_edit['codSupplier'] : ''; ?>"
                               <?php echo $supplier_edit ? 'readonly' : ''; ?>>
                        <?php if (!$supplier_edit): ?>
                            <small>Formato recomendado: SUVARIE001, SUPROBE001, etc.</small>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="nomSupplier">Nombre del Proveedor:</label>
                        <input type="text" id="nomSupplier" name="nomSupplier" required 
                               value="<?php echo $supplier_edit ? $supplier_edit['nomSupplier'] : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="directionSupplier">Dirección:</label>
                        <input type="text" id="directionSupplier" name="directionSupplier" 
                               value="<?php echo $supplier_edit ? $supplier_edit['directionSupplier'] : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="tienda">Tienda:</label>
                        <input type="text" id="tienda" name="tienda" 
                               value="<?php echo $supplier_edit ? $supplier_edit['tienda'] : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="telSupplier">Teléfono Principal:</label>
                        <input type="number" id="telSupplier" name="telSupplier" required 
                               value="<?php echo $supplier_edit ? $supplier_edit['telSupplier'] : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="telSupplier2">Teléfono Secundario:</label>
                        <input type="number" id="telSupplier2" name="telSupplier2" 
                               value="<?php echo $supplier_edit ? $supplier_edit['telSupplier2'] : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="telSupplier3">Teléfono Adicional:</label>
                        <input type="number" id="telSupplier3" name="telSupplier3" 
                               value="<?php echo $supplier_edit ? $supplier_edit['telSupplier3'] : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="emailSupplier">Email:</label>
                        <input type="email" id="emailSupplier" name="emailSupplier" 
                               value="<?php echo $supplier_edit ? $supplier_edit['emailSupplier'] : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="webSiteSupplier">Sitio Web:</label>
                        <input type="url" id="webSiteSupplier" name="webSiteSupplier" 
                               value="<?php echo $supplier_edit ? $supplier_edit['webSiteSupplier'] : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="tipVenta">Tipo de Venta:</label>
                        <input type="text" id="tipVenta" name="tipVenta" 
                               value="<?php echo $supplier_edit ? $supplier_edit['tipVenta'] : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="envios">Envíos:</label>
                        <select id="envios" name="envios">
                            <option value="" <?php echo ($supplier_edit && $supplier_edit['envios'] == '') ? 'selected' : ''; ?>>Seleccionar</option>
                            <option value="SI" <?php echo ($supplier_edit && $supplier_edit['envios'] == 'SI') ? 'selected' : ''; ?>>SI</option>
                            <option value="NO" <?php echo ($supplier_edit && $supplier_edit['envios'] == 'NO') ? 'selected' : ''; ?>>NO</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn">
                            <?php echo $supplier_edit ? 'Actualizar Proveedor' : 'Guardar Proveedor'; ?>
                        </button>
                        <?php if ($supplier_edit): ?>
                            <a href="suppliers.php" class="btn btn-secondary">Cancelar</a>
                        <?php endif; ?>
                    </div>
                </form>
            </section>
            
            <section class="table-section">
                <h2>Lista de Proveedores</h2>
                
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Dirección</th>
                                <th>Teléfono</th>
                                <th>Email</th>
                                <th>Tipo Venta</th>
                                <th>Envíos</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($suppliers as $supplier): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($supplier['idsupplier']); ?></td>
                                    <td><?php echo htmlspecialchars($supplier['codSupplier']); ?></td>
                                    <td><?php echo htmlspecialchars($supplier['nomSupplier']); ?></td>
                                    <td><?php echo htmlspecialchars($supplier['directionSupplier']); ?></td>
                                    <td><?php echo htmlspecialchars($supplier['telSupplier']); ?></td>
                                    <td><?php echo htmlspecialchars($supplier['emailSupplier']); ?></td>
                                    <td><?php echo htmlspecialchars($supplier['tipVenta']); ?></td>
                                    <td><?php echo htmlspecialchars($supplier['envios']); ?></td>
                                    <td class="actions">
                                        <a href="suppliers.php?edit=<?php echo $supplier['idsupplier']; ?>" class="btn btn-edit">
                                            Editar
                                        </a>
                                        <a href="suppliers.php?delete=<?php echo $supplier['idsupplier']; ?>" 
                                           class="btn btn-delete"
                                           onclick="return confirmarEliminar(<?php echo $supplier['idsupplier']; ?>, '<?php echo htmlspecialchars($supplier['nomSupplier'], ENT_QUOTES); ?>')">
                                            Eliminar
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
        
        <footer>
            <p>&copy; <?php echo date('Y'); ?> Sistema de Gestión de Productos</p>
        </footer>
    </div>
</body>
</html>

