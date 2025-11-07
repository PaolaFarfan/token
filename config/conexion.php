<?php
require_once "config.php";

class Conexion {
    public static function connect() {
        try {
            $mysql = new mysqli(BD_HOST, BD_USER, BD_PASSWORD, BD_NAME);
            
            if (mysqli_connect_errno()) {
                error_log("Error de Conexión: " . mysqli_connect_error());
                return null;
            }
            
            $mysql->set_charset(BD_CHARSET);
            date_default_timezone_set("America/Lima");
            
            return $mysql;
        } catch (Exception $e) {
            error_log("Error de Conexión: " . $e->getMessage());
            return null;
        }
    }
}
?>