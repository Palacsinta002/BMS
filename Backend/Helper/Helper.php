<?php
/**
 * Helper.php
 * 
 * Ez a fájl a `Helper` osztályt definiálja, amely segédfüggvényeket biztosít a bemenetek
 * érvényesítéséhez és a kérés törzsének feldolgozásához. Célja a HTTP kérésekből érkező adatok
 * épségének és biztonságának biztosítása.
 * 
 * Funkciók:
 * - `validateTheInput()`: Egyetlen bemeneti érték érvényesítése és megtisztítása.
 * - `validateTheInputArray()`: Több bemeneti értékből álló tömb érvényesítése és megtisztítása.
 * - `getPostBody()`: A nyers POST kérés törzsének beolvasása és dekódolása, opcionálisan JSON-formátum ellenőrzésével.
 * 
 * Használat:
 * - A `validateTheInput()` függvényt egyéni bemeneti értékek megtisztítására használd.
 * - A `validateTheInputArray()` segítségével tömbök, például lekérdezési paraméterek vagy URL-változók tisztíthatók.
 * - A `getPostBody()` használható POST kérések JSON törzsének értelmezésére és érvényesítésére.
 * 
 * Függőségek:
 * - `ApiResponse\Response`: A HTTP hibaválaszok kezelésére szolgál, ha az érvényesítés sikertelen.
 */

namespace Helper;
use ApiResponse\Response;

class Helper{
    /**
     * validate the input array
     * @param mixed $input
     * @param mixed $decode
     * @return string
     */
    public static function validateTheInput($input, $decode = null)
    {
        if (!isset($input)){
            Response::httpError(400,"bad format");
        }
        if ($decode == "url"){
            $input = urldecode($input);
        }
        $input = htmlspecialchars($input);
        $input = stripcslashes($input);
        $input = trim($input);
        return $input;
    }

    public static function validateTheInputArray($array, $decode = null){
        if (!is_array($array)){
            Response::httpError(400,"bad format");
        }
        foreach ($array as $key => $value) {
            $array[$key] = self::validateTheInput($value, $decode);
        }
        return $array;
    }

    public static function getPostBody($validateJsonFormat = true){
        
        $rawbody = file_get_contents("php://input");
        $decoded = json_decode($rawbody, true);
        if ($validateJsonFormat){
            if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
                Response::httpError(400,29);
            }
        }
        return $decoded;
    }
    public static function overrideDictElements(array $baseDict, array $addDict) : array{
        foreach ($addDict as $key => $value) {
            if (key_exists($key,$baseDict)){
                $baseDict[$key] = $value;
            }
        }
        return $baseDict;
    }
}

?>
