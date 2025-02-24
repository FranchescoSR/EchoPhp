<?php

class Request
{
    private array $data = []; // Declaración de la propiedad $data

    public function __construct()
    {
        // Si la petición es JSON, lo decodificamos
        if ($this->isJson()) {
            $this->data = json_decode(file_get_contents('php://input'), true) ?? [];
        } else {
            $this->data = array_merge($_GET, $_POST);
        }
    }

    /**
     * Obtiene todos los datos de la solicitud (GET y POST)
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * Obtiene un valor específico de la solicitud con un valor por defecto opcional
     */
    public function input(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Verifica si un parámetro está presente en la solicitud
     */
    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }

    /**
     * Obtiene solo ciertos valores de la solicitud
     */
    public function only(array $keys): array
    {
        return array_intersect_key($this->all(), array_flip($keys));
    }

    /**
     * Excluye ciertos valores de la solicitud
     */
    public function except(array $keys): array
    {
        return array_diff_key($this->all(), array_flip($keys));
    }

    /**
     * Obtiene el método HTTP de la solicitud (GET, POST, etc.)
     */
    public function method(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Verifica si la solicitud es de un método específico
     */
    public function isMethod(string $method): bool
    {
        return strtoupper($method) === $this->method();
    }

    /**
     * Obtiene la URL completa de la solicitud
     */
    public function fullUrl(): string
    {
        return $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    /**
     * Obtiene la IP del cliente
     */
    public function ip(): string
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * Maneja archivos subidos en la solicitud
     */
    public function file(string $key): ?array
    {
        return $_FILES[$key] ?? null;
    }

    /**
     * Verifica si un archivo fue subido en la solicitud
     */
    public function hasFile(string $key): bool
    {
        return isset($_FILES[$key]) && $_FILES[$key]['error'] === UPLOAD_ERR_OK;
    }

    /**
     * Obtiene los datos del cuerpo de la solicitud en formato JSON
     */
    public function isJson(): bool
    {
        return isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false;
    }
}
