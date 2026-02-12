<?php
/**
 * ReservationTable.php
 * 
 * Ez a fájl a `ReservationTable` osztályt definiálja, amely a `Table` osztályból származik,
 * és kifejezetten a foglalásokkal kapcsolatos adatok kezelésére és lekérdezésére biztosít metódusokat.
 * 
 * Funkciók:
 * - `reserve()`: Új foglalás létrehozása egy adott könyvre (ISBN alapján) és felhasználóra.
 * - `deleteReservation()`: Egy adott könyvre (ISBN alapján) vonatkozó foglalás törlése.
 * - `bookInReservation()`: Annak ellenőrzése, hogy egy adott könyv (ISBN alapján) jelenleg le van-e foglalva.
 * - `countUserReservations()`: Egy adott felhasználó által létrehozott foglalások számának lekérdezése.
 * - `selectByISBN()`: Foglalás részleteinek lekérdezése egy adott könyvre (ISBN alapján).
 * - `selectMyReservations()`: Egy adott felhasználó által létrehozott foglalások listázása, a foglalás részleteivel együtt.
 * 
 * Használat:
 * - Ezekkel a metódusokkal lehet kapcsolatba lépni a `reservations` táblával és a kapcsolódó táblákkal (pl. `books`, `authors`, `categories`).
 * - A `selectMyReservations()` metódus támogatja az összetett lekérdezéseket join-okkal, csoportosítással és szűréssel.
 * 
 * Függőségek:
 * - A `Table` osztályból származik, amely az alap lekérdezésépítési funkciókat biztosítja.
 * - Használja a kapcsolódó adatbázistáblákat, mint például: `reservations`, `books`, `authors`, `categories`, és `publishers`.
 */

namespace Database\Queries;

class ReservationTable extends Table{
    public static function reserve($ISBN,$userID){
        self::insert("Reservations",["ISBN","userID"],[$ISBN,$userID],["i","i"])->execute(false,false);
        
    }

    public static function deleteReservation($ISBN){
        self::delete("Reservations")
        ->where(["ISBN"],["="],[$ISBN],["i"])->execute(false,false);
    }

    public static function bookInReservation($ISBN){
        return self::select(["Reservations"],["ISBN"])
        ->where(["ISBN"],["="],[$ISBN],["i"])->execute(true,false);
    }
    public static function countUserReservations($userID){
        return self::select(["Reservations"],["count(ISBN) as userReservations"])
        ->where(["userID"],["="],[$userID],["i"])->execute(true);
    }
    public static function selectByISBN($ISBN,$fetch = false){
        return self::select(["Reservations"],["*"])
        ->where(["ISBN"],["="],[$ISBN],["i"])->execute(true,$fetch);
    }
    public static function selectMyReservations($userID){
        return self::select(["Reservations"],[
            "reservations.ISBN",
            "reservations.ReservationStartDate",
            "reservations.ReservationEndDate",
            "publishers.Publisher",
            "books.Title","GROUP_CONCAT(DISTINCT Authors.Author SEPARATOR ',') as 'Authors'",
            "GROUP_CONCAT(DISTINCT categories.category SEPARATOR ',') as 'Category'","books.publicationYear"
            ])
        ->innerJoin("books",["books.ISBN"],["="],["reservations.ISBN"])
        ->innerJoin("Publishers",["Publishers.ID"],["="],["books.PublisherID"])
        ->innerJoin("Books_authors",["Books.ISBN"],["="],["Books_Authors.ISBN"])
        ->innerJoin("Authors",["Books_Authors.AuthorID"],["="],["Authors.ID"])
        ->innerJoin("Books_Categories",["Books.ISBN"],["="],["Books_Categories.ISBN"])
        ->innerJoin("Categories",["Books_Categories.CategoryID"],["="],["Categories.ID"])
        ->where(["reservations.userID"],["="],[$userID],["i"])
        ->groupBy(["publishers.Publisher","books.Title","books.publicationYear"])
        ->execute(true);
    }
}


?>