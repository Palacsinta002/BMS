<?php

namespace App\Controllers;

use ApiResponse\Response;
use Database\Queries\Clients;
use Helper\Helper;
use App\Validations\Model;

class ClientController{
    public static function all(){
        $result = Clients::all();
        Response::httpSuccess(200, $result);
    }
    
    //post
    public static function store($body){
        $body = Helper::validateTheInputArray($body);
        if (!($body = Model::checkRequiredData($body, ["name", "phone", "address"]))){
            Response::httpError(200,0);
        }
        Clients::store($body);
        Response::httpSuccess(200, ["Success" => "inserted row"]);

    }
    
    public static function modify($body, $id){
        $user = Clients::show($id,false);
        if (!$user->rowCount()){
            Response::httpError(400, 2);
        };
        $modifiedUser = Helper::overrideDictElements($user, $body);

        Clients::modify($id,array_keys($modifiedUser),array_values($modifiedUser), ["s","i","s"]);
        Response::httpSuccess(200, ["Success" => "modified row"]);
    }

    public static function delete($id)  {
        if (!Clients::show($id,false)->rowCount()){
            Response::httpError(400, 2);
        }
        clients::destroy($id);
        Response::httpSuccess(200, ["Success" => "row deleted"]);
    }
}

?>