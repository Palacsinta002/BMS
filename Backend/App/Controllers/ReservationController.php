<?php
/**
 * ReservationController.php
 * 
 * Ez a fájl a `ReservationController` osztályt definiálja, amely a könyv foglalásokkal kapcsolatos HTTP kéréseket kezeli.
 * Közvetítőként működik a kliens és a `ReservationTable` adatbázis lekérdezések között, elvégezve a bemenetek validálását
 * és a megfelelő válaszokat visszaadva.
 * 
 * Funkciók:
 * - `store($body, $userID)`: Új foglalást hoz létre egy könyv számára egy adott felhasználó által a bemenet validálása után.
 * - `destroy($ISBN, $userID)`: Törli egy adott könyv foglalását egy adott felhasználó számára.
 * - `show($body, $userID)`: Lekérdezi az összes foglalást, amit egy adott felhasználó tett.
 * 
 * Használat:
 * - Használja ezt az kontrollert foglalásokkal kapcsolatos műveletekhez, mint például a foglalás létrehozása, törlése és listázása.
 * - A bemeneti adatokat a `Helper` és `Model` validáló osztályok segítségével validálják, mielőtt adatbázis lekérdezés történik.
 * 
 * Függőségek:
 * - `ApiResponse\Response`: HTTP válaszok küldésére szolgál.
 * - `Database\Queries\ReservationTable`: Adatbázis lekérdezési módszerek biztosítása a foglalásokhoz.
 * - `Database\Queries\BooksTable`: A könyv létezésének validálásához használatos.
 * - `Database\Queries\BorrowingsTable`: A kölcsönzés állapotának ellenőrzésére szolgál.
 * - `Helper\Helper`: A bemenetek validálásához használatos.
 * - `App\Validations\Model`: További validálási módszereket biztosít.
 */

namespace App\Controllers;

use Database\Queries\ReservationTable;
use App\Validations\Model;
use Helper\Helper;
use ApiResponse\Response;
use Database\Queries\BooksTable;
use Database\Queries\BorrowingsTable;


class ReservationController{
    public static function store($body,$userID){
        $body = Helper::validateTheInputArray($body);
        if (!($body = Model::checkRequiredData($body,["ISBN"]))){
            Response::httpError(400,21);
        }
        Model::validateISBN($body["ISBN"]);
        if (BorrowingsTable::bookInBorrowings($body["ISBN"])->num_rows != 0) Response::httpError(400,32);
        if (ReservationTable::bookInReservation($body["ISBN"])->num_rows != 0) Response::httpError(400,32);
        if (BooksTable::selectByISBN($body["ISBN"])->num_rows == 0) Response::httpError(400,32);
        if (ReservationTable::countUserReservations($userID)[0]["userReservations"] >= 3) Response::httpError(400,32);

        ReservationTable::reserve($body["ISBN"],$userID);
        Response::httpSuccess(200,["Success"=>"Reservation made"]);
    }

    public static function destroy($ISBN,$userID){
        Helper::validateTheInput($ISBN);
        $howsBook = ReservationTable::selectByISBN($ISBN,true);
        if (count($howsBook) == 0 || $howsBook[0]["UserID"] != $userID) Response::httpError(400,32);
        model::validateISBN($ISBN);
        ReservationTable::deleteReservation($ISBN);
        Response::httpSuccess(200,["Success"=>"Reservation deleted"]);
    }

    public static function show($body,$userID){
        $userID = Helper::validateTheInput($userID);
        Model::validateID( $userID );
        Response::httpSuccess(200,ReservationTable::selectMyReservations($userID));
    }

}

?>