<?php

namespace Database\Queries;

class Accounts extends Table {

    public static function show($accountNumber, $fetch = false) {
        return self::select(["accounts"],["*"])->where(["account_number"],["="],[$accountNumber],["i"])->execute(true, $fetch);
    }

    public static function all() {
        return self::select(["accounts"], ["*"])->execute(true);
    }

    public static function destroy($accountID) {
        self::delete("accounts")->where(["account_number"],["="],[$accountID],["i"])->execute(false);
    }

    public static function maxAccountNumber(){
        return self::select(["accounts"], ["max(\"account_number\") as account_number"])->execute(true)[0]["account_number"];
    }

    public static function register($values){
        self::insert("accounts", ["client_id", "balance", "limitation", "notifications", "password","account_number"],$values, ["i","i","i","","s"])->execute(false);
    }

    public static function modify($fields, $values, $id) {
        self::update("accounts", $fields, $values,  ["i", "i", "i", "i", "i","s"])->where(["account_number"], ["="], [$id], ["i"])->execute(false);
    }

}

?>