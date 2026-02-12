<?php
/**
 * Borrowing.php
 * 
 * Ez a fájl a `Borrowing` osztályt definiálja, amely validálási funkciókat biztosít a kölcsönzéssel kapcsolatos adatok kezelésére.
 * Biztosítja a bemenetek helyességét, például a kölcsönzés dátumainak és a kölcsönzési limiteknek.
 * 
 * Funkciók:
 * - `checkFormatDate($date)`: Validálja egy dátum formátumát (YYYY-MM-DD), és biztosítja, hogy érvényes naptári dátum legyen.
 * - `checkLimit($limit)`: Validálja, hogy a kölcsönzési limit numerikus érték-e.
 * 
 * Használat:
 * - Használja ezeket a metódusokat kölcsönzéssel kapcsolatos bemenetek validálására adatrögzítés vagy frissítés során.
 * - Ezek a validálások biztosítják, hogy az adatok az elvárt formátumoknak és korlátozásoknak megfelelőek.
 * 
 * Függőségek:
 * - `ApiResponse\Response`: HTTP hibás válaszok küldésére szolgál, amikor a validálás nem sikerül.
 */

namespace App\Validations;
use ApiResponse\Response;
class Borrowing extends Model{
    public static function checkFormatDate($date){
        if ($date == "NULL"){
            return true;
        }
        $pattern = "/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$/";
        if (!preg_match($pattern, $date)){
            Response::httpError(400,23);
            
        }
        $date = explode("-", $date);
        if (!checkdate($date[1],$date[2],$date[0])) {
            Response::httpError(400,22);
        }
        return true;
    }
    public static function checkLimit($limit){
        if (!preg_match("/^[0-9]+$/", $limit)){
            Response::httpError(400,3);
        }
        return true;
    }
}

?>