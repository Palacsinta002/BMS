<?php
/**
 * Response.php
 * 
 * Ez a fájl a `Response` osztályt definiálja, amely hasznos metódusokat biztosít HTTP válaszok küldésére
 * JSON formátumban. Az osztály célja az API végpontok hibás és sikeres válaszainak egységes kezelése.
 * 
 * Funkciók:
 * - `httpError($code, $message)`: HTTP hibaválasz küldése megadott státuszkóddal és hibaüzenettel.
 *   - Ha az üzenet numerikus, akkor a megfelelő hibaüzenetet az `errorCodes.txt` fájlból szerzi be.
 * - `httpSuccess($code, $json)`: HTTP sikeres válasz küldése megadott státuszkóddal és JSON adatokkal.
 * 
 * Használat:
 * - Használjuk a `httpError()` metódust hibás válaszok küldésére a megfelelő HTTP státuszkóddal és hibaüzenetekkel.
 * - Használjuk a `httpSuccess()` metódust sikeres válaszok küldésére JSON formátumban.
 * 
 * Függőségek:
 * - Az `errorCodes.txt` fájlra támaszkodik, amely a numerikus hiba kódokat a hozzájuk tartozó hibaüzenetekre térképezi.
 * 
 * Megjegyzések:
 * - Mindkét metódus automatikusan beállítja a `Content-Type` fejlécet `application/json` értékre,
 *   és a válasz küldése után lezárja a szkriptet.
 */

namespace ApiResponse;

class Response{
    
    public static function httpError( $code, $message ){
        header("Content-Type: application/json");
        http_response_code($code);
        if (!preg_match("/^[0-9]+$/", $message)) {
            echo json_encode([
                "error" => $message
            ]);
            die();
        }
        $lines = file("errorCodes.txt", FILE_IGNORE_NEW_LINES);
        echo json_encode([
            "error" => substr($lines[$message],strpos($lines[$message],"!")+1)
        ]);
        die();
    }
    public static function httpSuccess($code,$json ){
        header("Content-Type: application/json");
        http_response_code($code);
        echo json_encode($json);
        die();
    }
}

?>