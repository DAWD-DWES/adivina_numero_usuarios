<?php
session_start();
// Definición de constantes o parámetros de funcionamiento del juego
define('MAX_INTENTOS', 5);
define('LIM_INF', 1);
define('LIM_SUP', 20);
define('ERROR_APUESTA', 'Introduce una apuesta');

if (isset($_SESSION['usuario'])) {
    $usuario = $_SESSION['usuario'];
    if (filter_has_var(INPUT_POST, 'envio_apuesta')) {
        $numOculto = $_SESSION['num_oculto'];
        $numIntentos = $_SESSION['num_intentos'];
        $apuesta = filter_input(INPUT_POST, 'apuesta', FILTER_VALIDATE_INT);
        $numeros = $_SESSION['numeros'];
        $apuestaErr = empty($apuesta);
        if (!$apuestaErr) {
            $numeros[] = $apuesta;
            $numIntentos++;
            $_SESSION['apuesta'] = $apuesta;
            $_SESSION['num_intentos'] = $numIntentos;
            $_SESSION['numeros'] = $numeros;
            $fin = $numIntentos >= MAX_INTENTOS || $apuesta === $numOculto; // Establezco si se ha acabado la partida o no
            $_SESSION['fin'] = $fin;
        }
    } elseif (filter_has_var(INPUT_POST, 'numeros_jugados')) {
        $petNumerosJugados = true;
        $numOculto = $_SESSION['num_oculto'];
        $numIntentos = $_SESSION['num_intentos'];
        $numeros = $_SESSION['numeros'];
        $fin = $_SESSION['fin'];
        $apuesta = $_SESSION['apuesta'];
    } else { // Si se arranca el juego o se solicita una nueva partida
        $_SESSION['num_intentos'] = $numIntentos = 0;
        $_SESSION['num_oculto'] = $numOculto = mt_rand(LIM_INF, LIM_SUP); // Genero un valor aleatorio
        $_SESSION['numeros'] = $numeros = []; // Array de números jugados
    }
} else {
    header('Location:index.php');
    die;
}
?>

<!DOCTYPE html>
<html>
    <head>
        <title>¡Adivina número!</title>
        <meta name="viewport" content="width=device-width">
        <meta charset="UTF-8">
        <!-- css para usar Bootstrap -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" 
              integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" 
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    </head>
    <body>
        <div class= "d-flex flex-column vh-100">
            <nav class="navbar navbar-light bg-light d-flex justify-content-around">
                <div class="fs-5">Adivina Número</div><div></div>
                <div class="d-flex dropdown p-2">
                    <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" 
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <?= $usuario ?>
                    </button>
                    <ul class = "dropdown-menu" aria-labelledby = "dropdownMenuButton">
                        <li><a class = "dropdown-item" href = "index.php?logout">Salir</a></li>
                        <li><a class = "dropdown-item" href = "index.php?baja">Baja</a></li>
                    </ul>
                </div>
            </nav>
            <div class = "d-flex flex-column m-5" style = "flex: 1 1 auto">
                <div class = "container-sm">
                    <h1 class = "text-center mb-4">¡Adivina el número oculto!</h1>
                    <div class = "border border-4 border-warning p-5">
                        <form name = "form_apuestanumero" action = "<?= "{$_SERVER['PHP_SELF']}" ?>" method = "POST">
                            <div class = "row">
                                <div class = "col-md-6">
                                    <label for = "apuesta" class = "fs-5 col-form-label col-6"><?= 'Introduce un número (' . LIM_INF . '-' . LIM_SUP . '):' ?></label> 
                                </div>
                                <div class="col-md-6">
                                    <input id="apuesta" type="number"  required name="apuesta" min="<?= LIM_INF ?>" 
                                           class="<?= "form-control w-25 " . (isset($errorNombre) ? ($errorNombre ? "is-invalid" : "is-valid") : "")?> "
                                           max="<?= LIM_SUP ?>" value="<?= ($apuesta) ?? '' ?>" <?= !empty($fin) ? 'readonly' : '' ?> />
                                    <div class="invalid-feedback">
                                        <p>{{ ERROR_APUESTA }}</p>
                                    </div>
                                </div>
                            </div>
                            <?php if (isset($fin) && $fin): ?> <!-- Si se ha acabado el juego -->
                                <div class="d-flex justify-content-center mt-5">
                                    <!-- Añado un botón para iniciar una nueva partida y un mensaje de fin de juego -->
                                    <!-- <input class="submit" type="submit" value="Nuevo Juego" name="nuevo_juego" /> -->
                                    <!-- <input class="submit" type="submit" formmethod="GET" value="Nuevo Juego" name="nuevo_juego"> -->
                                    <a href="<?= "{$_SERVER['PHP_SELF']}?nuevo_juego" ?>"><input class="btn btn-warning" value="Nuevo Juego"></a>
                                </div>
                                <p class="text-center mt-5 fs-5"><?= ($apuesta === $numOculto) ? "Enhorabuena!!! Lo has acertado en {$numIntentos} " . (($numIntentos !== 1) ? "intentos" : "intento") : 'Lo sentimos!!' ?></p> 
                            <?php else: ?> <!-- Si no se ha acabado el juego o es el inicio de un nuevo juego-->
                                <div class="d-flex justify-content-center mt-5">
                                    <!-- Añado un botón para enviar apuesta -->
                                    <input class="btn btn-warning" type="submit" 
                                           value="Apuesta" name="envio_apuesta" /> 
                                </div>

                                <?php if (isset($fin) && !$fin): ?> <!-- Si no se ha acabado el juego -->
                                    <div class="text-center mt-5 fs-5">
                                        <!-- Añado una pista para el usuario -->
                                        <p>Intentos restantes: <?= MAX_INTENTOS - $numIntentos ?></p>
                                        <p><?= ($apuesta <=> $numOculto) > 0 ? 'Inténtalo con un número mas bajo' : 'Inténtalo con un número mas alto' ?></p>
                                    </div>
                                <?php endif ?> 
                            <?php endif ?>
                            <?php if (isset($petNumerosJugados)): ?>
                                <p class="text-center mt-5 fs-5">
                                    <?= ($numeros) ? "Ya has jugado con los siguientes números: " . implode(",", $numeros) : "No hay números todavía" ?></p>
                            <?php endif ?>
                            <div class="d-flex justify-content-center mt-5">
                                <input class="btn btn-warning" type="submit" 
                                       value="Números Jugados" name="numeros_jugados" />
                            </div>
                        </form> 
                    </div>
                </div>  
            </div>
        </div>
    </body>
</html>