<?php
/**
 * router.php
 * 
 * Ez a fájl egy egyszerű routerként szolgál, amely kezeli a beérkező HTTP kéréseket, és továbbítja azokat a megfelelő vezérlőkhöz.
 * Meghatározza az alkalmazás API útvonalait, és összeköti azokat a megfelelő vezérlőmetódusokkal.
 * 
 * Funkciók:
 * - Kezeli az API végpontok útvonalait, beleértve a felhasználókezelést, könyvkezelést, kölcsönzéseket, foglalásokat és képek kezelését.
 * - Támogatja a többféle HTTP metódust (GET, POST, PUT, DELETE).
 * - Központosított helyet biztosít az API útvonalak definiálására és kezelésére.
 * - 404-es hibát ad vissza, ha a kért útvonal nem található.
 * 
 * Útvonalak:

 * 
 * Függőségek:
 * - `Router\Router`: Az útvonalak és kérések kezelése.
 * - `ApiResponse\Response`: API válaszok és hibaformázás kezelése.
 * - Vezérlők (Controllers): Az egyes funkciók kezelése (pl. `UserController`, `BooksController` stb.).
 */

require __DIR__ . '/vendor/autoload.php';
use Router\Router;
use ApiResponse\Response;

use App\Controllers\ClientController;

Router::get("/api/personal", ClientController::class, "all", );
Router::post("/api/personal",ClientController::class, "store");

Response::httpError(404, "Route not found");



?>