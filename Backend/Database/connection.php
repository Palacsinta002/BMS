<?php
/**
 * connection.php
 * 
 * Ez a fájl a `Connection` osztályt definiálja, amely a MySQL adatbázishoz való kapcsolódásért felelős.
 * A fájl betölti a környezeti változókat, és létrehozza a kapcsolatot az adatbázissal.
 * Ha a kapcsolat sikertelen, akkor egy HTTP 500-as hibát küld vissza.
 * A fájl tartalmaz egy statikus `connect()` metódust, amely létrehozza a kapcsolatot
 * és visszaadja a kapcsolat objektumot.
 */
namespace Database;

use Config\Env;
use ApiResponse\Response;
class Connection{

    public static function connect(){
        Env::load();
        try{
            $conn = new \PDO(sprintf("pgsql:host=%s;port=%s;dbname=%s",$_ENV["DB_HOST"],$_ENV["DB_PORT"],$_ENV["DB_NAME"]) , 
            $_ENV["DB_USER"], 
            $_ENV["DB_PASSWORD"]);
        }
        catch (\Exception $e){
            Response::httpError(500, "Database connection error ". $e);
        }
        if (isset(getallheaders()["Test"])){
            //$conn->autocommit(false);
        }
        return $conn;
        
        }
}
?>