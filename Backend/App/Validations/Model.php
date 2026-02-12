<?php
/**
 * Model.php
 * 
 * Ez a fájl a `Model` osztályt definiálja, amely segédfunkciókat biztosít az adatok validálásához és feldolgozásához.
 * Az osztály alapértelmezettként szolgál az input validálásához, típuskezeléshez és egyéb gyakori műveletekhez,
 * amelyek szükségesek a validálási munkafolyamatokhoz.
 * 
 * Funkciók:
 * - `checkRequiredData()`: Validálja és kinyeri a szükséges és opcionális adatokat egy bemeneti tömbből.
 * - `makeTypesArray()`: SQL lekérdezésekhez szükséges adattípusok tömbjét generálja a bemeneti mezők és operátorok alapján.
 * - `callingValidateFunctions()`: Dinamikusan hívja meg a validálási funkciókat a megadott mezők számára.
 * - `validateISBN()`: Validálja az ISBN formátumot (10 vagy 13 számjegy).
 * - `validateID()`: Validálja egy azonosítót, hogy csak numerikus karaktereket tartalmazzon.
 * - `removeNullValues()`: Eltávolítja a null vagy üres értékeket egy bemeneti tömbből.
 * 
 * Használat:
 * - Használja ezt az osztályt alapként a validálási logikában más osztályok számára.
 * - Az olyan metódusok, mint a `checkRequiredData()` és `callingValidateFunctions()`, hasznosak az input adatok strukturált feldolgozásához és validálásához.
 * 
 * Függőségek:
 * - `ApiResponse\Response`: HTTP hibás válaszok küldésére szolgál, amikor a validálás nem sikerül.
 */

namespace App\Validations;

use ApiResponse\Response;

class Model{
    public static function checkRequiredData($inputList,$requiredData,$optional = []){
        $arrayRequired = [];
        
        for ($i=0; $i < count($requiredData); $i++) { 
            if (is_array($requiredData[$i])) {
                $tmp = $requiredData[$i][0];
            }
            $tmp = $requiredData[$i];
            
            if (!isset($inputList[$tmp])) {
                return false;
            }

            if (is_array($requiredData[$i])){
                $arrayRequired[$requiredData[$i][1]] =  $inputList[$tmp];
            }
            else{
                $arrayRequired[$tmp] =  $inputList[$tmp];
            }
            
        }
        for ($i= 0; $i < count($optional); $i++){
            if (is_array($optional[$i])) {
                $tmp = $optional[$i][0];
            }
            else{
                $tmp = $optional[$i];
            }
            if (isset($inputList[$tmp])) {
                if (is_array($optional[$i])) {
                    $arrayRequired[$optional[$i][1]] = $inputList[$optional[$i][0]];
                }
                else{
                    $arrayRequired[$tmp] =  $inputList[$tmp];
                }
                
            }
        }
        
        return $arrayRequired;
    }
    public static function makeTypesArray(&$inputList,$fields,$types,$operators = []){
        $typesRes = [];
        if (count($operators) == 0){
            for ($i= 0; $i < count($fields); $i++){
                if (isset($inputList[$fields[$i]])) {
                    $typesRes[] = $types[$i];
                }
            }
            return $typesRes;
        }
        else{
            for ($i= 0; $i < count($fields); $i++){
                if (isset($inputList[$fields[$i]])) {
                    $typesRes[$types[$i]] = $operators[$i];
                    if ($operators[$i] == "LIKE") {
                        $inputList[$fields[$i]] = "%". $inputList[$fields[$i]] ."%";
                    }
                }
            }
            return $typesRes;
        }
    }
    public static function callingValidateFunctions($inputList, $fields, $model, $function){
        for ($i=0; $i < count($fields); $i++) {
            if (is_array( $fields[$i])) {
                $array =[];
                for ($j=0; $j < count($fields[$i]); $j++) { 
                    if (!isset($inputList[$fields[$i][$j]])){
                        $array = [];
                        break;
                    }
                    $array[] = $inputList[$fields[$i][$j]];
                }
                if (count( $array) > 0) {
                    $model::$function(...array_values($array));
                }
            }
            else{
                if (isset($inputList[$fields[$i]])) {
                    $model::$function($inputList[$fields[$i]]);
                }
            }
            
            
        }
    }
    public static function validateISBN( $ISBN ){
        $ISBN = str_replace("-", "", $ISBN);
        if (!preg_match("/^([0-9]{13}|[0-9]{10})$/", $ISBN)) {
            Response::httpError(400,24);
        }
        return true;
    }

    public static function validateID( $ID ){
        $pattern = "/^[0-9]+$/";

        if (!preg_match($pattern, $ID)) {
            Response::httpError(400,27);
        }
        return true;
    }
    public static function removeNullValues( $inputArray ){
        foreach ($inputArray as $key => $value) {
            if ($inputArray[$key] == ""){
                unset($inputArray[$key]);
            }
        }
        return $inputArray;
    }
}


?>