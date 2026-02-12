<?php
/**
 * BooksTable.php
 * 
 * Ez a fájl a `BooksTable` osztályt definiálja, amely a `Table` osztályból származik, és
 * kifejezetten a könyvekkel kapcsolatos adatok kezelésére és lekérdezésére biztosít metódusokat.
 * 
 * Funkciók:
 * - `selectByParams()`: Könyvek lekérdezése megadott mezők, operátorok, értékek és típusok alapján.
 * - `countAllBooks()`: Az adatbázisban található összes könyv megszámlálása.
 * - `selectByISBN()`: Egy könyv adatainak lekérdezése ISBN alapján.
 * - `availableBooks()`: Egy adott ISBN-hez tartozó könyv elérhetőségének ellenőrzése.
 * - `selectAvailableBooks()`: Azoknak a könyveknek a listázása, amelyek jelenleg elérhetők (nincsenek kikölcsönözve vagy lefoglalva).
 * 
 * Használat:
 * - Ezekkel a metódusokkal lehet kapcsolatba lépni a `books` táblával és a hozzá kapcsolódó táblákkal (pl. `publishers`, `authors`, `categories`).
 * - A `selectByParams()` és `selectAvailableBooks()` metódusok támogatják az összetett lekérdezéseket join-olással és csoportosítással.
 * 
 * Függőségek:
 * - A `Table` osztályból származik, amely az alap lekérdezésépítési funkciókat biztosítja.
 * - Használja a kapcsolódó adatbázistáblákat, mint például: `books`, `publishers`, `authors`, `categories`, `borrowings` és `reservations`.
 */
namespace Database\Queries;

class BooksTable extends Table{
    public static function selectByParams($fields =[], $operators = [], $values = [],$types = []){
        if (count($fields) == 0) {
            return self::select(["books"],[
                "books.ISBN",
                "publishers.Publisher",
                "books.Title","GROUP_CONCAT(DISTINCT Authors.Author SEPARATOR ',') as 'Authors'",
                "GROUP_CONCAT(DISTINCT categories.category SEPARATOR ',') as 'Category'","books.publicationYear"
                ])
            ->innerJoin("Publishers",["Publishers.ID"],["="],["books.PublisherID"])
            ->innerJoin("Books_authors",["Books.ISBN"],["="],["Books_Authors.ISBN"])
            ->innerJoin("Authors",["Books_Authors.AuthorID"],["="],["Authors.ID"])
            ->innerJoin("Books_Categories",["Books.ISBN"],["="],["Books_Categories.ISBN"])
            ->innerJoin("Categories",["Books_Categories.CategoryID"],["="],["Categories.ID"])
            ->groupBy(["Books.Title", "Books.PublicationYear", "Publishers.Publisher", "Books.ISBN"])->execute(true);
        }
        return self::select(["books"],[
            "books.ISBN",
            "publishers.Publisher",
            "books.Title",
            "GROUP_CONCAT(DISTINCT Authors.Author SEPARATOR ',') as 'Authors'",
            "GROUP_CONCAT(DISTINCT categories.category SEPARATOR ',') as 'Category'","books.publicationYear"
            ])
        ->innerJoin("Publishers",["Publishers.ID"],["="],["books.PublisherID"])
        ->innerJoin("Books_authors",["Books.ISBN"],["="],["Books_Authors.ISBN"])
        ->innerJoin("Authors",["Books_Authors.AuthorID"],["="],["Authors.ID"])
        ->innerJoin("Books_Categories",["Books.ISBN"],["="],["Books_Categories.ISBN"])
        ->innerJoin("Categories",["Books_Categories.CategoryID"],["="],["Categories.ID"])
        ->where($fields,$operators,$values,$types)
        ->groupBy(["Books.Title", "Books.PublicationYear", "Publishers.Publisher", "Books.ISBN"])->execute(true);
    }
    public static function countAllBooks(){
        return self::select(["books"],["count(ISBN) AS books"])->execute(true);
    }
    public static function selectByISBN($ISBN, $fetch = false){
        return self::select(["books"],["ISBN"])->where(["ISBN"],["="],[$ISBN],["i"])->execute(true, $fetch);
    }
    public static function availableBooks($ISBN, $fetch = false){
        return self::select(["books"],["Available"])->where(["ISBN"],["="],[$ISBN],["i"])->execute(true,$fetch);
    }
    public static function selectAvailableBooks(){

        $subQueryBorrowed = self::select(["borrowings"], ["ISBN"])
            ->where(["returnDate"], ["IS"], ["NULL"], ["i"])
            ->toString("borrowed_books");

        $subQueryReserved = self::select(["reservations"], ["ISBN"])
            ->toString("reserved_books");

        return self::select(["books"], [
                "books.ISBN",
                "publishers.Publisher",
                "books.Title",
                "GROUP_CONCAT(DISTINCT Authors.Author SEPARATOR ',') as 'Authors'",
                "GROUP_CONCAT(DISTINCT categories.category SEPARATOR ',') as 'Category'",
                "books.publicationYear"
            ])
            ->innerJoin("Publishers", ["Publishers.ID"], ["="], ["books.PublisherID"])
            ->innerJoin("Books_authors", ["Books.ISBN"], ["="], ["Books_Authors.ISBN"])
            ->innerJoin("Authors", ["Books_Authors.AuthorID"], ["="], ["Authors.ID"])
            ->innerJoin("Books_Categories", ["Books.ISBN"], ["="], ["Books_Categories.ISBN"])
            ->innerJoin("Categories", ["Books_Categories.CategoryID"], ["="], ["Categories.ID"])
            ->leftJoin($subQueryBorrowed, ["books.ISBN"], ["="], ["borrowed_books.ISBN"])
            ->leftJoin($subQueryReserved, ["books.ISBN"], ["="], ["reserved_books.ISBN"])
            ->where(["borrowed_books.ISBN","reserved_books.ISBN"], ["IS","IS"], ["NULL","NULL"], ["i","i"])
            ->groupBy(["books.ISBN", "publishers.Publisher", "books.Title", "books.publicationYear"])
            ->execute(true);
    }
}
?>