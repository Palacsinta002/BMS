<?php
/**
 * User.php
 * 
 * Ez a fájl a `User` osztályt definiálja, amely validálási metódusokat biztosít a felhasználókkal kapcsolatos adatok számára.
 * Biztosítja a felhasználói adatok integritását és helyességét, például jelszavak, felhasználónevek, e-mailek és egyéb felhasználói attribútumok esetén.
 * Ezen kívül kezel autentikációs és megerősítési logikát is.
 * 
 * Funkciók:
 * - `checkPassword()`: Validálja és hasheli a jelszót, biztosítva, hogy megfeleljen a biztonsági követelményeknek.
 * - `checkUsername()`: Validálja a felhasználónevet, és ellenőrzi annak egyediségét az adatbázisban.
 * - `checkEmail()`: Validálja az e-mail címet, és ellenőrzi annak egyediségét az adatbázisban.
 * - `checkFirstLastName()`: Validálja a kereszt- és családi neveket a megfelelő formázás érdekében.
 * - `loginAuth()`: Hitelesíti a felhasználót a felhasználónév és jelszó ellenőrzésével.
 * - `createAuthCode()`: Generál egy 6 számjegyű hitelesítő kódot.
 * - `checkAuthCode()`: Validálja a hitelesítő kódot az e-mail megerősítése érdekében.
 * - `updateToVerified()`: A felhasználót megerősítettre jelöli a sikeres e-mail megerősítés után.
 * - `userPasswordIsMatch()`: Ellenőrzi, hogy a megadott jelszó megegyezik-e az adatbázisban tárolt hash-elt jelszóval.
 * - `validateAddress()`: Validálja a felhasználói címet a megfelelő formázás és hosszúság érdekében.
 * - `validateEmail()`: Validálja az e-mail cím formátumát.
 * - `validateDateOfBirth()`: Validálja a felhasználó születési dátumát, hogy megfeleljen az életkori követelményeknek.
 * - `isUserVerified()`: Ellenőrzi, hogy a felhasználó megerősítve van-e az e-mail cím vagy a fiók státusza alapján.
 * 
 * Használat:
 * - Használjuk ezeket a metódusokat a felhasználói adatok validálására regisztráció, bejelentkezés és profilfrissítés során.
 * - Az olyan metódusok, mint a `loginAuth()` és `checkAuthCode()`, alapvetőek az autentikációs és megerősítési folyamatokban.
 * 
 * Függőségek:
 * - `ApiResponse\Response`: HTTP hibás vagy sikeres válaszok küldésére szolgál.
 * - `Helper\Helper`: Bemeneti adatok validálására szolgáló segédfunkciók biztosítása.
 * - `Database\Queries\UserTable`: Adatbázis-interakciók a felhasználói adatok ellenőrzésére és frissítések végrehajtására.
 */

namespace App\Validations;
use ApiResponse\Response;
use Helper\Helper;
use Database\Queries\UserTable;

class User extends Model{

    public static function checkPassword($password,$password2 = null){
        if ($password2 == null){
            $password2 = $password;
        }
        if ($password != $password2){
            Response::httpError(400,15);
        }
        if ((strlen($password < 8) || strlen($password) > 16) || !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*[\d\W]).+$/', $password)){
            Response::httpError(400,6);
        }
        return password_hash(Helper::validateTheInput($password), PASSWORD_BCRYPT) ;  
    }

    public static function checkUsername($username){
        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9._]{2,19}$/', $username)){
            Response::httpError(400,3);
        }
        if (UserTable::rowExists(["username"],["="],[$username],["s"])){
            Response::httpError(400,22);
        }
        return true;
    }

    public static function checkEmail($email){
        $email = Helper::validateTheInput($email);
        
        self::validateEmail($email);
        if (UserTable::rowExists(["email"],["="],[$email],["s"])) {
            Response::httpError(400,1);
        }
        return true;
    }

    public static function checkFirstLastName($name){
        if (!preg_match("/^[A-ZÁRVÍZTŰRŐTÜKÖRFÚRÓGÉP][a-záéíóöőúüű\-']*$/u",$name) || (strlen($name) < 3 || strlen($name) > 100)){
            Response::httpError(400,4);
        }
        return true;
    }
    public static function loginAuth($username,$password, $hasEmail = true){
        if (($selectResult = UserTable::selectByUsername($username,false) )->num_rows == 0){
            Response::httpError(400,7);
        }
        $data = $selectResult->fetch_assoc();
        self::userPasswordIsMatch($password,$data["password"]);
        if ($hasEmail == true){
            if ($data["EmailVerified"] == 0){
                Response::httpError(400,7);
            }
        }
        return $data["id"];
        

    }
    public static function createAuthCode(){
        $code = "";
        for ($i = 0; $i < 6; $i++) {
            $code .= rand(0,9);
        }
        return $code;
    }
    public static function checkAuthCode($code){
        $result = UserTable::rowExists(["EmailVerificationCode","Emailverified"],["=","="],[$code,"0"],["s","i"]);
        if (!$result){
            Response::httpError(400,11);
        }
        return $result;
    }
    public static function updateToVerified($code){
        UserTable::updateToVerified($code);
        Response::httpSuccess(200,["Success" =>"User verified"] );
    }
    
    public static function userPasswordIsMatch($loginPassword,$DBPassword){
        if (!password_verify($loginPassword,$DBPassword)){
            Response::httpError(400,7);
        }
        return true;
      } 

    public static function validateAddress($address){
        if (!preg_match('/^[A-ZÁÉÍÓÖŐÚÜŰa-záéíóöőúüű0-9\s.,\-\/#°\'0-9]+$/u', $address)) {
            Response::httpError(400,30);
        }
    

        if (strlen($address) < 5 || strlen($address) > 100) {
            Response::httpError(400,30);
        }
        return true;
    }
    public static function validateEmail($email){
        if (!preg_match("/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/", $email)) {
            Response::httpError(400,0);
        }
    
    }
    public static function validateDateOfBirth($date) {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            Response::httpError(400,35); 
        }
    
        $dob = \DateTime::createFromFormat('Y-m-d', $date);
        if (!$dob || $dob->format('Y-m-d') !== $date) {
            Response::httpError(400,35); 
        }
    
        $today = new \DateTime();
        $minDate = $today->modify('-14 years');
    
        return $dob <= $minDate;
    }
    //halooo ezt lehet ki kéne venni
    public static function isUserVerified($ID,$emailVerified = null, $verified = null){
        $user = UserTable::allByID($ID)[0];
        if (isset($emailVerified) && !isset($verified)){
            if ($user["EmailVerified"] == 0){
                return false;
            }
            return true;
        }
        elseif(isset($verified) && !isset($emailVerified)){
            if ($user["Verified"] == 0){
                return false;
            }
            return true;
        }
        elseif(isset($verified) && isset($emailVerified)){
            $returnArray = [];
            if ($user["EmailVerified"] == 1){
                $returnArray["email"] = true;
            }
            if ($user["Verified"] == 1){
                $returnArray["verified"] = true;
            }
            return $returnArray;
        }
        return null;
        
    }
}
?>