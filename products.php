<?php
require_once './config/config.php';

// Procesar eliminación si se solicita
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        // Verificar si el producto está siendo utilizado en la tabla de precios
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM price_products_supplier WHERE codProducts = (SELECT codProducts FROM products WHERE idproducts = :id)");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $result = $stmt->fetch();
        
        if ($result['count'] > 0) {
            $error = "No se puede eliminar el producto porque está siendo utilizado en la tabla de precios.";
        } else {
            // Eliminar el producto
            $stmt = $conn->prepare("DELETE FROM products WHERE idproducts = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $message = "Producto eliminado correctamente.";
        }
    } catch(PDOException $e) {
        $error = "Error al eliminar el producto: " . $e->getMessage();
    }
}

// Obtener producto para editar
$product_edit = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $id = $_GET['edit'];
    try {
        $stmt = $conn->prepare("SELECT * FROM products WHERE idproducts = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $product_edit = $stmt->fetch();
        
        if (!$product_edit) {
            $error = "Producto no encontrado.";
        }
    } catch(PDOException $e) {
        $error = "Error al obtener el producto: " . $e->getMessage();
    }
}

// Procesar el formulario si se envió
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar si es una actualización o una inserción
    if (isset($_POST['idproducts']) && !empty($_POST['idproducts'])) {
        // Es una actualización
        try {
            $stmt = $conn->prepare("UPDATE products SET 
                                   codProducts = :codProducts,
                                   nomProducts = :nomProducts, 
                                   statusProducts = :statusProducts, 
                                   dir_Imag = :dir_Imag 
                                   WHERE idproducts = :idproducts");
            
            $stmt->bindParam(':idproducts', $_POST['idproducts']);
            $stmt->bindParam(':codProducts', $_POST['codProducts']);
            $stmt->bindParam(':nomProducts', $_POST['nomProducts']);
            $stmt->bindParam(':statusProducts', $_POST['statusProducts']);
            $stmt->bindParam(':dir_Imag', $_POST['dir_Imag']);
            
            $stmt->execute();
            
            $message = "Producto actualizado correctamente.";
            // Redirigir para evitar reenvío del formulario
            header("Location: products.php?message=" . urlencode($message));
            exit;
        } catch(PDOException $e) {
            $error = "Error al actualizar el producto: " . $e->getMessage();
        }
    } else {
        // Es una inserción
        try {
            $stmt = $conn->prepare("INSERT INTO products (codProducts, nomProducts, statusProducts, dir_Imag) 
                                   VALUES (:codProducts, :nomProducts, :statusProducts, :dir_Imag)");
            
            $stmt->bindParam(':codProducts', $_POST['codProducts']);
            $stmt->bindParam(':nomProducts', $_POST['nomProducts']);
            $stmt->bindParam(':statusProducts', $_POST['statusProducts']);
            $stmt->bindParam(':dir_Imag', $_POST['dir_Imag']);
            
            $stmt->execute();
            
            $message = "Producto agregado correctamente.";
        } catch(PDOException $e) {
            $error = "Error al agregar el producto: " . $e->getMessage();
        }
    }
}

// Obtener mensaje de la URL si existe
if (isset($_GET['message'])) {
    $message = $_GET['message'];
}

// Obtener todos los productos
$stmt = $conn->query("SELECT * FROM products ORDER BY idproducts DESC");
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos</title>
    <link rel="stylesheet" href="./css/home.css">
    <script>
        function confirmarEliminar(id, nombre) {
            return confirm('¿Estás seguro de que deseas eliminar el producto "' + nombre + '"?');
        }
    </script>
</head>
<body>
    <div class="container">
        <header>
            <h1>Gestión de Productos</h1>
            <nav>
                <ul>
                    <li><a href="index.php">Inicio</a></li>
                    <li><a href="products.php" class="active">Productos</a></li>
                    <li><a href="suppliers.php">Proveedores</a></li>
                    <li><a href="prices.php">Precios</a></li>
                </ul>
            </nav>
        </header>
        
        <main>
            <section class="form-section">
                <h2><?php echo $product_edit ? 'Editar Producto' : 'Agregar Nuevo Producto'; ?></h2>
                
                <?php if (isset($message)): ?>
                    <div class="alert success"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form action="products.php" method="post">
                    <?php if ($product_edit): ?>
                        <input type="hidden" name="idproducts" value="<?php echo $product_edit['idproducts']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="codProducts">Código del Producto:</label>
                        <input type="text" id="codProducts" name="codProducts" required 
                               value="<?php echo $product_edit ? $product_edit['codProducts'] : ''; ?>"
                               <?php echo $product_edit ? 'readonly' : ''; ?>>
                        <?php if (!$product_edit): ?>
                            <small>Formato recomendado: SECOMA001, SECOMA002, etc.</small>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="nomProducts">Nombre del Producto:</label>
                        <input type="text" id="nomProducts" name="nomProducts" required 
                               value="<?php echo $product_edit ? $product_edit['nomProducts'] : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="statusProducts">Estado:</label>
                        <select id="statusProducts" name="statusProducts" required>
                            <option value="1" <?php echo ($product_edit && $product_edit['statusProducts'] == 1) ? 'selected' : ''; ?>>Activo</option>
                            <option value="0" <?php echo ($product_edit && $product_edit['statusProducts'] == 0) ? 'selected' : ''; ?>>Inactivo</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="dir_Imag">URL de la Imagen:</label>
                        <input type="url" id="dir_Imag" name="dir_Imag" 
                               value="<?php echo $product_edit ? $product_edit['dir_Imag'] : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn">
                            <?php echo $product_edit ? 'Actualizar Producto' : 'Guardar Producto'; ?>
                        </button>
                        <?php if ($product_edit): ?>
                            <a href="products.php" class="btn btn-secondary">Cancelar</a>
                        <?php endif; ?>
                    </div>
                </form>
            </section>
            
            <section class="table-section">
                <h2>Lista de Productos</h2>
                
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Estado</th>
                                <th>Imagen</th>
                                <th>Fecha Creación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['idproducts']); ?></td>
                                    <td><?php echo htmlspecialchars($product['codProducts']); ?></td>
                                    <td><?php echo htmlspecialchars($product['nomProducts']); ?></td>
                                    <td><?php echo $product['statusProducts'] == 1 ? 'Activo' : 'Inactivo'; ?></td>
                                    <td>
                                        <?php if ($product['dir_Imag']): ?>
                                            <img src="<?php echo htmlspecialchars($product['dir_Imag']); ?>" alt="<?php echo htmlspecialchars($product['nomProducts']); ?>" class="thumbnail">
                                        <?php else: ?>
                                            Sin imagen
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($product['created_at']); ?></td>
                                    <td class="actions">
                                        <a href="products.php?edit=<?php echo $product['idproducts']; ?>" class="btn btn-edit">
                                            Editar
                                        </a>
                                        <a href="products.php?delete=<?php echo $product['idproducts']; ?>" 
                                           class="btn btn-delete"
                                           onclick="return confirmarEliminar(<?php echo $product['idproducts']; ?>, '<?php echo htmlspecialchars($product['nomProducts'], ENT_QUOTES); ?>')">
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
