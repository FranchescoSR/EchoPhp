<?php
//header('Content-Type: application/json');

require_once 'core/Request.php'; // Asegurar que la clase Request está disponible

// Cargar las rutas
$routes = require_once 'router/web.php';

// Obtener la URL y eliminar `/echophp/` si existe
$requestUri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
if (strpos($requestUri, 'echophp/') === 0) {
    $requestUri = substr($requestUri, 4);
}

// Verificar si la URL es vacía (es decir, la raíz de la API, http://localhost/echophp/)
if ($requestUri === 'echophp') {
    // Redirigir a la vista home.php
    require_once './views/home.php';
    exit; // Detener el flujo
}

// Obtener el método HTTP (GET, POST, etc.)
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Recorrer las rutas definidas en `web.php`
foreach ($routes as $pattern => $route) {
    // Convertir el patrón a una expresión regular para las rutas dinámicas
    $pattern = '#^' . preg_replace('/\{(.*?)\}/', '(?P<$1>[^/]+)', $pattern) . '$#';

    if (preg_match($pattern, $requestUri, $matches)) {
        list($controller, $action) = explode('@', $route);

        $controllerPath = "controllers/$controller.php";

        // Verificar si el controlador existe antes de incluirlo
        if (!file_exists($controllerPath)) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => "Controlador '$controller' no encontrado"]);
            exit;
        }

        require_once $controllerPath;

        // Verificar si la clase del controlador existe
        if (!class_exists($controller)) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => "Clase '$controller' no definida en el archivo"]);
            exit;
        }

        $controllerInstance = new $controller();
        array_shift($matches); // Eliminar el primer valor (URL completa)

        // Crear instancia de Request para inyectarla en los controladores
        $request = new Request();

        // Verificar si el método del controlador acepta Request como parámetro
        $reflection = new ReflectionMethod($controllerInstance, $action);
        $parameters = $reflection->getParameters();

        if (!empty($parameters) && $parameters[0]->getType() && $parameters[0]->getType()->getName() === 'Request') {
            array_unshift($matches, $request);
        }

        // Llamar al método del controlador con los parámetros extraídos
        call_user_func_array([$controllerInstance, $action], array_values($matches));
        exit;
    }
}

// Si ninguna ruta coincide, responder con 404
http_response_code(404);
echo json_encode(['status' => 'error', 'message' => 'Ruta no encontrada']);
exit;
