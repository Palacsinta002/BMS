<?php
/**
 * UserController.php
 * 
 * Ez a fájl a `UserController` osztályt definiálja, amely a felhasználókezeléssel kapcsolatos HTTP-kéréseket kezeli.
 * Kapcsolatot teremt a kliens és a `UserTable` adatbázis-lekérdezések között, elvégzi a bemeneti adatok validálását,
 * hitelesítést, és megfelelő válaszokat ad vissza.
 * 
 * Funkciók:
 * - `login()`: Felhasználó hitelesítése felhasználónév és jelszó alapján, majd token visszaadása.
 * - `verifyUser()`: Felhasználói token ellenőrzése.
 * - `register()`: Új felhasználó regisztrálása, adatok validálása és megerősítő e-mail küldése.
 * - `verifyAccount()`: Felhasználói fiók megerősítése egy megerősítő kód segítségével.
 * - `allUsers()`: A felhasználók számának lekérdezése az adatbázisból.
 * - `userData()`: Felhasználói adatok lekérdezése azonosító alapján.
 * - `updateUser()`: Felhasználói adatok frissítése azonosító alapján validálás után.
 * - `deleteUser()`: Felhasználó törlése azonosító alapján.
 * - `forgotPassword()`: Jelszó-visszaállító e-mail küldése a felhasználónak.
 * - `changePassword()`: Jelszó megváltoztatása validálás után.
 * - `finalizeRegistration()`: Regisztráció véglegesítése e-mail frissítésével és megerősítő e-mail küldésével.
 * 
 * Használat:
 * - Ezt a kontrollert használd felhasználókkal kapcsolatos műveletekhez, mint például bejelentkezés,
 *   regisztráció, profilfrissítés vagy jelszókezelés.
 * - A bemeneti adatokat a `Helper` és `User` validáló osztályok ellenőrzik, mielőtt adatbázis-lekérdezés történik.
 * 
 * Függőségek:
 * - `ApiResponse\Response`: HTTP válaszok küldésére szolgál.
 * - `Database\Queries\UserTable`: A felhasználókkal kapcsolatos adatbázis-lekérdezési metódusokat biztosítja.
 * - `Helper\Helper`: Bemeneti adatok validálására szolgál.
 * - `App\Validations\User`: Felhasználókkal kapcsolatos speciális validálási metódusokat tartalmaz.
 * - `App\Authorize\Token`: Tokenek generálásáért és ellenőrzéséért felelős.
 * - `Emailer\SendEmail` és `Emailer\EmailBodies`: E-mailek küldésére és tartalomgenerálásra szolgál.
 */

namespace App\Controllers;
use App\Validations\User;
use Database\Queries\UserTable;
use Helper\Helper;
use App\Authorize\Token;
use ApiResponse\Response;
use Emailer\SendEmail;
use Emailer\EmailBodies;
class UserController{


    # login the user by username and password
    # return a token if the user is found in the database
    # if the user is not found, return an error message or values are missing
    public static function login($body){
        $body = Helper::validateTheInputArray($body);
        if (!($body = User::checkRequiredData($body,["username","password"]))){
            Response::httpError(400,21);
        }
        $id = User::loginAuth($body["username"], $body["password"]);
        Response::httpSuccess(200,["Token" => Token::makeToken($id,$body["username"])]);
        
    }

    public static function veifyUser(){
        $body = Helper::getPostBody();
        echo Token::verifyToken($body["token"]);
    }


    # register a new user
    # If the required data is not found, return an error message
    # If the email or username is already in use, return an error message
    # Validates the input data and checks if the password is strong enough
    # If the data is valid, insert the user into the database and send a verification email
    public static function register($body){
        
        # Check if the required data is found in the body
        $body = Helper::validateTheInputArray($body);
        if (!($body = User::checkRequiredData($body,["email","username","password","firstname","lastname","address","dateOfBirth"]))){
            Response::httpError(400,21);
        }

        # Validate the input data
        User::checkEmail($body["email"]);
        User::checkFirstLastName($body["firstname"]);
        User::checkFirstLastName($body["lastname"]);
        User::validateAddress($body["address"]);
        User::validateDateOfBirth($body["dateOfBirth"]);
        $body["password"] = User::checkPassword($body["password"]);
        User::checkUsername($body["username"]);
        # Sending email and inserting to the database
        $verificationCode = User::createAuthCode();
        $body["EmailVerificationCode"] = $verificationCode;
        UserTable::insertToUser($body);

        SendEmail::sendEmail($body["email"],"New user","Verification Email",EmailBodies::verifyEmail($verificationCode));
        Response::httpSuccess(200,["Success" =>"User created"]);
    }


    # Verify the user by checking the verification code
    # If the code is valid, update the user to verified in the database
    public static function verifyAccount($body){

        $body = Helper::validateTheInputArray($body);
        if (!($body = User::checkRequiredData($body,["verificationCode"]))){
            Response::httpError(400,21);
        }
        User::checkAuthCode($body["verificationCode"]);
        User::updateToVerified($body["verificationCode"]);
        
    }


    # Get number of all users in the database
    public static function allUsers(){
        Response::httpSuccess(200,UserTable::numberOfUsers());
    }
    
