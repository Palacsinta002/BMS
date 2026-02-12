<?php
/**
 * BooksController.php
 * 
 * Ez a fájl a `BooksController` osztályt definiálja, amely a könyvekkel kapcsolatos HTTP-kéréseket kezeli.
 * Kapcsolatot teremt a kliens és a `BooksTable` adatbázis-lekérdezések között, elvégzi a bemeneti adatok validálását,
 * és megfelelő válaszokat ad vissza.
 * 
 * Funkciók:
 * - `getFromDBByParams()`: Könyvek lekérdezése az adatbázisból megadott paraméterek alapján.
 * - `countAllBooks()`: A könyvek teljes számának visszaadása az adatbázisból.
 * - `availableBooks()`: Az éppen elérhető (nem kölcsönzött vagy lefoglalt) könyvek listájának lekérdezése.
 * 
 * Használat:
 * - Ezt a kontrollert használd könyvekkel kapcsolatos műveletekhez, például kereséshez, számláláshoz
 *   vagy elérhetőség ellenőrzéséhez.
 * - A bemeneti adatokat a `Helper` és a `Books` validáló osztályok segítségével ellenőrzi, mielőtt lekérdezést végezne.
 * 
 * Függőségek:
 * - `ApiResponse\Response`: HTTP válaszok küldésére szolgál.
 * - `Database\Queries\BooksTable`: A könyvekhez kapcsolódó adatbázis-lekérdezési metódusokat biztosítja.
 * - `Helper\Helper`: A bemeneti adatok validálására szolgál.
 * - `App\Validations\Books`: Könyvekkel kapcsolatos speciális validálási metódusokat tartalmaz.
 */

namespace App\Controllers;

use ApiResponse\Response;
use Database\Queries\BooksTable;
use Helper\Helper;
use App\Validations\Books;
class BooksController{
    public static function getFromDBByParams($body){
        $body = Helper::validateTheInputArray($body, "url");  
        Books::callingValidateFunctions($body,["publihser","Publisher"],Books::class,"validatePublisher");
        Books::callingValidateFunctions($body,["ISBN"],Books::class,"validateISBN");
        Books::callingValidateFunctions($body,["author","Author"],Books::class,"validateFullName");
        Books::callingValidateFunctions($body,["category","Category"],Books::class,"validatefullName");
        Books::callingValidateFunctions($body,["title","Title"],Books::class,"validateTitle");
        Books::callingValidateFunctions($body,["publicationYear","PublicationYear","publicationyear"],Books::class,"validateYear");
        $body = Books::checkRequiredData($body,[],[
            ["ISBN","books.ISBN"],
            ["publihser","publishers.publisher"],
            ["Publihser","publishers.publisher"],
            ["Author","Authors.author"],
            ["author","Authors.author"],
            ["category","categories.category"],
            ["Category","categories.category"],
            ["title","books.title"],
            ["Title","books.title"],
            ["publicationYear","books.publicationYear"],
            ["PublicationYear","books.publicationYear"]]);

        $typesAndOperators = Books::makeTypesArray($body,
        ["books.ISBN","publishers.publisher","Authors.author","categories.category","books.title","books.publicationYear"],
        ["i","s","s","s","s","i"],
        ["=","=","LIKE","LIKE","=","="]);
        Response::httpSuccess(200,BooksTable::selectByParams(
            array_keys($body),
            array_values($typesAndOperators),
            array_values($body),
            array_keys($typesAndOperators)));
    }

    public static function countAllBooks(){
        Response::httpSuccess(200, BooksTable::countAllBooks());
    }

    public static function availableBooks($body){
        Response::httpSuccess(200,BooksTable::selectAvailableBooks());
        
    }

}
?>