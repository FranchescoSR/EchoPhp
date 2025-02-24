<?php
// web.php - Este archivo contendrá las rutas de la aplicación

// Aquí se definen las rutas y sus respectivos controladores
return [
    'usuario/{id}' => 'UsuariosController@mostrarUsuario',  // Ruta para obtener un usuario

    // Ruta para autentificar - recibe usuario y contraseña y genera el token
    'autentificacion' => 'AuthController@autentificar_usuario',  // Ruta para autenticación
];
