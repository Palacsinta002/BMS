<?php
/**
 * EmailBodies.php
 * 
 * Ez a fájl az `EmailBodies` osztályt definiálja, amely különböző e-mail törzseket biztosít
 * Az osztály statikus metódusokat tartalmaz, amelyek egy-egy e-mail törzs HTML kódját generálják.
 * Az e-mail törzsek tartalmazzák a felhasználó számára szükséges információkat, például
 * a jelszó visszaállításához szükséges linket vagy a fiók ellenőrzéséhez szükséges kódot.
 */
namespace Emailer;

class EmailBodies{
    public static function verifyEmail($verificationCode){
        return "<html>
            <body style='font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px;'>
                <div style='max-width: 600px; background: #ffffff; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); margin: auto;'>
                    <div style='text-align: center; font-size: 22px; color: #333; font-weight: bold;'>Email Verification</div>
                    <div style='margin-top: 20px; font-size: 16px; color: #555;'>
                        <p>Thank you for registering with us! Please use the verification code below to verify your email address:</p>
                        <div style='display: block; width: fit-content; margin: 20px auto; font-size: 24px; font-weight: bold; background: #007bff; color: white; padding: 12px 20px; border-radius: 5px; letter-spacing: 2px;'>
                            ".$verificationCode."
                        </div>
                        <p>If you did not request this, please ignore this email.</p>
                    </div>
                    <div style='font-size: 12px; text-align: center; color: #999; margin-top: 20px;'>
                        <p>If you have any concerns, please contact our support team.</p>
                        <p>Thank you, <br> LMS</p>
                    </div>
                </div>
            </body>
        </html>";
    }
    public static function forgotPassword( $username, $token ){
        return "<html>
        <body style='font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px;'>
            <div style='max-width: 600px; background: #ffffff; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); margin: auto;'>
                <div style='text-align: center; font-size: 22px; color: #333; font-weight: bold;'>Password Reset Request</div>
                <div style='margin-top: 20px; font-size: 16px; color: #555;'>
                    <p>Hello, ".$username."</p>
                    <p>A password reset request was made for your account. If you initiated this request, please click the button below to reset your password:</p>
                    <a href='http://localhost:5173/change-password/$token' style='display: block; width: 200px; text-align: center; background: #007bff; color: white; padding: 12px; border-radius: 5px; text-decoration: none; font-weight: bold; margin: 20px auto;'>Reset Password</a>
                    <p>If you did not request this, please ignore this email. Your account remains secure.</p>
                    <p>For security reasons, this link will expire in 15 minutes.</p>
                </div>
                <div style='font-size: 12px; text-align: center; color: #999; margin-top: 20px;'>
                    <p>If you have any concerns, please contact our support team.</p>
                    <p>Thank you, <br> LMS</p>
                </div>
            </div>
        </body>
        </html>";
    }
}

?>