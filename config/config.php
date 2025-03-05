<?php
// Configuración de la base de datos
$host = 'localhost';
$dbname = 'productos';
$username = 'root'; // Cambia esto por tu usuario de MySQL
$password = '29141105'; // Cambia esto por tu contraseña de MySQL

try {
    // Crear conexión PDO
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    // Configurar el modo de error de PDO para que lance excepciones
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Configurar para que devuelva arrays asociativos
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    // En caso de error, mostrar mensaje y terminar script
    die("Error de conexión: " . $e->getMessage());
}

// Función para generar códigos automáticos
function generateCode($prefix, $conn, $table, $column, $length = 5) {
    // Obtener el último código
    $stmt = $conn->prepare("SELECT MAX($column) as max_code FROM $table WHERE $column LIKE :prefix");
    $stmt->execute(['prefix' => $prefix . '%']);
    $result = $stmt->fetch();
    
    if ($result['max_code']) {
        // Extraer el número del código
        $num = intval(substr($result['max_code'], strlen($prefix)));
        $num++;
    } else {
        $num = 1;
    }
    
    // Formatear el nuevo código
    return $prefix . str_pad($num, $length, '0', STR_PAD_LEFT);
}
?>

