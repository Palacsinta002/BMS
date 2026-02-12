<?php
/**
 * Router.php
 * 
 * Ez a fájl a `Router` osztályt definiálja, amely a HTTP kérések kezeléséért
 * és a megfelelő vezérlőkhöz (controller) való irányításáért felelős.
 * Támogatja a többféle HTTP metódust (GET, POST, PUT, DELETE), valamint biztosítja
 * a hitelesítést, fájlkezelést és az URL-ekből történő változók kinyerését.
 * 
 * Funkciók:
 * - `post()`: POST kérések kezelése egy adott végpontra.
 * - `get()`: GET kérések kezelése egy adott végpontra, opcionális URL-változó kinyeréssel.
 * - `put()`: PUT kérések kezelése egy adott végpontra.
 * - `delete()`: DELETE kérések kezelése egy adott végpontra.
 * - Hitelesítés: Token alapú hitelesítés támogatása védett útvonalakhoz.
 * - URL feldolgozás: Változók és adatok kinyerése az URL-ből dinamikus végpontokhoz.
 * - Segédfüggvények integrálása: Segédfüggvények használata bemenetellenőrzéshez és a kérés törzsének feldolgozásához.
 * 
 * Használat:
 * - Az útvonalakat a statikus metódusok (`post`, `get`, `put`, `delete`) meghívásával lehet definiálni,
 *   megadva a végpontot, a vezérlőt, a függvényt és opcionálisan további paramétereket (pl. hitelesítés).
 * - A vezérlőknek implementálniuk kell a megadott függvényeket, hogy kezelni tudják az irányított kéréseket.
 * 
 * Függőségek:
 * - `Helper\Helper`: Segédfüggvényeket biztosít a bemenetek ellenőrzéséhez és a kérés törzsének feldolgozásához.
 * - `App\Authorize\Token`: A tokenek ellenőrzéséért felelős a hitelesítés során.
 */

namespace Router;
use Helper\Helper;
use App\Authorize\Token;
class Router{

    public static function post($endpoint, $controller, $function, $auth = false,$getFiles = false){
        $uri = $_SERVER["REQUEST_URI"];
        
        if ($uri == $endpoint){
            if ($_SERVER["REQUEST_METHOD" ] != "POST"){
                return;
            }
            if ($getFiles == true){
                $controller::$function();
                die();
            }
            $body = Helper::getPostBody();

                if ($auth == true){
                    if ($getFiles == true){
                        $controller::$function();
                        die();
                    }
                    $userID = self::getHeadAuth();
                    $controller::$function($body,$userID);
                    die();
                }
                
                $controller::$function($body);
                die("");
            
        }
        
    }

    

    public static function get( $endpoint, $controller, $function,$auth = false, $getFromURL = false ){
        $uri = $_SERVER["REQUEST_URI"];
            if ($getFromURL){
                $data = self::getVariablesFromUrl($uri, $endpoint);
                if ($data == -1){
                    return;
                }
                $data = $data == false ? self::getBodyFromUrl($uri,$endpoint) : $data;
            }
            else{
                $data = Helper::validateTheInputArray($_GET);
            }
            if (strlen($uri) >= strlen($endpoint) && substr($uri,0, strlen($endpoint))  == $endpoint){
                if ($_SERVER["REQUEST_METHOD" ] != "GET"){
                    return;
                }

                if ($auth == true){
                    $userID = self::getHeadAuth();
                    $controller::$function($data,$userID);
                    die();
                }
                $controller::$function($data);
                die("");
            }
        
        
    }
    public static function put($endpoint, $controller, $function,$auth = true){
        $uri = $_SERVER["REQUEST_URI"];
        
        if ($uri == $endpoint){
            if ($_SERVER["REQUEST_METHOD" ] != "PUT"){
                return;
            }
            $body = Helper::getPostBody();
            if ($auth == true){
                $userID = self::getHeadAuth();
                $controller::$function($body,$userID);
                die();
            }
            
            $controller::$function($body);
            die();
            
        }
    }    
    public static function delete($endpoint, $controller, $function){
        $uri = $_SERVER["REQUEST_URI"];
        $data = self::getVariablesFromUrl($uri, $endpoint);
        if ($data == -1){
            return;
        }
        if (strlen($uri) >= strlen($endpoint) && substr($uri,0, strlen($endpoint)) == $endpoint){
            if ($_SERVER["REQUEST_METHOD"] != "DELETE"){
                return;
            }
            if (($userID = self::getHeadAuth())){
                if ($data != false){
                    $controller::$function($data,$userID);
                    die();
                }
                $controller::$function($userID);
                die();
            }

        }
    }
    private static function getBodyByUrl( $uri ){
        $lengthOfUriDoubles = (count($uri)) %2 == 0 ? count($uri) : count($uri)-1;
        $data = [];
        for ($i=0; $i < $lengthOfUriDoubles / 2; $i++) { 
            $j = $i*2;
            $uriData[$j+1] = strtolower($uri[$j+1]);
            $data[$uri[$i]] =  $uriData[$j+ 1];
        }
        return $data;
    }

    private static function getHeadAuth(){
        $headers = getallheaders();
        $token = $headers['Authorization'] ?? $headers['authorization'] ?? "";
        return Token::verifyToken($token);
    }


    private static function getBodyFromUrl( $uri,$endpoint){
        $body = explode("/",trim(substr($uri, strlen($endpoint)),"/"));
        $data = Helper::validateTheInputArray(self::getBodyByUrl($body));
        return $data;
    }


    private static function getVariablesFromUrl($uri, &$endpoint){
        $endpointArray = explode("/", trim($endpoint, "/"));
        $uriArray = explode("/", trim($uri, "/"));
        $variables = [];
        $returnEndpoint = "";
        for ($i=0; $i < count($endpointArray); $i++) { 
            if ($endpointArray[$i][0] == "{" && $endpointArray[$i][strlen($endpointArray[$i])-1] == "}"){
                $key = substr($endpointArray[$i], 1, strlen($endpointArray[$i])-2);
                $variables[$key] = $i;
            }
            else{
                $returnEndpoint .= "/". $endpointArray[$i];
            }
        }
        
        $endpoint = $returnEndpoint;
        if (count($variables) == 0){
            return false;
        }
        foreach ($variables as $key => $value) {
            if (isset($uriArray[$value])){
                $variables[$key] = $uriArray[$value];
            }
            else{
                return -1;
            }
        }
        
        $variables = Helper::validateTheInputArray($variables, "url");
        if (count($variables) == 1){
            return $variables[array_keys($variables)[0]];
        }
        return $variables;
    }
}
?>