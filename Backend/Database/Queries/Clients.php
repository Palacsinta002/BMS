<?php

namespace Database\Queries;

class Clients extends Table{
    public static function all(){
        return self::select(["clients"],["id", "name", "phone", "address"])->execute(true);
    }
    public static function show($userID, $exec = true){
        return self::select(["clients"], ["*"])->where(["id"],["="],[$userID],["i"])->execute($exec);
    }
    public static function store($values){
        self::insert("clients",["name", "phone", "address"], array_values($values),["s", "i", "s"])->execute(false);
    }

    public static function dropByID($userID) {
        self::delete("clients")
        ->where(["id"],["="],[$userID],["i"])->execute(false);
    }

    public static function modify($userID, $fields, $values, $types){
        self::update("clients",$fields, $values, $types)
        ->where(["id"],["="],[$userID],["i"])->execute(false);
    }

    public static function destroy($userID) {
        self::delete("clients")->where(["id"], ["="], [$userID], ["i"])->execute(false);
    }
}


?>