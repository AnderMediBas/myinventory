<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión de Productos</title>
    <link rel="stylesheet" href="./css/home.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Sistema de Gestión de Productos</h1>
            <nav>
                <ul>
                    <li><a href="index.php" class="active">Inicio</a></li>
                    <li><a href="products.php">Productos</a></li>
                    <li><a href="suppliers.php">Proveedores</a></li>
                    <li><a href="prices.php">Precios</a></li>
                </ul>
            </nav>
        </header>
        
        <main>
            <div class="dashboard">
                <div class="card">
                    <h2>Productos</h2>
                    <p>Gestiona tu catálogo de productos</p>
                    <a href="products.php" class="btn">Administrar</a>
                </div>
                
                <div class="card">
                    <h2>Proveedores</h2>
                    <p>Administra tus proveedores</p>
                    <a href="suppliers.php" class="btn">Administrar</a>
                </div>
                
                <div class="card">
                    <h2>Precios</h2>
                    <p>Gestiona los precios de productos por proveedor</p>
                    <a href="prices.php" class="btn">Administrar</a>
                </div>
            </div>
        </main>
        
        <footer>
            <p>&copy; <?php echo date('Y'); ?> Sistema de Gestión de Productos</p>
        </footer>
    </div>
</body>
</html>
