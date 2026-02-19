<?php

namespace App\Controllers;


use ApiResponse\Response;
use App\Authorize\Token;
use Database\Queries\Accounts;
use Helper\Helper;

use App\Validations\Model;
use Database\Queries\Clients;

class AccountController {

    /**
     * POST
     * AccountNumber, password
     * @param array $body
     * @return void
     */

    public static function login($body)  {
        $body = Helper::validateTheInputArray($body);
        if (!($body = Model::checkRequiredData($body,["account_number", "password"]))){
            Response::httpError(400, 3);
        }
        $user = Accounts::show($body["account_number"], false);
        if (!$user->rowCount()){
            Response::httpError(400, 3);    
        }
        $user = $user->fetchAll(\PDO::FETCH_ASSOC)[0];
        if (!password_verify($body["password"], $user["password"])){
            Response::httpError(400, 3);
        };

        Response::httpSuccess(200, ["Token" => Token::makeToken($user["account_number"])]);
    }

    public static function register($body) {
        $body = Helper::validateTheInputArray($body);
        if (!($body = Model::checkRequiredData($body, ["client_id", "balance", "limitation", "notifications", "password"]))){
            Response::httpError(400, "wrong data");
        }
        $body["account_number"] = Accounts::maxAccountNumber()+1;
        $body["password"] = password_hash($body["password"], PASSWORD_BCRYPT);
        
        
        Accounts::register(array_values($body));
        Response::httpSuccess(200, ["Success" => "registered"]);
    }

    public static function show($id) {
        $user = Accounts::show($id,false);
        if (!$user->rowCount()) {Response::httpError(400, 2);}
        Response::httpSuccess(200, $user->fetchAll(\PDO::FETCH_ASSOC));
    }

    public static function all($body) {
        Response::httpSuccess(200, Accounts::all());
    }

    public static function update($body, $id) {
        $user = Accounts::show($id);
        $user->rowCount() ? $user = $user->fetchAll(\PDO::FETCH_ASSOC)[0] : Response::httpError(400, "wrong user");
        unset($user["token"]);
        $body = Helper::validateTheInputArray($body);
        isset($body["password"]) ? $body["password"] = password_hash($body["password"], PASSWORD_BCRYPT) : "";
        
        $modifiedAccount = helper::overrideDictElements($user,$body);
        
        Accounts::modify(array_keys($modifiedAccount), array_values($modifiedAccount), $id);
        Response::httpSuccess(200, ["Success" => "modified row"]);

    }

    public static function destroy($accountID) {
        $client = Accounts::show($accountID);

        if (!$client->rowCount()){
            Response::httpError(400, 3);
        }
        accounts::destroy($accountID);
        Response::httpSuccess(200, ["Success" => "account deleted"]);
    }

}



?>