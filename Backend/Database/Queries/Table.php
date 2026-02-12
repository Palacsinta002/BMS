<?php
/**
 * Table.php
 * 
 * Ez a fájl a `Table` osztályt definiálja, amely alapot biztosít az adatbázis-lekérdezések felépítéséhez.
 * Olyan metódusokat tartalmaz, amelyek lehetővé teszik SQL lekérdezések (pl. SELECT, INSERT, UPDATE, DELETE, JOIN)
 * dinamikus összeállítását és végrehajtását.
 * 
 * Funkciók:
 * - Lekérdezésépítés:
 *   - `select()`: SELECT lekérdezés összeállítása megadott mezőkkel és táblákkal.
 *   - `insert()`: INSERT lekérdezés összeállítása megadott mezőkkel és értékekkel.
 *   - `update()`: UPDATE lekérdezés összeállítása megadott mezőkkel és értékekkel.
 *   - `delete()`: DELETE lekérdezés összeállítása a megadott táblára.
 * - Lekérdezésmódosítók:
 *   - `where()`: WHERE feltételek hozzáadása operátorokkal, mint pl. `=`, `LIKE`, `IN`, `IS NULL`.
 *   - `groupBy()`: GROUP BY feltételek hozzáadása a lekérdezéshez.
 *   - `orderBy()`: ORDER BY feltételek hozzáadása növekvő vagy csökkenő sorrendben.
 *   - `limit()`: LIMIT feltétel megadása az eredmények számának korlátozására.
 *   - `innerJoin()` és `leftJoin()`: JOIN feltételek hozzáadása az adatok több táblából történő összekapcsolásához.
 * - Végrehajtás:
 *   - `execute()`: A felépített lekérdezés végrehajtása, opcionálisan az eredmények lekérésével.
 *   - `toString()`: A lekérdezés szöveges formátumba alakítása hibakereséshez vagy al-lekérdezésekhez.
 * - Egyéb lehetőségek:
 *   - Automatikus paraméter kötés előkészített utasításokhoz (prepared statements).
 *   - A lekérdezési állapot automatikus visszaállítása végrehajtás után, hogy az osztály újra felhasználható legyen.
 * 
 * Használat:
 * - Származtass ebből az osztályból konkrét táblákhoz tartozó lekérdező osztályokat (pl. `BooksTable`, `BorrowingsTable`).
 * - Használd a rendelkezésre álló metódusokat SQL lekérdezések dinamikus felépítésére és futtatására.
 * 
 * Függőségek:
 * - `Database\Connection`: Az adatbázis-kapcsolat létrehozására szolgál a lekérdezések végrehajtásához.
 */

namespace Database\Queries;
use Database\Connection;
class Table {
    protected static $query;
    protected static $values = [];
    protected static $types = [];

    protected function where( $field, $operator,$value, $type) {
        $sql = " Where ";
        for ($i=0; $i < count($field); $i++) { 
            if ($operator[$i] == "IN"){
                $sql .= $field[$i] . " IN " . $value[$i] ." AND ";
            }
            elseif ($value[$i] == "NULL" || $value[$i] == "NOT NULL"){
                $sql .= $field[$i] . " IS ". $value[$i] ." AND ";
                
            }
            elseif($operator[$i] == "LIKE"){
                $sql .= $field[$i] . " LIKE " . "'".$value[$i]."' AND ";
            }
            else{
                $sql .= $field[$i] . " " . $operator[$i] . " ? AND ";
            }
        }
        $sql = substr($sql,0,-4);
        self::$values = array_merge(self::$values,$value);
        self::$types = array_merge(self::$types,$type);
        self::$query .=$sql;
        return $this;
    }

    protected function limit($value) {
        self::$query .= " LIMIT $value ";
        return $this;
    }

    protected function groupBy( $field ) {
        self::$query .= " Group by  ". implode(", ", $field);
        return $this;
    }
    protected function innerJoin($table, $fields, $operators, $valueFields){
        $sql = " INNER JOIN $table ON ";
        for ($i= 0; $i < count($fields); $i++){
            $sql .= " " . $fields[$i] . " " . $operators[$i] . " " . $valueFields[$i] . "  AND ";
            
        }
        $sql = substr($sql,0,-4);
        self::$query .=$sql;
        return $this;
    }
    protected function leftJoin(string $table, array $fields, array $operators, array $valueFields){
        $sql = " LEFT JOIN $table ON ";
        for ($i= 0; $i < count($fields); $i++){
            $sql .= " " . $fields[$i] . " " . $operators[$i] . " " . $valueFields[$i] . "  AND ";
            
        }
        $sql = substr($sql,0,-4);
        self::$query .=$sql;
        return $this;
    }
    protected function orderBy(array $fields, $ASC = true){
        $sql = " ORDER BY  ". implode(", ", $fields);
        if ($ASC){
            $sql .= " ASC ";
        }
        else{
            $sql .= " DESC ";
        }
        self::$query .=$sql;
        return $this;
    }
    protected static function insert(string $table, array $field, array  $value, array $type) {
        $valueString = implode(", ", $field);
        $placeholder = implode(", ", array_fill(0, count($value), "?"));
        $sql = "INSERT INTO \"$table\" ($valueString) VALUES ($placeholder)";
        self::$values = $value;
        self::$types = $type;
        self::$query = $sql;
        return new self();
    }
    protected static function update(string $table, array $field, array $value, array $type,) {
        $sql = "UPDATE \"$table\" 
        SET " . implode(" = ? , ", $field) . " = ? " . " ";
        self::$query = $sql;
        self::$values = $value;
        self::$types = $type;
        return new self();
    }

    protected static function execute( bool $getresult, bool $fetch = true ){
        $conn = Connection::connect();
        self::fixingValues();
        //echo self::$query;
        //die();
            $query = $conn->prepare(self::$query);
            if (substr_count(self::$query,"?") > 0 ){
                $types = implode("", self::$types);
                
            }
            $query->execute([...self::$values]);
            if ($getresult == false) {
                self::reset();
                return;
            }
            if ($fetch == false) {
                self::reset();
                return $query;
            }
            self::reset();
            return $query->fetchAll(\PDO::FETCH_ASSOC);
        
    }
    protected static function select(array $tables,array $fields){
        self::$query = "SELECT " . implode(", ",$fields) . " FROM \"" . implode("\", \"",$tables) . "\" ";
        return new self();
    }
    protected static function delete($table){
        self::$query = "DELETE FROM $table";
        return new self();
    }
    private static function reset(){
        self::$query = "";
        self::$values = [];
        self::$types = [];
    }
    private static function fixingValues(){
        for ($i=0; $i < count(self::$values); $i++) { 
            if (self::$values[$i] == "NULL" || self::$values[$i] == "NOT NULL"){
                array_splice(self::$values,$i,1);
                array_splice(self::$types,$i,1);
            }
        }
    }
    protected static function toString($alias = null){
        self::fixingValues();
        for ($i=0; $i < count(self::$values); $i++) { 
            $replacement = self::$values[$i];
            if (self::$types[$i] == "s"){
                $replacement = "'" . $replacement ."'";
            }
            self::$query = preg_replace('/\?/', $replacement, self::$query, 1);
        }
        
        $stringSql = "(" . self::$query . ")";
        $stringSql = isset($alias) ? $stringSql . " AS $alias ": $stringSql;
        self::reset();
        return $stringSql;
    }
}

?>