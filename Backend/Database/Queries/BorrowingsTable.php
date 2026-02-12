<?php
/**
 * BorrowingsTable.php
 * 
 * Ez a fájl a `BorrowingsTable` osztályt definiálja, amely a `Table` osztályból származik,
 * és kifejezetten a kölcsönzésekkel kapcsolatos adatok kezelésére és lekérdezésére biztosít metódusokat.
 * 
 * Funkciók:
 * - `allBorrowings()`: Az összes kölcsönzés megszámlálása az adatbázisban.
 * - `bookInBorrowings()`: Annak ellenőrzése, hogy egy adott könyvet (ISBN alapján) jelenleg kölcsönözték-e, és még nem került vissza.
 * - `selectMyBooks()`: Egy adott felhasználó által kölcsönzött könyvek listázása, a kölcsönzés részleteivel együtt.
 * - `selectTopBorrowedBook()`: A legtöbbször kölcsönzött könyvek lekérdezése, megadott darabszámig korlátozva.
 * 
 * Használat:
 * - Ezekkel a metódusokkal lehet kapcsolatba lépni a `borrowings` táblával és a kapcsolódó táblákkal (pl. `books`, `authors`, `categories`).
 * - A `selectMyBooks()` és `selectTopBorrowedBook()` metódusok támogatják az összetett lekérdezéseket join-okkal, csoportosítással és rendezéssel.
 * 
 * Függőségek:
 * - A `Table` osztályból származik, amely az alap lekérdezésépítési funkciókat biztosítja.
 * - Használja a kapcsolódó adatbázistáblákat, mint például: `borrowings`, `books`, `authors`, `categories`, és `publishers`.
 */

namespace Database\Queries;

class BorrowingsTable extends Table{

    public static function allBorrowings(){
        return self::select(["borrowings_storage"],["count(ISBN) AS borrowings"])->execute(true);
    }
    public static function bookInBorrowings($ISBN){
        return self::select(["borrowings"],["ISBN"])->where(
            ["ISBN","returnDate"],
            ["=","IS"],
            [$ISBN,"NULL"],
            ["i","i"])
            ->execute(true,false);
    }
    public static function selectMyBooks($userID){
        return self::select(["borrowings"],[
            "borrowings.ISBN",
            "publishers.Publisher",
            "books.Title",
            "GROUP_CONCAT(DISTINCT Authors.Author SEPARATOR ',') as 'Authors'",
            "GROUP_CONCAT(DISTINCT categories.category SEPARATOR ',') as 'Category'",
            "books.publicationYear","borrowings.BorrowDate","borrowings.DueDate","borrowings.ReturnDate"
            ])
        ->innerJoin("books",["books.ISBN"],["="],["borrowings.ISBN"])
        ->innerJoin("Publishers",["Publishers.ID"],["="],["books.PublisherID"])
        ->innerJoin("Books_authors",["Books.ISBN"],["="],["Books_Authors.ISBN"])
        ->innerJoin("Authors",["Books_Authors.AuthorID"],["="],["Authors.ID"])
        ->innerJoin("Books_Categories",["Books.ISBN"],["="],["Books_Categories.ISBN"])
        ->innerJoin("Categories",["Books_Categories.CategoryID"],["="],["Categories.ID"])
        ->where(["borrowings.userID"],["="],[$userID],["i"])
        ->groupBy(["borrowings.ISBN","publishers.Publisher","books.Title","books.publicationYear","borrowings.borrowDate","borrowings.DueDate","borrowings.ReturnDate"])
        ->orderBy(["borrowings.borrowDate"],false)
        ->execute(true);
    }
    public static function selectTopBorrowedBook($limit){
        return self::select(
            ["books"],
            [
                "books.ISBN",
                "books.Title",
                "GROUP_CONCAT(DISTINCT Authors.Author SEPARATOR ',') as 'Authors'",
                "GROUP_CONCAT(DISTINCT categories.category SEPARATOR ',') as 'Category'",
                "COUNT(DISTINCT borrowings.ID) AS BorrowCount", 
                "books.publicationYear"
            ]
        )
        ->innerJoin("borrowings", ["books.ISBN"], ["="], ["borrowings.ISBN"])
        ->innerJoin("Books_authors", ["Books.ISBN"], ["="], ["Books_Authors.ISBN"])
        ->innerJoin("Authors", ["Books_Authors.AuthorID"], ["="], ["Authors.ID"])
        ->innerJoin("Books_Categories",["Books.ISBN"],["="],["Books_Categories.ISBN"])
        ->innerJoin("Categories",["Books_Categories.CategoryID"],["="],["Categories.ID"])
        ->groupBy(["books.Title", "books.publicationYear"])
        ->orderBy(["BorrowCount"], false) 
        ->limit($limit)
        ->execute(true);
    }
}

?>