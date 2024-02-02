<?php 

namespace Controllers;

use Model\Proyecto;
use Model\Usuario;
use MVC\Router;

class DashboardController {
    public static function index(Router $router) {

        session_start();
        isAuth();

        $id = $_SESSION['id'];
        $proyectos = Proyecto::belongsTo('propietarioId', $id);




        $router->render('dashboard/index', [
          'titulo' => 'proyectos',
          'proyectos'=>$proyectos
        ]);
    }

    public static function crear_proyecto(Router $router) {
        session_start();
        isAuth();
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
          $proyecto = new Proyecto($_POST);

          //Validacion
          $alertas = $proyecto->validarProyecto();

          if(empty($alertas)) {
            //Generar una URL unica
            $hash =md5(uniqid());
            $proyecto->url = $hash;

            //Almacenar el creador del proyecto
             $proyecto->propietarioId = $_SESSION['id'];

            //Guardar el Proyecto
            $proyecto->guardar();

            //Redireccionar
            header('Location: /proyecto?id=' . $proyecto->url);
          }



        }

        $router->render('dashboard/crear-proyecto', [
            'alertas' => $alertas,
            'titulo' => 'crear proyecto'
          ]);
    }



    public static function proyecto(Router $router) {
        session_start();
        isAuth();
        $token = $_GET['id'];
        if(!$token) header('Location: /dashboard');
        //Revisar que la persona que visita el proyecto, es quien lo creo
        $proyecto = Proyecto::where('url', $token);
        if($proyecto->propietarioId !== $_SESSION['id']) {
          header('Location: /dashboard');
        }


      $router->render('dashboard/proyecto', [
           'titulo' => $proyecto->proyecto
      ]);
    }

    public static function perfil(Router $router) {
        session_start();
        isAuth();
        $alertas = [];

        $usuario = Usuario::find($_SESSION['id']);

       if($_SERVER['REQUEST_METHOD'] === 'POST') {

        $usuario->sincronizar($_POST);

        $alertas = $usuario->validar_perfil();

        if(empty($alertas)) {

          //Verificar que el Email no pertenezca a otro usuario
          $existeUsuario = Usuario::where('email', $usuario->email);

          if($existeUsuario && $existeUsuario->id !== $usuario->id ) {
            //mensaje de error
            Usuario::setAlerta('error', 'Email no valido, Ya pertenece a otra cuenta');
          $alertas = $usuario->getAlertas();

          } else {
            //guardar el registro

            //Guardar el usuario
          $usuario->guardar();

          Usuario::setAlerta('exito', 'Guardado Correctamente');
          $alertas = $usuario->getAlertas();

          //Asignar el nombre nuevo a la barra
          $_SESSION['nombre'] = $usuario->nombre;
          }

          
        }

       }

        $router->render('dashboard/perfil', [
            'titulo' => 'perfil',
            'usuario' => $usuario,
            'alertas' => $alertas
          ]);
    }

    public static function cambiar_password(Router $router) {
      session_start();
      isAuth();

      $alertas = [];

      if($_SERVER['REQUEST_METHOD'] === 'POST') {
        $usuario = Usuario::find($_SESSION['id']);

        //Sincronizar con los datos del Usuario
        $usuario->sincronizar($_POST);

        $alertas = $usuario->nuevo_password();

         if(empty($alertas)) {
           $resultado = $usuario->comprobar_password();

           if($resultado) {
            $usuario->password = $usuario->password_nuevo;

            //Elimina Propiedades no necesarias
            unset($usuario->password_actual);
            unset($usuario->password_nuevo);

            //Hashear el nuevo password
            $usuario->hashPassword();

            //Actualizar
            $resultado = $usuario->guardar();

            if($resultado) {
              Usuario::setAlerta('exito', 'Password Guardado Correctamente');
              $alertas = $usuario->getAlertas();
            }
           } else {
            Usuario::setAlerta('error', 'Password Incorrecto');
            $alertas = $usuario->getAlertas();
           }
         }
      }

      $router->render('dashboard/cambiar-password',[
          'titulo' => 'Cambiar Password',
          'alertas'=> $alertas
      ]);

    }
}