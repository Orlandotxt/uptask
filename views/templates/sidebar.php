<aside class="sidebar">
    <div class="contenedor-sidebar">
    <h2>UpTask</h2>
    <div class="cerrar-menu">
        <img id="cerrar-menu" src="build/img/cerrar.svg" alt="imagen cerrar menu">
    </div>
    </div>
   

    <div class="sidebar-nav">
        <a class="<?php echo ($titulo === 'proyectos') ? 'activo' : ''; ?>" href="/dashboard">Proyectos</a>
        <a class="<?php echo ($titulo === 'crear proyecto') ? 'activo' : ''; ?>" href="/crear-proyecto">Crear Proyecto</a>
        <a class="<?php echo ($titulo === 'perfil') ? 'activo' : ''; ?>" href="/perfil">Perfil</a>
    </div>

    <div class="cerrar-sesion-mobile">
        <a href="/logout" class="cerrar-sesion">Cerrar Sesion</a>
    </div>
</aside>