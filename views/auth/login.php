<div class="contenedor login">
<?php include_once __DIR__ . '/../templates/nombre-sitio.php'; ?>

    <div class="contenedor-sm">
        <p class="descripcion-pagina">Inicia Sesion</p>

        <?php include_once __DIR__ . '/../templates/alertas.php'; ?>

        <form class="formulario" method="POST" action="/" novalidate>
            <div class="campo">
                <label for="email">Email</label>
                <input 
                type="email"
                id="email"
                placeholder="Correo electronico"
                name="email"
                />
            </div>

            <div class="campo">
                <label for="password">Contraseña</label>
                <input 
                type="password"
                id="password"
                placeholder="Tu Contraseña"
                name="password"
                />
            </div>

            <input type="submit" class="boton" value="Iniciar Sesion">
        </form>

        <div class="acciones">
          <a href="/crear">Aun no tienes una cuenta? Obtener una</a>
          <a href="/olvide">Olvidaste tu contraseña</a>
        </div>
    </div> <!--.contenedor-sm -->
</div>