<?php
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../../vendor/autoload.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

class AuthController {
    public static function register() {
        
        $conn = Database::connect();
        $response = ['success' => false, 'message' => ''];
        
        $data = json_decode(file_get_contents('php://input'), true);
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        self::sanitiseInputs($response, $username, $password);
        $hashedPass = self::hashPassword($password);
        
        self::checkExists($conn, $response, $username);
        self::addUser($conn, $response, $username, $hashedPass);
        
        echo json_encode($response);
    }

    public static function login() {
        
        $conn = Database::connect();
        $response = ['success' => false, 'message' => '', 'token' => ''];
        
        $data = json_decode(file_get_contents('php://input'), true);
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';
        
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../..");
        $dotenv->load();
        $secret = $_ENV['JWT_SECRET'];

        self::sanitiseInputs($response, $username, $password);
        self::authenticateUser($conn, $response, $username, $password, $secret);
        
        echo json_encode($response);
    }
    
    
    private static function sanitiseInputs(&$response, $username, $password) {
        if ($username != filter_var($username, FILTER_SANITIZE_STRING)) {
            http_response_code(400); // Bad Request
            $response['message'] = "Non-conforming characters in the username field. Please review and re-enter this field";
            $response['success'] = false;
            echo json_encode($response);
            exit;
        }

        if ($password != filter_var($password, FILTER_SANITIZE_STRING)) {
            http_response_code(400); // Bad Request
            $response['message'] = "Non-conforming characters in the password field. Please review and re-enter this field";
            $response['success'] = false;
            echo json_encode($response);
            exit;
        }
    }
        
    private static function checkExists($conn, &$response, $username) {
        $stmt = $conn->prepare("SELECT 1 FROM Users WHERE username = ? LIMIT 1");
        if ($stmt === false) {
            http_response_code(500);
            $response['message'] = 'DB error ';
            return;
        }
        
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();
        
        if ($exists) {
            $response['message'] = "Username already exists.";
            echo(json_encode($response));
            exit;
        }
    }
    
    private static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
        
    private static function addUser($conn, &$response, $username, $password) {
        $stmt = $conn->prepare("INSERT INTO Users (username, password_hash) VALUES (?,?)");
        if ($stmt === false) {
            http_response_code(500);
            $response['message'] = 'DB error ';
            return;
        }
        
        $stmt->bind_param("ss", $username, $password);
        
        
        if (!mysqli_stmt_execute($stmt)) {
            http_response_code(500); // Internal Server Error
            $response['message'] = "DB Error";
        } else {
            http_response_code(201); // Created
            $response['success'] = true;
            $response['message'] = "User registered successfully";
            
        }
        
        mysqli_stmt_close($stmt);
    }
    
    private static function authenticateUser($conn, &$response, $username, $password, $secret) {
        $stmt = $conn->prepare("SELECT userId, password_hash FROM Users WHERE username = ?");
        if ($stmt === false) {
            http_response_code(500);
            $response['message'] = 'DB error ';
            return;
        }
        
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($id, $storedPass);
        
        if($stmt->fetch()) {
            $stmt->close();
            if(password_verify($password, $storedPass)) {
                
                $response['success'] = true;
                $response['message'] = "Successful login";
                
                self::createjwt($id, $username, $secret, $response);
                
            } else {
                http_response_code(401);
                $response['message'] = "Username or password incorrect";
            }
        } else {
            http_response_code(401);
            $response['message'] = "Username or password incorrect";
            $stmt->close();
        }
        
    }
    
    private static function createjwt($id, $username, $secret, &$response) {
        $payload = [
            "id"=>$id,
            "username"=>$username,
            "iat"=>time(),
            "exp"=>time()+3600
        ];
        
        $jwt = self::makeJWT($payload, $secret);
        $response['token'] = $jwt;
    }
    
    private static function makeJWT($payload, $secret) {
        $header = json_encode([
            'alg' => 'HS256',
            'typ' => 'JWT'
        ]);
        
        $base64Header = self::base64UrlEncode($header);
        $base64Payload = self::base64UrlEncode(json_encode($payload));
        
        $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, $secret, true);
        $base64Signature = self::base64UrlEncode($signature);
        
        return $base64Header . "." . $base64Payload . "." . $base64Signature;
    }
    
    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
?>