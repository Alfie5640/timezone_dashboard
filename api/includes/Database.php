<?php
class Database {
    private static ?mysqli $connection = null;

    public static function connect(): mysqli {
        if (self::$connection === null) {
            require __DIR__ . '/../../config.local.php';

            self::$connection = new mysqli($dbserver, $dbuser, $dbpass, $dbname);

            if (self::$connection->connect_error) {
                die(json_encode(['error' => 'Database connection failed']));
            }
        }

        return self::$connection;
    }
}
?>