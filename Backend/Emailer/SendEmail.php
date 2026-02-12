<?php
/**
 * SendEmail.php
 * Ez a fájl a PHPMailer alapfájlja. A forráskód elérhető itt: https://github.com/PHPMailer/PHPMailer.
 * Ez a fájl kezeli az e-mailek küldését. A projekt e-mail címe: librarymanagementsystem.emailer@gmail.com,
 * így minden, a rendszer által küldött e-mail erről a címről fog érkezni.
 * A sendEmail függvény 4 paramétert vár:
 * Az első a címzett e-mail címe
 * A második a címzett neve
 * A harmadik az e-mail tárgya
 * A negyedik az e-mail szövegtörzse (tartalma)
 * Ezen információk alapján a függvény e-mailt küld a megadott címzettnek.
 */

namespace Emailer;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use ApiResponse\Response;
use Config\Env;

class SendEmail{
public static function sendEmail($sendToElmail,$sendToName,$subject,$body){
    $mail = new PHPMailer(true);
    Env::load(); 
    try {
        #Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'librarymanagementsystem.emailer@gmail.com';
        $mail->Password = $_ENV['EMAIL_PASSWORD']; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
        $mail->Port = 587;

        #Recipients
        $mail->setFrom('librarymanagementsystem.emailer@gmail.com', 'LMS');
        $mail->addAddress($sendToElmail, $sendToName);

        #Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        

        $mail->send();
    } catch (Exception $e) {
        Response::httpError(500, 2);
    }
    }
}
?>