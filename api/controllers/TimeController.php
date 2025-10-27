<?php
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/jwtHelper.php';
require_once __DIR__ . '/../../vendor/autoload.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
//header('Access-Control-Allow-Methods: POST');


class TimeController {
    public static function addTimezone() {
        $conn = Database::connect();
        $response = ['success' => false, 'message' => ''];
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        $payload = self::getPayload($response);
        $username = $payload['username'];
        
        
        $timezone = $data['timezone'];
        $desc = $data['desc'];
        
        
        self::sanitiseInputs($response, $desc);        
        self::addTimezone($conn, $response, $timezone, $desc, $username);
        
    }
    
    private static function sanitiseInputs(&$response, $desc) {
        if ($desc != filter_var($desc, FILTER_SANITIZE_STRING)) {
            http_response_code(400);
            $response['message'] = "Non-conforming characters in the description field. Please review and re-enter this field";
            $response['success'] = false;
            echo json_encode($response);
            exit;
        }
    }
    
    private static function getPayload(&$response) {
        $token = JwtHelper::getBearerToken();
        if (!$token) {
            http_response_code(401);
            $response['message'] = 'No token provided';
            echo json_encode($response);
            exit;
        }

        $payload = JwtHelper::verifyJWT($token, $secret);
        if ($payload) {
            $response['success'] = true;
            $response['username'] = $payload['username'];
        } else {
            http_response_code(401);
            $response['message'] = 'Invalid or expired token';
        }
        
        return $payload;
    }
    
    private static function addTimezone($conn, $response, $timezone, $desc, $username) {
        
    }
    
}
?>