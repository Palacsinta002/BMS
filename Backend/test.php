<?php


$conn = new PDO("pgsql:host=localhost port=5432 dbname=BMS", "bms", "bms_user123");

if (!$conn){
    echo "anyád";
    exit;
    }
?>