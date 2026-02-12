<?php
/**
 * UserTable.php
 * 
 * Ez a fájl a `UserTable` osztályt definiálja, amely a `Table` osztályból származik,
 * és kifejezetten a felhasználókkal kapcsolatos adatok kezelésére és lekérdezésére biztosít metódusokat.
 * 
 * Funkciók:
 * - `selectByUsername()`: Felhasználó adatainak lekérdezése felhasználónév alapján.
 * - `rowExists()`: Annak ellenőrzése, hogy létezik-e adott feltételeknek megfelelő sor a `users` táblában.
 * - `updateToVerified()`: Felhasználó email megerősítési állapotának frissítése.
 * - `insertToUser()`: Új felhasználó beszúrása a `users` táblába.
 * - `numberOfUsers()`: Felhasználók számának lekérdezése az adatbázisból.
 * - `selectUserData()`: Részletes felhasználói adatok lekérdezése, beleértve a szerepkör-információkat is.
 * - `updateUserData()`: Egy adott felhasználó adatainak frissítése.
 * - `deleteUserRow()`: Felhasználó törlése a `users` táblából.
 * - `selectByEmail()`: Felhasználói adatok lekérdezése email-cím alapján.
 * - `changePassword()`: Felhasználó jelszavának frissítése.
 * - `allByID()`: Összes felhasználói adat lekérdezése felhasználóazonosító alapján.
 * 
 * Használat:
 * - Ezekkel a metódusokkal lehet kapcsolatba lépni a `users` táblával, például hitelesítés, regisztráció,
 *   profilfrissítés és fiókkezelés céljából.
 * - A `selectUserData()` és `updateUserData()` metódusok támogatják az összetett lekérdezéseket join-okkal és frissítésekkel.
 * 
 * Függőségek:
 * - A `Table` osztályból származik, amely az alap lekérdezésépítési funkciókat biztosítja.
 * - A `Helper` osztályt használja a bemeneti adatok validálására.
 */

namespace Database\Queries;

use Helper\Helper;

class UserTable extends Table
{
    public static function selectByUsername($username, $fetch = true)
    {
        $username = Helper::validateTheInput($username);
        return self::select(["users"], [
            "id", 
            "username", 
            "password", 
            "EmailVerified", 
            "email"])
            ->where(["username"], ["="], [$username], ["s"])
            ->execute(true, $fetch);
    }

    public static function rowExists($field, $operator, $value, $type)
    {
        $validatedField = Helper::validateTheInputArray($field);
        $validatedOperator = Helper::validateTheInputArray($operator);
        $validatedValue = Helper::validateTheInputArray($value);
        $result = self::select(["users"], ["id"])
            ->where($validatedField, $validatedOperator, $validatedValue, $type)
            ->execute(true, false);

        if ($result->num_rows == 0) {
            return false;
        }
        return true;
    }

    public static function updateToVerified($code)
    {
        $validatedCode = Helper::validateTheInput($code);
        self::update("users", ["Emailverified"], ["1"], ["i"])
        ->where(["EmailVerificationCode", "Emailverified"], ["=", "="], [$code, "0"], ["s", "i"])->execute(false);
        return true;
    }

    public static function insertToUser($value)
    {
        $validatedCode = Helper::validateTheInputArray($value);
        self::insert(
            "users",
            [
                "email", 
                "username", 
                "password", 
                "firstname", 
                "lastname", 
                "EmailVerificationCode", 
                "Address", 
                "DateOfBirth",
                "RoleID"],
            [$validatedCode["email"], 
            $validatedCode["username"], 
            $validatedCode["password"], 
            $validatedCode["firstname"], 
            $validatedCode["lastname"], 
            $validatedCode["EmailVerificationCode"], 
            $validatedCode["address"],
            $validatedCode["dateOfBirth"],
            "3"],
            ["s", "s", "s", "s", "s", "s", "s","s", "i"]
        )->execute(false);
        return true;
    }

    public static function numberOfUsers()
    {
        return self::select(["users"], ["Count(id) AS 'users'"])->execute(true);
    }

    public static function selectUserData($ID)
    {
        return self::select(["users"], [
            "users.email", 
            "users.username", 
            "users.firstname", 
            "users.lastname", 
            "users.lastname", 
            "users.address", 
            "users.DateOfBirth",
            "Roles.Role"])
            ->innerJoin("Roles", ["users.RoleID"], ["="], ["Roles.ID"])
            ->where(["users.ID"], ["="], [$ID], ["i"])->execute(true);
    }

    public static function updateUserData($ID, $modifyFields, $modifyValues, $types)
    {
        if (empty($modifyFields) || (is_array($modifyFields) && count($modifyFields) == 0)) {
            return false;
        }
        self::update("users", $modifyFields, $modifyValues, $types)
        ->where(["id"], ["="], [$ID], ["i"])->execute(false);
    }

    public static function deleteUserRow($ID)
    {
        self::delete("users")->where(["id"], ["="], [$ID], ["i"])->execute(false);
    }

    public static function selectByEmail($email, $fetch = true)
    {
        return self::select(["users"], ["id", "username"])
        ->where(["email"], ["="], [$email], ["s"])->execute(true, $fetch);
    }

    public static function changePassword($password, $userID)
    {
        return self::update("users", ["password"], [$password], ["s"])
        ->where(["ID"], ["="], [$userID], ["i"])->execute(false, false);
    }
    
    public static function allByID($ID)
    {
        return self::select(["users"], ["*"])
        ->where(["ID"], ["="], [$ID], ["i"])->execute(true);
    }
}
