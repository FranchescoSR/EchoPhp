<?php
require_once __DIR__ . '/../core/DB.php';
require_once __DIR__ . '/../core/Request.php';

class AuthController {
    
    private $secretKey = 'mi_clave_secreta'; // Clave secreta para firmar el token

    // Función para autenticar usuario y generar un token
    public function autentificar_usuario(Request $request) {
        // Obtener los datos del body en formato JSON
        $data = $request->all();
        
        $usuario = $data['usuario'] ?? '';
        $contraseña = $data['contraseña'] ?? '';
    
        // Verifica que los datos no estén vacíos
        if (empty($usuario) || empty($contraseña)) {
            echo json_encode([
                'success' => false,
                'message' => 'Usuario y contraseña son requeridos.'
            ]);
            return;
        }
    
        // Buscar el usuario en la base de datos
        $usuario_encontrado = DB::table('usuarios')->where('Usuario', $usuario)->first();
    
        if ($usuario_encontrado) {
            // Verificar que la contraseña coincida (usando hash en la base de datos)
            if ($contraseña == $usuario_encontrado['Contraseña']) {
                // Generar un token simple usando un hash con la clave secreta
                $token = $this->generateToken($usuario_encontrado['Id_Usuario'], $usuario_encontrado['Usuario']);
                    
                // Actualizar el token en la base de datos para este usuario_encontrado
                $actualizar_token = DB::table('usuarios')
                    ->where('Id_Usuario', $usuario_encontrado['Id_Usuario'])
                    ->update(['Token' => $token]);

                if ($actualizar_token) {
                    // Retornar el token en formato JSON
                    echo json_encode([
                        'success' => true,
                        'message' => 'Autenticación exitosa',
                        'token' => $token
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Error al actualizar el token'
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Contraseña incorrecta'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ]);
        }

    }    

    // Función para generar un token simple
    private function generateToken($userId, $username) {
        // Concatenar los datos del usuario con la clave secreta
        $data = $userId . $username . $this->secretKey . time();  // Incluye el timestamp para hacerlo único
        return hash('sha256', $data);  // Generar un hash (el "token")
    }

    // Función para verificar el token
    public function verifyToken($token, $userId, $username) {
        // Volver a generar el token con los mismos datos y comparar
        $generatedToken = $this->generateToken($userId, $username);

        if ($generatedToken === $token) {
            return true;  // Token válido
        } else {
            return false;  // Token inválido
        }
    }
}
