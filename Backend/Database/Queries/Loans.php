<?php

namespace Database\Queries;

class Loans extends Table {

    public static function all($fetch) {
         return self::select(["loans"], ["*"])->execute(true, $fetch);
    }
    public static function show($id, $fetch){
        return self::select(["loans"], ["*"])->where(["account_id"], ["="], [$id], ["i"])->execute(true, $fetch);
    }

    public static function one($id, $fetch){
        return self::select(["loans"], ["*"])->where(["id"], ["="], [$id], ["i"])->execute(true, $fetch);
    }

    public static function modify($fields, $values, $id){
        self::update("loans", $fields, $values,  ["i", "i", "i", "i", "i","s", "s"])->where(["account_id"], ["="], [$id], ["i"])->execute(false);
    }

}


?>