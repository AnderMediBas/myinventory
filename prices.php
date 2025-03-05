<?php
require_once './config/config.php';

// Procesar eliminación si se solicita
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        // Eliminar el precio
        $stmt = $conn->prepare("DELETE FROM price_products_supplier WHERE idprice_products_supplier = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $message = "Precio eliminado correctamente.";
    } catch(PDOException $e) {
        $error = "Error al eliminar el precio: " . $e->getMessage();
    }
}

// Obtener precio para editar
$price_edit = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $id = $_GET['edit'];
    try {
        $stmt = $conn->prepare("SELECT * FROM price_products_supplier WHERE idprice_products_supplier = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $price_edit = $stmt->fetch();
        
        if (!$price_edit) {
            $error = "Precio no encontrado.";
        }
    } catch(PDOException $e) {
        $error = "Error al obtener el precio: " . $e->getMessage();
    }
}

// Procesar el formulario si se envió
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar si es una actualización o una inserción
    if (isset($_POST['idprice_products_supplier']) && !empty($_POST['idprice_products_supplier'])) {
        // Es una actualización
        try {
            $stmt = $conn->prepare("UPDATE price_products_supplier SET 
                                   priceProducts = :priceProducts 
                                   WHERE idprice_products_supplier = :idprice_products_supplier");
            
            $stmt->bindParam(':idprice_products_supplier', $_POST['idprice_products_supplier']);
            $stmt->bindParam(':priceProducts', $_POST['priceProducts']);
            
            $stmt->execute();
            
            $message = "Precio actualizado correctamente.";
            // Redirigir para evitar reenvío del formulario
            header("Location: prices.php?message=" . urlencode($message));
            exit;
        } catch(PDOException $e) {
            $error = "Error al actualizar el precio: " . $e->getMessage();
        }
    } else {
        // Es una inserción
        try {
            // Preparar la consulta - El código se genera automáticamente por el trigger
            $stmt = $conn->prepare("INSERT INTO price_products_supplier (codProducts, codSupplier, priceProducts) 
                                   VALUES (:codProducts, :codSupplier, :priceProducts)");
            
            // Vincular parámetros
            $stmt->bindParam(':codProducts', $_POST['codProducts']);
            $stmt->bindParam(':codSupplier',  $_POST['codProducts']);
            $stmt->bindParam(':codSupplier', $_POST['codSupplier']);
            $stmt->bindParam(':priceProducts', $_POST['priceProducts']);
            
            // Ejecutar la consulta
            $stmt->execute();
            
            $message = "Precio agregado correctamente.";
        } catch(PDOException $e) {
            $error = "Error al agregar el precio: " . $e->getMessage();
        }
    }
}

// Obtener mensaje de la URL si existe
if (isset($_GET['message'])) {
    $message = $_GET['message'];
}

// Obtener todos los productos
$stmt = $conn->query("SELECT codProducts, nomProducts FROM products WHERE statusProducts = 1 ORDER BY nomProducts");
$products = $stmt->fetchAll();

// Obtener todos los proveedores
$stmt = $conn->query("SELECT codSupplier, nomSupplier FROM supplier ORDER BY nomSupplier");
$suppliers = $stmt->fetchAll();

// Obtener todos los precios con información de productos y proveedores
$stmt = $conn->query("SELECT p.*, pr.nomProducts, s.nomSupplier 
                     FROM price_products_supplier p
                     JOIN products pr ON p.codProducts = pr.codProducts
                     JOIN supplier s ON p.codSupplier = s.codSupplier
                     ORDER BY p.idprice_products_supplier DESC");
$prices = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Precios</title>
    <link rel="stylesheet" href="./css/home.css">
    <script>
        function confirmarEliminar(id, producto, proveedor) {
            return confirm('¿Estás seguro de que deseas eliminar el precio del producto "' + producto + '" del proveedor "' + proveedor + '"?');
        }
    </script>
</head>
<body>
    <div class="container">
        <header>
            <h1>Gestión de Precios</h1>
            <nav>
                <ul>
                    <li><a href="index.php">Inicio</a></li>
                    <li><a href="products.php">Productos</a></li>
                    <li><a href="suppliers.php">Proveedores</a></li>
                    <li><a href="prices.php" class="active">Precios</a></li>
                </ul>
            </nav>
        </header>
        
        <main>
            <section class="form-section">
                <h2><?php echo $price_edit ? 'Editar Precio' : 'Agregar Nuevo Precio'; ?></h2>
                
                <?php if (isset($message)): ?>
                    <div class="alert success"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form action="prices.php" method="post">
                    <?php if ($price_edit): ?>
                        <input type="hidden" name="idprice_products_supplier" value="<?php echo $price_edit['idprice_products_supplier']; ?>">
                        
                        <div class="form-group">
                            <label>Producto:</label>
                            <input type="text" value="<?php 
                                $stmt = $conn->prepare("SELECT nomProducts FROM products WHERE codProducts = :codProducts");
                                $stmt->bindParam(':codProducts', $price_edit['codProducts']);
                                $stmt->execute();
                                $product = $stmt->fetch();
                                echo htmlspecialchars($product['nomProducts']); 
                            ?>" readonly class="readonly">
                            <input type="hidden" name="codProducts" value="<?php echo $price_edit['codProducts']; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Proveedor:</label>
                            <input type="text" value="<?php 
                                $stmt = $conn->prepare("SELECT nomSupplier FROM supplier WHERE codSupplier = :codSupplier");
                                $stmt->bindParam(':codSupplier', $price_edit['codSupplier']);
                                $stmt->execute();
                                $supplier = $stmt->fetch();
                                echo htmlspecialchars($supplier['nomSupplier']); 
                            ?>" readonly class="readonly">
                            <input type="hidden" name="codSupplier" value="<?php echo $price_edit['codSupplier']; ?>">
                        </div>
                    <?php else: ?>
                        <div class="form-group">
                            <label for="codProducts">Producto:</label>
                            <select id="codProducts" name="codProducts" required>
                                <option value="">Seleccionar Producto</option>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?php echo htmlspecialchars($product['codProducts']); ?>">
                                        <?php echo htmlspecialchars($product['nomProducts']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="codSupplier">Proveedor:</label>
                            <select id="codSupplier" name="codSupplier" required>
                                <option value="">Seleccionar Proveedor</option>
                                <?php foreach ($suppliers as $supplier): ?>
                                    <option value="<?php echo htmlspecialchars($supplier['codSupplier']); ?>">
                                        <?php echo htmlspecialchars($supplier['nomSupplier']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="priceProducts">Precio:</label>
                        <input type="number" id="priceProducts" name="priceProducts" step="0.01" required
                               value="<?php echo $price_edit ? $price_edit['priceProducts'] : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn">
                            <?php echo $price_edit ? 'Actualizar Precio' : 'Guardar Precio'; ?>
                        </button>
                        <?php if ($price_edit): ?>
                            <a href="prices.php" class="btn btn-secondary">Cancelar</a>
                        <?php endif; ?>
                    </div>
                </form>
            </section>
            
            <section class="table-section">
                <h2>Lista de Precios</h2>
                
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Código</th>
                                <th>Producto</th>
                                <th>Proveedor</th>
                                <th>Precio</th>
                                <th>Fecha Creación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($prices as $price): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($price['idprice_products_supplier']); ?></td>
                                    <td><?php echo htmlspecialchars($price['codPriceProductsSupplier']); ?></td>
                                    <td><?php echo htmlspecialchars($price['nomProducts']); ?></td>
                                    <td><?php echo htmlspecialchars($price['nomSupplier']); ?></td>
                                    <td><?php echo htmlspecialchars($price['priceProducts']); ?></td>
                                    <td><?php echo htmlspecialchars($price['created_at']); ?></td>
                                    <td class="actions">
                                        <a href="prices.php?edit=<?php echo $price['idprice_products_supplier']; ?>" class="btn btn-edit">
                                            Editar
                                        </a>
                                        <a href="prices.php?delete=<?php echo $price['idprice_products_supplier']; ?>" 
                                           class="btn btn-delete"
                                           onclick="return confirmarEliminar(<?php echo $price['idprice_products_supplier']; ?>, '<?php echo htmlspecialchars($price['nomProducts'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($price['nomSupplier'], ENT_QUOTES); ?>')">
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

<?php
require_once './config/config.php';

// Procesar eliminación si se solicita
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        // Eliminar el precio
        $stmt = $conn->prepare("DELETE FROM price_products_supplier WHERE idprice_products_supplier = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $message = "Precio eliminado correctamente.";
    } catch(PDOException $e) {
        $error = "Error al eliminar el precio: " . $e->getMessage();
    }
}

// Obtener precio para editar
$price_edit = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $id = $_GET['edit'];
    try {
        $stmt = $conn->prepare("SELECT * FROM price_products_supplier WHERE idprice_products_supplier = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $price_edit = $stmt->fetch();
        
        if (!$price_edit) {
            $error = "Precio no encontrado.";
        }
    } catch(PDOException $e) {
        $error = "Error al obtener el precio: " . $e->getMessage();
    }
}

// Procesar el formulario si se envió
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar si es una actualización o una inserción
    if (isset($_POST['idprice_products_supplier']) && !empty($_POST['idprice_products_supplier'])) {
        // Es una actualización
        try {
            $stmt = $conn->prepare("UPDATE price_products_supplier SET 
                                   priceProducts = :priceProducts 
                                   WHERE idprice_products_supplier = :idprice_products_supplier");
            
            $stmt->bindParam(':idprice_products_supplier', $_POST['idprice_products_supplier']);
            $stmt->bindParam(':priceProducts', $_POST['priceProducts']);
            
            $stmt->execute();
            
            $message = "Precio actualizado correctamente.";
            // Redirigir para evitar reenvío del formulario
            header("Location: prices.php?message=" . urlencode($message));
            exit;
        } catch(PDOException $e) {
            $error = "Error al actualizar el precio: " . $e->getMessage();
        }
    } else {
        // Es una inserción
        try {
            // Preparar la consulta - El código se genera automáticamente por el trigger
            $stmt = $conn->prepare("INSERT INTO price_products_supplier (codProducts, codSupplier, priceProducts) 
                                   VALUES (:codProducts, :codSupplier, :priceProducts)");
            
            // Vincular parámetros
            $stmt->bindParam(':codProducts', $_POST['codProducts']);
            $stmt->bindParam(':codSupplier',  $_POST['codProducts']);
            $stmt->bindParam(':codSupplier', $_POST['codSupplier']);
            $stmt->bindParam(':priceProducts', $_POST['priceProducts']);
            
            // Ejecutar la consulta
            $stmt->execute();
            
            $message = "Precio agregado correctamente.";
        } catch(PDOException $e) {
            $error = "Error al agregar el precio: " . $e->getMessage();
        }
    }
}

// Obtener mensaje de la URL si existe
if (isset($_GET['message'])) {
    $message = $_GET['message'];
}

// Obtener todos los productos
$stmt = $conn->query("SELECT codProducts, nomProducts FROM products WHERE statusProducts = 1 ORDER BY nomProducts");
$products = $stmt->fetchAll();

// Obtener todos los proveedores
$stmt = $conn->query("SELECT codSupplier, nomSupplier FROM supplier ORDER BY nomSupplier");
$suppliers = $stmt->fetchAll();

// Obtener todos los precios con información de productos y proveedores
$stmt = $conn->query("SELECT p.*, pr.nomProducts, s.nomSupplier 
                     FROM price_products_supplier p
                     JOIN products pr ON p.codProducts = pr.codProducts
                     JOIN supplier s ON p.codSupplier = s.codSupplier
                     ORDER BY p.idprice_products_supplier DESC");
$prices = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Precios</title>
    <link rel="stylesheet" href="./css/home.css">
    <script>
        function confirmarEliminar(id, producto, proveedor) {
            return confirm('¿Estás seguro de que deseas eliminar el precio del producto "' + producto + '" del proveedor "' + proveedor + '"?');
        }
    </script>
</head>
<body>
    <div class="container">
        <header>
            <h1>Gestión de Precios</h1>
            <nav>
                <ul>
                    <li><a href="index.php">Inicio</a></li>
                    <li><a href="products.php">Productos</a></li>
                    <li><a href="suppliers.php">Proveedores</a></li>
                    <li><a href="prices.php" class="active">Precios</a></li>
                </ul>
            </nav>
        </header>
        
        <main>
            <section class="form-section">
                <h2><?php echo $price_edit ? 'Editar Precio' : 'Agregar Nuevo Precio'; ?></h2>
                
                <?php if (isset($message)): ?>
                    <div class="alert success"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form action="prices.php" method="post">
                    <?php if ($price_edit): ?>
                        <input type="hidden" name="idprice_products_supplier" value="<?php echo $price_edit['idprice_products_supplier']; ?>">
                        
                        <div class="form-group">
                            <label>Producto:</label>
                            <input type="text" value="<?php 
                                $stmt = $conn->prepare("SELECT nomProducts FROM products WHERE codProducts = :codProducts");
                                $stmt->bindParam(':codProducts', $price_edit['codProducts']);
                                $stmt->execute();
                                $product = $stmt->fetch();
                                echo htmlspecialchars($product['nomProducts']); 
                            ?>" readonly class="readonly">
                            <input type="hidden" name="codProducts" value="<?php echo $price_edit['codProducts']; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Proveedor:</label>
                            <input type="text" value="<?php 
                                $stmt = $conn->prepare("SELECT nomSupplier FROM supplier WHERE codSupplier = :codSupplier");
                                $stmt->bindParam(':codSupplier', $price_edit['codSupplier']);
                                $stmt->execute();
                                $supplier = $stmt->fetch();
                                echo htmlspecialchars($supplier['nomSupplier']); 
                            ?>" readonly class="readonly">
                            <input type="hidden" name="codSupplier" value="<?php echo $price_edit['codSupplier']; ?>">
                        </div>
                    <?php else: ?>
                        <div class="form-group">
                            <label for="codProducts">Producto:</label>
                            <select id="codProducts" name="codProducts" required>
                                <option value="">Seleccionar Producto</option>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?php echo htmlspecialchars($product['codProducts']); ?>">
                                        <?php echo htmlspecialchars($product['nomProducts']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="codSupplier">Proveedor:</label>
                            <select id="codSupplier" name="codSupplier" required>
                                <option value="">Seleccionar Proveedor</option>
                                <?php foreach ($suppliers as $supplier): ?>
                                    <option value="<?php echo htmlspecialchars($supplier['codSupplier']); ?>">
                                        <?php echo htmlspecialchars($supplier['nomSupplier']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="priceProducts">Precio:</label>
                        <input type="number" id="priceProducts" name="priceProducts" step="0.01" required
                               value="<?php echo $price_edit ? $price_edit['priceProducts'] : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn">
                            <?php echo $price_edit ? 'Actualizar Precio' : 'Guardar Precio'; ?>
                        </button>
                        <?php if ($price_edit): ?>
                            <a href="prices.php" class="btn btn-secondary">Cancelar</a>
                        <?php endif; ?>
                    </div>
                </form>
            </section>
            
            <section class="table-section">
                <h2>Lista de Precios</h2>
                
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Código</th>
                                <th>Producto</th>
                                <th>Proveedor</th>
                                <th>Precio</th>
                                <th>Fecha Creación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($prices as $price): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($price['idprice_products_supplier']); ?></td>
                                    <td><?php echo htmlspecialchars($price['codPriceProductsSupplier']); ?></td>
                                    <td><?php echo htmlspecialchars($price['nomProducts']); ?></td>
                                    <td><?php echo htmlspecialchars($price['nomSupplier']); ?></td>
                                    <td><?php echo htmlspecialchars($price['priceProducts']); ?></td>
                                    <td><?php echo htmlspecialchars($price['created_at']); ?></td>
                                    <td class="actions">
                                        <a href="prices.php?edit=<?php echo $price['idprice_products_supplier']; ?>" class="btn btn-edit">
                                            Editar
                                        </a>
                                        <a href="prices.php?delete=<?php echo $price['idprice_products_supplier']; ?>" 
                                           class="btn btn-delete"
                                           onclick="return confirmarEliminar(<?php echo $price['idprice_products_supplier']; ?>, '<?php echo htmlspecialchars($price['nomProducts'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($price['nomSupplier'], ENT_QUOTES); ?>')">
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

