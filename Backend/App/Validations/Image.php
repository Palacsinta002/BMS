<?php
/**
 * Image.php
 * 
 * Ez a fájl az `Image` osztályt definiálja, amely validálási funkciókat biztosít a képfájlok kezelésére.
 * Biztosítja, hogy a feltöltött képek megfeleljenek bizonyos követelményeknek, mint például fájl típus, méret és létezés.
 * 
 * Funkciók:
 * - `validateImage($img)`: Validálja a feltöltött képfájlt a létezés, típus és méret szempontjából.
 * - `validateExtentions($fileType)`: Validálja a fájl típusát, és biztosítja, hogy az engedélyezett képformátumok egyike legyen (pl. PNG, JPG, JPEG).
 * - `checkForValidFile($imgPath)`: Ellenőrzi, hogy egy érvényes képfájl létezik-e a megadott elérési úton, és a helyes MIME típusú fájlt szolgáltatja.
 * 
 * Használat:
 * - Használja a `validateImage()` metódust a feltöltött képek validálására a fájl feltöltési folyamatok során.
 * - Használja a `checkForValidFile()` metódust képfájlok kiszolgálására a szerverről, ha léteznek.
 * 
 * Függőségek:
 * - `ApiResponse\Response`: HTTP hibás válaszok küldésére szolgál, amikor a validálás nem sikerül.
 * 
 * Megjegyzések:
 * - A maximálisan engedélyezett kép mérete 70 MB.
 * - Támogatott képformátumok: PNG, JPG, és JPEG.
 */

namespace App\Validations;

use ApiResponse\Response;
class Image extends Model{
    public static function validateImage($img){
        if (empty($img['tmp_name']) || !file_exists($img["tmp_name"])) {
            Response::httpError(400, 16);
        }

        $imageInfo = getimagesize($img["tmp_name"]);
        if ($imageInfo == false) {
            Response::httpError(400,28);
        }
        
        $fileType = explode("/", $img["type"]);
        self::validateExtentions($fileType);
        if ($img["size"] > 70 * 1024 * 1024){
            Response::httpError(400,28);
        }
        return true;
           
    }

    public static function validateExtentions($fileType){
        $allowedTypes = ["png", "jpg", "jpeg"];
        if ($fileType[0] != "image" || !in_array($fileType[1], $allowedTypes)){
            Response::httpError(400,28);
        }
    }
    public static function checkForValidFile($imgPath){
        $allowedTypes = ["png", "jpg", "jpeg"];
        foreach ($allowedTypes as $extention) {
            if (file_exists($imgPath . $extention)) {
                header('Content-Type: ' . mime_content_type($imgPath . $extention));
                readfile($imgPath . $extention);
                die();
            }
        }
    }
}

?>