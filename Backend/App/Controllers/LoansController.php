<?php

namespace App\Controllers;

use ApiResponse\Response;
use App\Validations\Model;
use Database\Queries\Loans;
use Helper\Helper;

class LoansController{

    public static function all() {
        Response::httpSuccess(200, Loans::all(true));
    }

    public static function show($id) {
        Response::httpSuccess(200, Loans::show($id, true));
    }
    
    public static function store($body) {
        
    }
    public static function modify($body, $id) {
        $body = Helper::validateTheInputArray($body);
        $body = Model::checkRequiredData($body, ["id"], ["account_id", "amount", "left_amount", "amount_of_installment", "date_of_start", "date_of_end"]);
        if (!$body){
            Response::httpError(400, "wrong data");
        }
        $row = Loans::one($body["id"], false);
        if (!$row->rowCount()){
            Response::httpError(400, "no such row exists");
        }
        $modifiedRow = Helper::overrideDictElements($row, $body);

        Loans::modify(array_keys($modifiedRow), array_values($modifiedRow), $modifiedRow[$id]);

    }
}


?>