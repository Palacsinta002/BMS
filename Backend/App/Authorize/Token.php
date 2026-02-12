<?php
/**
 * Token.php
 * 
 * Ez a fájl a `Token` osztályt definiálja, amely a JSON Web Tokenek (JWT) létrehozására és érvényesítésére szolgáló módszereket biztosít.
 * A felhasználói hitelesítéshez és autorizációhoz használatos az alkalmazásban.
 * 
 * Funkciók:
 * - `makeToken($userid, $username, $expireTime = 3600)`: Generál egy JWT-t egy felhasználó számára a megadott lejárati idővel.
 * - `verifyToken($token)`: Érvényesíti a JWT-t és kinyeri a felhasználói azonosítót belőle.
 * 
 * Használat:
 * - Használja a `makeToken()` metódust az autentikált felhasználók számára történő tokenek generálásához.
 * - Használja a `verifyToken()` metódust a tokenek validálásához és a felhasználói információk lekéréséhez védett útvonalakhoz.
 * 
 * Függőségek:
 * - `Firebase\JWT\JWT` és `Firebase\JWT\Key`: A JWT-k kódolására és dekódolására használt osztályok.
 * - `Config\Env`: Betölti a környezeti változókat, beleértve a JWT titkos kulcsot.
 * - `ApiResponse\Response`: HTTP hibaválaszok küldése, ha a token érvényesítése nem sikerül.
 * 
 * Megjegyzések:
 * - A tokeneket az HS256 algoritmus és egy titkos kulcs segítségével írják alá, amely a környezeti változókban tárolódik.
 * - Az `iss` állítás az alkalmazás alap URL-jére van állítva, és az `exp` állítás meghatározza a token lejárati idejét.
 */

namespace App\Authorize;
use Firebase\JWT\JWT;
use Config\Env;
use ApiResponse\Response;
use Firebase\JWT\Key;
class Token{

    public static function makeToken($userid,$username, $expireTime = 3600){
        Env::load();
        $payload = [
            "sub"=> $username,
            "iss" => "http://localhost/5173",
            "iat" => time(),
            "exp" => time() + $expireTime,
            "userID"=> $userid,
        ];
        return JWT::encode($payload, $_ENV["JWT_KEY"], "HS256");
    }
    public static function verifyToken($token){
        try {
            if (strpos($token, 'Bearer ') == 0) {
                $token = substr($token, 7);
            }
            Env::load();
            $token = JWT::decode($token,new Key($_ENV["JWT_KEY"],"HS256"));
            return $token->userID;
            
        }
        catch(\Exception $e){
            Response::httpError(400, 31);
        
        }
    }
}


?>