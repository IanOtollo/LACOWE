<?php
/**
 * Database Session Handler
 * Stores session data in the database for persistence across serverless instances
 */

class DatabaseSessionHandler implements SessionHandlerInterface
{
    private $db;
    private $table = 'sessions';

    public function __construct()
    {
    // Database connection will be initialized in open()
    }

    public function open($savePath, $sessionName): bool
    {
        try {
            $this->db = new Database();
            return true;
        }
        catch (Exception $e) {
            error_log("Session Open Error: " . $e->getMessage());
            return false;
        }
    }

    public function close(): bool
    {
        $this->db = null;
        return true;
    }

    public function read($id): string
    {
        try {
            $sql = "SELECT data FROM {$this->table} WHERE id = :id AND expires > :now";
            $row = $this->db->query($sql)
                ->bind(':id', $id)
                ->bind(':now', time())
                ->fetch();

            return $row ? $row['data'] : '';
        }
        catch (Exception $e) {
            error_log("Session Read Error: " . $e->getMessage());
            return '';
        }
    }

    public function write($id, $data): bool
    {
        try {
            $expires = time() + (int)ini_get('session.gc_maxlifetime');

            // Use DELETE then INSERT for cross-driver compatibility (MySQL & Postgres)
            $this->db->beginTransaction();

            $sql = "DELETE FROM {$this->table} WHERE id = :id";
            $this->db->query($sql)->bind(':id', $id)->execute();

            $sql = "INSERT INTO {$this->table} (id, data, expires) VALUES (:id, :data, :expires)";
            $this->db->query($sql)
                ->bind(':id', $id)
                ->bind(':data', $data)
                ->bind(':expires', $expires)
                ->execute();

            $this->db->commit();
            return true;
        }
        catch (Exception $e) {
            if ($this->db)
                $this->db->rollback();
            error_log("Session Write Error: " . $e->getMessage());
            return false;
        }
    }

    public function destroy($id): bool
    {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id = :id";
            $this->db->query($sql)->bind(':id', $id)->execute();
            return true;
        }
        catch (Exception $e) {
            error_log("Session Destroy Error: " . $e->getMessage());
            return false;
        }
    }

    public function gc($maxlifetime): int|false
    {
        try {
            $sql = "DELETE FROM {$this->table} WHERE expires < :now";
            $this->db->query($sql)->bind(':now', time())->execute();
            return true;
        }
        catch (Exception $e) {
            error_log("Session GC Error: " . $e->getMessage());
            return false;
        }
    }
}
