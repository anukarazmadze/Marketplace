<?php
class CustomSessionHandler implements SessionHandlerInterface {
    private $conn;

    public function __construct($db_host, $db_user, $db_pass, $db_name) {
        $this->conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
        if ($this->conn->connect_error) {
            die('Connection failed: ' . $this->conn->connect_error);
        }
    }

    public function open($savePath, $sessionName): bool {
        return true;
    }

    public function close(): bool {
        return $this->conn->close();
    }

    public function read($id): string {
        $stmt = $this->conn->prepare("SELECT data FROM sessions WHERE id = ? AND last_access > DATE_SUB(NOW(), INTERVAL 1 DAY)");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $stmt->bind_result($data);
        $stmt->fetch();
        $stmt->close();

        return $data ? $data : '';
    }

    public function write($id, $data): bool {
        $userId = $_SESSION['user_id'] ?? null;
        $email = $_SESSION['email'] ?? '';
        $role = $_SESSION['role'] ?? '';

        $stmt = $this->conn->prepare("REPLACE INTO sessions (id, user_id, email, role, data, last_access) VALUES (?, ?, ?, ?, ?, NOW())");
        
        if ($stmt === false) {
            die('Prepare failed: ' . htmlspecialchars($this->conn->error));
        }

        $stmt->bind_param("sssss", $id, $userId, $email, $role, $data);
        $result = $stmt->execute();

        if ($result === false) {
            die('Execute failed: ' . htmlspecialchars($stmt->error));
        }

        $stmt->close();

        return $result;
    }

    public function destroy($id): bool {
        $stmt = $this->conn->prepare("DELETE FROM sessions WHERE id = ?");
        $stmt->bind_param("s", $id);
        return $stmt->execute();
    }

    public function gc($maxlifetime): int|false {
        $stmt = $this->conn->prepare("DELETE FROM sessions WHERE last_access < DATE_SUB(NOW(), INTERVAL 1 DAY)");
        return $stmt->execute() ? 0 : false;
    }
}
?>
