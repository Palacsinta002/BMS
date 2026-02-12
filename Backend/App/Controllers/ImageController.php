<?php
/**
 * ImageController.php
 * 
 * Ez a fájl az `ImageController` osztályt definiálja, amely a képfeltöltésekkel és lekérésekkel kapcsolatos HTTP kéréseket kezeli.
 * Validálja a képfájlokat, kezeli a feltöltéseket, és kiszolgálja a képeket a szerverről.
 * 
 * Funkciók:
 * - `uploadImg()`: Kezeli egy kép fájl feltöltését, validálja azt, és áthelyezi a kijelölt könyvtárba.
 * - `getImg($img)`: Lekéri egy kép fájlt az azonosítója alapján, validálja, és kiszolgálja a kliensnek. Ha a kép hiányzik, egy alapértelmezett képet szolgáltat.
 * 
 * Használat:
 * - Használja az `uploadImg()`-t a képfeltöltések kezelésére a kliensektől.
 * - Használja a `getImg()`-t képek lekérésére és kiszolgálására a klienseknek.
 * 
 * Függőségek:
 * - `App\Validations\Image`: A képfájlok validálására szolgál.
 * - `ApiResponse\Response`: HTTP válaszok küldésére használatos, ha a validálás vagy a fájl műveletek hibát okoznak.
 * 
 * Megjegyzések:
 * - A feltöltött képek a `booksImages` könyvtárban kerülnek tárolásra.
 * - Ha egy kép nem található, egy alapértelmezett "hiányzó" képet szolgáltat.
 */

namespace App\Controllers;

use App\Validations\Image;
use ApiResponse\Response;
class ImageController {
    public static function uploadImg(){
        $img = $_FILES["image"] ?? Response::httpError(400,16);
        Image::validateImage($img);
        move_uploaded_file($img["tmp_name"], __DIR__ . "/../../booksImages". "/" .basename($img["name"]));
        Response::httpSuccess(200, ["Success" => "Image uploaded"]);
    }
    public static function getImg($img){
        Image::validateISBN($img);
        $imgPath = __DIR__ . "/../../booksImages/". $img . ".";
        Image::checkForValidFile($imgPath);
        header('Content-Type: ' . mime_content_type(__DIR__ . "/../../booksImages/missing.png"));
        readfile(__DIR__ . "/../../booksImages/missing.png");
        die();
    }
}
?>