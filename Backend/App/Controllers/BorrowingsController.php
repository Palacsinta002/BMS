<?php
/**
 * BorrowingsController.php
 * 
 * Ez a fájl a `BorrowingsController` osztályt definiálja, amely a könyvek kölcsönzéseivel kapcsolatos HTTP kéréseket kezeli.
 * A kliens és a `BorrowingsTable` adatbázis lekérdezések között hidat képez, végrehajtja a bemeneti validálást,
 * és megfelelő válaszokat küld vissza.
 * 
 * Funkciók:
 * - `allBorrowings($body)`: Lekéri az adatbázisból a kölcsönzések teljes számát.
 * - `show($body, $userID)`: Lekéri egy adott felhasználó által kölcsönzött könyvek listáját.
 * - `topBorrowedBooks($limit)`: Lekéri a leggyakrabban kölcsönzött könyveket, a megadott számú limit alapján.
 * 
 * Használat:
 * - Használja ezt a kontrollert kölcsönzéssel kapcsolatos műveletekhez, mint például a kölcsönzési statisztikák lekérése vagy felhasználó-specifikus kölcsönzések.
 * - A bemeneti adatokat a `Borrowing` validálási osztály segítségével validálják, mielőtt lekérdezéseket hajtanának végre az adatbázisban.
 * 
 * Függőségek:
 * - `ApiResponse\Response`: A HTTP válaszok küldésére használatos.
 * - `Database\Queries\BorrowingsTable`: Kölcsönzésekkel kapcsolatos adatbázis lekérdezési módszereket biztosít.
 * - `App\Validations\Borrowing`: A kölcsönzéssel kapcsolatos bemeneti adatok validálásához használt osztály.
 */

namespace App\Controllers;
use App\Validations\Borrowing;
use Database\Queries\BorrowingsTable;
use ApiResponse\Response;

class BorrowingsController{
    public static function allBorrowings($body){
        Response::httpSuccess(200, BorrowingsTable::allBorrowings());
    }
    public static function show($body,$userID = null){
        Response::httpSuccess(200, BorrowingsTable::selectMyBooks($userID));
    }

    public static function topBorrowedBooks($limit){
        Borrowing::validateID($limit);
        Response::httpSuccess(200,BorrowingsTable::selectTopBorrowedBook($limit));

    }
}

?>