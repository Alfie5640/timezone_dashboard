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
        $userId = $payload['id'];
        
        
        $timezone = $data['timezone'];
        $desc = $data['description'];
        
        
        self::sanitiseInputs($response, $desc);
        
        //Get timezone Id, insert userId, timezoneId into user_timezones
        $timezoneId = self::getTimezoneId($conn, $response, $timezone);
        
        self::connectTimezone($conn, $response, $desc, $userId, $timezoneId);
        
        $response['success'] = true;
        $response['message'] = "timezones loaded";
        
        echo(json_encode($response));
    }
    
    public static function loadTimezones() {
        $conn = Database::connect();
        $response = ['success' => false, 'message' => '', 'timezone' => [], 'offset' => [], 'descriptions' => []];
        
        $payload = self::getPayload($response);
        $userId = $payload['id'];
        
        //Get timezoneId's associated with userId
        $timezones = self::loadTimezoneIds($conn, $response, $userId);
        
        
        if (empty($timezones)) {
            // No timezones saved yet
            $response['success'] = true;
            $response['message'] = "No timezones found for this user.";
            echo json_encode($response);
            return;
        }
        
        //Get timezone names, offset associates with timzone Ids
        self::getTimezoneInfo($conn, $response, $timezones);
        
        $response['success'] = true;
        
        echo(json_encode($response));
    }
    
    public static function deleteTimezone($tzName) {
        $tzName = urldecode($tzName);
        
        $conn = Database::connect();
        $response = ['success' => false, 'message' => ''];
        
        $payload = self::getPayload($response);
        $userId = $payload['id'];
        
        //We have name to delete and userId
        $timezoneId = self::getTimezoneId($conn, $response, $tzName);
        
        self::deleteFromUserTimezone($conn, $response, $timezoneId, $userId);
        
        echo(json_encode($response));
    }
    
//HELPER FUNCTIONS ------------------------------------
    
    private static function sanitiseInputs(&$response, $desc) {
        if ($desc != filter_var($desc, FILTER_SANITIZE_STRING)) {
            http_response_code(400);
            $response['message'] = "Non-conforming characters in the description field. Please review and re-enter this field";
            $response['success'] = false;
            echo json_encode($response);
            exit;
        }
    }
    
    private static function deleteFromUserTimezone($conn, &$response, $timezoneId, $userId) {
        $stmt = $conn->prepare("DELETE FROM user_timezones WHERE timezoneId = ? AND userId = ?");
        
        if (!$stmt) {
            http_response_code(500);
            $response['message'] = "Database error";
            echo json_encode($response);
            exit;
        }
        
        $stmt->bind_param('ii', $timezoneId, $userId);
        if($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = "Timezone Deleted.";
            $stmt->close();
        } else {
            $response['message'] = "Failed to Add timezone";
            $stmt->close();
        }
    }
    
    private static function getTimezoneInfo($conn, &$response, $timezones) {
        //Loop through each timezone id and push response array with corrolary info
        for ($i=0; $i < count($timezones); $i++) {
            
            $stmt = $conn->prepare("SELECT name, `offset` FROM timezones WHERE id = ?");
            
            $stmt->bind_param('i', $timezones[$i]);
            $stmt->execute();
            $stmt->bind_result($name, $offset);
            
            if($stmt->fetch()) {
                array_push($response['timezone'], $name);
                array_push($response['offset'], $offset);
            }
            
            $stmt->close();
        }
    }
    
    private static function loadTimezoneIds($conn, &$response, $userId) {
        $stmt = $conn->prepare("SELECT timezoneId, description FROM user_timezones WHERE userId = ?");
        
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $stmt->bind_result($timezoneId, $description);
        
        $timezones = [];
        $descriptions = [];
        
        while ($stmt->fetch()) {
            array_push($timezones, $timezoneId);
            array_push($descriptions, $description);
        }
        
        $response['descriptions'] = $descriptions;
        
        $stmt->close();
        return $timezones;
    }
    
    
    private static function getPayload(&$response) {
        $token = JwtHelper::getBearerToken();
        
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../..");
        $dotenv->load();
        $secret = $_ENV['JWT_SECRET'];
        
        if (!$token) {
            http_response_code(401);
            $response['message'] = 'No token provided';
            echo json_encode($response);
            exit;
        }

        $payload = JwtHelper::verifyJWT($token, $secret);
        if ($payload) {
            return $payload;
        } else {
            http_response_code(401);
            $response['message'] = 'Invalid or expired token';
        }
        
        return $payload;
    }
    
    private static function getTimezoneId($conn, &$response, $timezone) {
        $stmt = $conn->prepare("SELECT id FROM timezones WHERE name = ?");
    
        $stmt->bind_param('s', $timezone);
        $stmt->execute();
        $stmt->bind_result($timezoneId);

        if ($stmt->fetch()) {  
            $stmt->close();
            return $timezoneId;
        } else {
            $response['message'] = "Unable to find timezone";
            echo(json_encode($response));
            exit;
        }
    }
    
    private static function connectTimezone($conn, &$response, $desc, $userId, $timezoneId) {
        $stmt = $conn->prepare("INSERT INTO user_timezones (userId, timezoneId, description) VALUES (?, ?, ?)");
        
        if (!$stmt) {
            http_response_code(500);
            $response['message'] = "Database error";
            echo json_encode($response);
            exit;
        }
        
        $stmt->bind_param('iis', $userId, $timezoneId, $desc);
        if($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = "Timezone Added.";
            $stmt->close();
        } else {
            $response['message'] = "Failed to Add timezone";
            $stmt->close();
        }
    }
    
}
?>
