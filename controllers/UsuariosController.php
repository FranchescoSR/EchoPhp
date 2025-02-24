<?php
require_once __DIR__ . '/../core/DB.php';

class UsuariosController {
    public function mostrarUsuario($id) {
        // Simulación de base de datos
        $usuario = DB::table('usuarios')->where('Id_Usuario', $id)->first();

        if ($usuario) {
            echo json_encode(['data' => $usuario, 'success' => true]);
        } else {
            echo json_encode(['data' => 'Usuario no encontrado', 'success' => false]);
        }
    }
}
