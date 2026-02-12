<?php
/**
 * Env.php
 * 
 *  Ez a fájl a környezeti változók betöltéséért felelős osztályt definiálja.
 *  A `Dotenv` könyvtár segítségével betölti a `.env` fájlban található változókat,
 * 
 */
namespace Config;
use Dotenv\Dotenv;

class Env{

    public static function load(){
        $dotenv = Dotenv::createImmutable(__DIR__);
        $dotenv->load();
    }
}

?>