    # Get the given user data by ID
    public static function userData($body,$userID= null){
        $body = Helper::validateTheInputArray($body);
        User::validateID($userID);

        Response::httpSuccess(200,UserTable::selectUserData($userID));
    }


    # Update the user data by ID
    # If the required data is not found, return an error message
    # If the email or username is already in use, return an error message
    # Validates the input data and checks if the password is strong enough
    # If the data is valid, update the user in the database
    public static function updateUser($body,$userID = null){
        # If the required data is not found, return an error message
        $body = Helper::validateTheInputArray($body);
        $body = User::removeNullValues($body);
        if (!($body = User::checkRequiredData($body,[],["username","passwordOld","password","firstname","lastname","address","dateOfBirth"]))){
            Response::httpError(400,21);
        }

        # Validate the input data
        User::validateID($userID);
        if (!isset((($userCurentData = UserTable::selectUserData($userID))[0]))){
            Response::httpError(400,27);
        };
        $userCurentData= $userCurentData[0];
        $userPassword = UserTable::selectByUsername($userCurentData["username"])[0]["password"];
        User::callingValidateFunctions($body,["username"],User::class,"checkUsername");
        User::callingValidateFunctions($body,["firstname"],User::class,"checkFirstLastName");
        User::callingValidateFunctions($body,["lastname"],User::class,"checkFirstLastName");
        User::callingValidateFunctions($body,["password"],User::class,"checkPassword");
        User::callingValidateFunctions($body,["address"],User::class,"validateAddress");
        User::callingValidateFunctions($body,["dateOfBirth"],User::class,"validateDateOfBirth");
        if ((isset($body["passwordOld"]) && !isset($body["password"])) || (!isset($body["passwordOld"]) && isset($body["password"]))){
            Response::httpError(400,21);
        }
        if (isset($body["passwordOld"]) && isset($body["password"]) ){
            User::userPasswordIsMatch($body["passwordOld"],$userPassword);
            $body["password"] = password_hash($body["password"], PASSWORD_BCRYPT);
            unset($body["passwordOld"]);
        }
        # Update the user in the database
        $types = User::makeTypesArray($body,["username","password","firstname","lastname","address","dateOfBirth"],["s","s","s","s","s","s"]);
        UserTable::updateUserData($userID,array_keys($body),array_values($body),$types);
        Response::httpSuccess(200,["Success" =>"User updated"]);
        
    }

    # Delete the user by ID
    public static function deleteUser($userID){

        if (!isset((($userCurentData = UserTable::selectUserData($userID))[0]))){
            Response::httpError(400,27);
        };
        User::validateID($userID);
        UserTable::deleteUserRow($userID);
        Response::httpSuccess(200,["Success" => "User deleted"]);
    }


    # Send a password reset email to the user
    # Validate the email and check if the user exists in the database
    # If the user exists, send a password reset email with a token
    public static function forgotPassword($body){
        $body = Helper::validateTheInputArray($body);
        if (!($body = User::checkRequiredData($body,["email"]))){
            Response::httpError(400,21);
        }
        User::validateEmail($body["email"]);
        if (($user = UserTable::selectByEmail($body["email"],false))->num_rows == 0){
            Response::httpError(400,0);
        };
        $data = $user->fetch_assoc();
        $token = Token::makeToken($data["id"],$data["username"],900);
        SendEmail::sendEmail($body["email"],$data["username"],"Forgot password",EmailBodies::forgotPassword($data["username"],$token));
        Response::httpSuccess(200,["Success"=> "Email sent"]);

    }


    # Change the password of the user by ID
    # Validate the password and check if the user exists in the database
    # If the user exists, update the password in the database
    # If the password is not strong enough, return an error message
    public static function changePassword($body,$userID){
        $body = Helper::validateTheInputArray($body);
        if (!($body = User::checkRequiredData($body,["password","passwordAgain"]))){
            Response::httpError(400,21);
        }
        $newPasswordHash = User::checkPassword($body["password"],$body["passwordAgain"]);
        UserTable::changePassword($newPasswordHash,$userID);
        Response::httpSuccess(200,["Success"=> "Password updated"]);
    }

    
    # Finalize the registration of the user by giving the email, username and password
    # Validate the email and check if the user exists in the database
    # If the user gave the correct email, username and password, update the email
    public static function finalizeRegistration($body){
        $body = Helper::validateTheInputArray($body);
        
        if (!($body = User::checkRequiredData($body,["email","username","password"]))){
            Response::httpError(400,21);
        }
        
        User::checkEmail($body["email"]);
        $id = User::loginAuth($body["username"],$body["password"],false);
        $user = UserTable::selectUserData($id)[0];
        if (isset($user["email"])){
            Response::httpError(400,34);
        }
   
        $verificationCode = User::createAuthCode();
        SendEmail::sendEmail($body["email"],"New user","Verification Email",EmailBodies::verifyEmail($verificationCode));
        UserTable::updateUserData($id,["email","EmailVerificationCode"],[$body["email"],$verificationCode],["s","i"]);
        Response::httpSuccess(200,["Success"=> "Email sent"]);
    }
}