<?php
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/jwtHelper.php';
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
        $hashedPass = password_hash($password, PASSWORD_DEFAULT);

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

    public static function decode() {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../..");
        $dotenv->load();
        $secret = $_ENV['JWT_SECRET'];

        $response = ['success' => false, 'message' => '', 'username' => ''];

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

        echo json_encode($response);
    }

    // -------------------- HELPER FUNCTIONS --------------------

    private static function sanitiseInputs(&$response, $username, $password) {
        if ($username != filter_var($username, FILTER_SANITIZE_STRING)) {
            http_response_code(400);
            $response['message'] = "Invalid characters in username.";
            echo json_encode($response);
            exit;
        }

        if ($password != filter_var($password, FILTER_SANITIZE_STRING)) {
            http_response_code(400);
            $response['message'] = "Invalid characters in password.";
            echo json_encode($response);
            exit;
        }
    }

    private static function checkExists($conn, &$response, $username) {
        $stmt = $conn->prepare("SELECT 1 FROM Users WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();

        if ($exists) {
            $response['message'] = "Username already exists.";
            echo json_encode($response);
            exit;
        }
    }

    private static function addUser($conn, &$response, $username, $hashedPass) {
        $stmt = $conn->prepare("INSERT INTO Users (username, password_hash) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $hashedPass);

        if (!$stmt->execute()) {
            http_response_code(500);
            $response['message'] = "Database error.";
        } else {
            http_response_code(201);
            $response['success'] = true;
            $response['message'] = "User registered successfully.";
        }

        $stmt->close();
    }

    private static function authenticateUser($conn, &$response, $username, $password, $secret) {
        $stmt = $conn->prepare("SELECT userId, password_hash FROM Users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($id, $storedPass);

        if ($stmt->fetch()) {
            $stmt->close();
            if (password_verify($password, $storedPass)) {
                $response['success'] = true;
                $response['message'] = "Successful login";
                $response['token'] = self::createJWT($id, $username, $secret);
            } else {
                http_response_code(401);
                $response['message'] = "Username or password incorrect.";
            }
        } else {
            http_response_code(401);
            $response['message'] = "Username or password incorrect.";
            $stmt->close();
        }
    }

    private static function createJWT($id, $username, $secret) {
        $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
        $payload = json_encode([
            "id" => $id,
            "username" => $username,
            "iat" => time(),
            "exp" => time() + 3600
        ]);

        $base64Header = rtrim(strtr(base64_encode($header), '+/', '-_'), '=');
        $base64Payload = rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');

        $signature = hash_hmac('sha256', "$base64Header.$base64Payload", $secret, true);
        $base64Signature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

        return "$base64Header.$base64Payload.$base64Signature";
    }
}
?>
