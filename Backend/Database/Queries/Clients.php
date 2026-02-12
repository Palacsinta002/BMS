<?php

namespace Database\Queries;

class Clients extends Table{
    public static function all(){
        return self::select(["Clients"],["id", "name", "phone", "address"])->execute(true);
    }
    public static function show($userID, $exec = true){
        return self::select(["Clients"], ["*"])->where(["ID"],["="],[$userID],["i"])->execute($exec);
    }
    public static function store($values){
        self::insert("Clients",["name", "phone", "address"], array_values($values),["s", "i", "s"])->execute(false);
    }

    public static function dropByID($userID) {
        self::delete("Clients")
        ->where(["ID"],["="],[$userID],["i"])->execute(false);
    }

    public static function modify($userID, $fields, $values, $types){
        self::update("Clients",$fields, $values, $types)
        ->where(["ID"],["="],[$userID],["i"])->execute(false);
    }

    public static function destroy($userID) {
        self::delete("Clients")->where(["ID"], ["="], [$userID], ["i"])->execute(false);
    }
}


?>