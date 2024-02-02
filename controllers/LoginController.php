<?php

namespace Controllers;

use Classes\Email;
use Model\Usuario;
use MVC\Router;

class LoginController {
    public static function login(Router $router) {
        $alertas = [];

      if($_SERVER['REQUEST_METHOD'] === 'POST') {
           $usuario = new Usuario($_POST);

           $alertas = $usuario->validarLogin();

           if(empty($alertas)) {
            //Verificar que el usuario no exista
            $usuario = Usuario::where('email', $usuario->email);

            if(!$usuario || !$usuario->confirmado ) {
                Usuario::setAlerta('error', 'El Usuario No Existe o no esta Confrimado');
                } else {
                  //El Usuario Existe
                  if( password_verify($_POST['password'], $usuario->password) ) {
                    
                    //Iniciar la sesion
                    session_start();
                    $_SESSION['id'] = $usuario->id;
                    $_SESSION['nombre'] = $usuario->nombre;
                    $_SESSION['email'] = $usuario->email;
                    $_SESSION['login'] = true;

                    //Redireccionar
                    header("Location: /dashboard");

                  } else {
                    Usuario::setAlerta('error', 'Password Incorrecto');
                  }
                }
           
           }
        }

        $alertas = Usuario::getAlertas();
        //Render a la vista
        $router->render('auth/login', [
            'titulo' => "Iniciar Sesion",
            'alertas' => $alertas
         ]);
    }

    public static function logout() {
       session_start();
       $_SESSION = [];
       header('Location: /');

    }

    public static function crear(Router $router) {
        $alertas = [];
        $usuario = new Usuario;
      

        if($_SERVER['REQUEST_METHOD'] === 'POST') {  
          $usuario->sincronizar($_POST);
          $alertas = $usuario->validarNuevaCuenta();
      
          if(empty($alertas)) {
            $existeUsuario = Usuario::where('email', $usuario->email);

            if($existeUsuario) {
              Usuario::setAlerta('error', 'El Usuario ya esta registrado');
              $alertas = Usuario::getAlertas();
            } else {
                //Hashear Password
                $usuario->hashPassword();

                //Eliminar password2
                unset($usuario->password2);

                //Generar el Token
                $usuario->crearToken();
                
                //Crear un nuevo usuario 
              $resultado = $usuario->guardar();

              //Enviar email
              $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
              $email->enviarConfirmacion();



              if($resultado) {
                header('Location: /mensaje');
              }

            }
          }

        }
        //Render a la vista
        $router->render('auth/crear', [
             'titulo' => 'Crea tu cuenta',
             'usuario' => $usuario,
             'alertas' => $alertas
        ]);
    }

    public static function olvide(Router $router) {
         $alertas = [];
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = new Usuario($_POST);
            $alertas = $usuario->validarEmail();

            if(empty($alertas)) {
              //Buscar el usuario
              $usuario = Usuario::where('email', $usuario->email);

              if($usuario && $usuario->confirmado) {

                //Generar un nuevo token
                $usuario->crearToken();
                unset($usuario->password2);
                  
                //Actualizar el usuario
                $usuario->guardar();

                //Enviar el email
                $email = new Email( $usuario->email, $usuario->nombre, $usuario->token );
                $email->enviarInstrucciones();

                //Imprimir la alerta
                Usuario::setAlerta('exito', 'Hemos enviado las instrucciones a tu email');

              } else {
                Usuario::setAlerta('error', 'El Usuario no existe o no esta confirmado');
                
              }
           
            }
          }
          $alertas = Usuario::getAlertas();

            //Muestra la vista
            $router->render('auth/olvide', [
              'titulo' => 'Olvide mi password',
              'alertas' => $alertas
            ]);
          
        }

    public static function restablecer(Router $router) {

      $token = s($_GET['token']);
      $mostrar = true;

      if(!$token) header('Location: /');

      //Identificar el usuario con este token
      $usuario = Usuario::where('token', $token);

      if(empty($usuario)) {
        Usuario::setAlerta('error', 'Token No Valido');
        $mostrar = false;
      }

      if($_SERVER['REQUEST_METHOD'] === 'POST') {

        //Agregar nuevo Password
        $usuario->sincronizar($_POST);

        //Validar el Password
        $alertas = $usuario->validarPassword();

        if(empty($alertas)) {
          //Hashear el nuevo password
          $usuario->hashPassword();

          //Eliminar el token
          $usuario->token = null;

          //Guardar el usuario en la BD
          $resultado =  $usuario->guardar();
 
          //Redireccionar
           if($resultado) {
            header('Location: /');
          }

        }

        }

        $alertas = Usuario::getAlertas();

        //Muestra la vista
         $router->render('auth/restablecer', [
            'titulo' => "Restablecer Password",
            'alertas' => $alertas,
            'mostrar' => $mostrar
         ]);
    }

    public static function mensaje(Router $router) {
        

        //Muestra la vista
        $router->render('auth/mensaje', [
            'titulo' => "Cuenta Creada Exitosamente"
        ]);
    }

    public static function confirmar(Router $router) {

        $token = s($_GET['token']);
        $alertas = [];
        if(!$token) header('Location: /');

        //Encontrar el usuario con este token
        $usuario =  Usuario::where('token', $token);

        //No se encontro un usuario con ese token
        if(empty($usuario)) {
            Usuario::setAlerta('error', 'Token No Valido');
        } else {
            //Confirmar la cuenta 
            $usuario->confirmado = 1;
            $usuario->token = "";
            unset($usuario->password2);
            
            //Guardar en la BD
            $usuario->guardar();

            Usuario::setAlerta('exito', 'Cuenta comprobada correctamente');
        }

        $alertas = Usuario::getAlertas();
       
       $router->render('auth/confirmar', [
          'titulo' => 'Confirma tu cuenta',
          'alertas' => $alertas
       ]);
    }
}