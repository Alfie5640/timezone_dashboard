<?php
require_once __DIR__ . '/../includes/Database.php';

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
        
        self::addUser($conn, $response, $username, $hashedPass);
        
        echo json_encode($response);
    }

    public static function login() {
        $data = json_decode(file_get_contents('php://input'), true);
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        // DB check, JWT generation
        echo json_encode(['success' => true, 'token' => 'JWT_HERE']);
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
}
?>