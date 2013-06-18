<?php

class DB {
    // Configuration information:
    private $db;
    private $serverType;
    private static $user = 'username';
    private static $pass = 'password';
    private static $factory;
    private static $config = array(
        'write' =>
            array('mysql:dbname=username;host=localhost;charset=utf8'),
        'read' =>
          // Add more options for scalable slave servers
            array('mysql:dbname=username;host=localhost;charset=utf8')
        );

    public static function getFactory() {
        if (!self::$factory) {
            self::$factory = new DB();
            }
            return self::$factory;
        }

    // Static method to return a database connection:
    public function getConnection($serverType) {
        if ($this->db && ($serverType == $this->serverType) || $this->serverType == 'write') {
            return $this->db;
            }
        else {
            // First make a copy of the server array so we can modify it
            $servers = self::$config[$serverType];
            
            // Keep trying to make a connection:
            while (!$this->db && count($servers)) {
                $key = array_rand($servers);
                try {
                    $this->db = new PDO($servers[$key], 
    			self::$user, self::$pass);
                } catch (PDOException $e) {}
                
                if (!$this->db) {
                    // We couldn't connect.  Remove this server:
                    unset($servers[$key]);
                }
            	}
            
            // If we never connected to any database, throw an exception:
            if (!$this->db) {
                throw new Exception("Failed: {$server} database");
            	}
            $this->serverType = $serverType;
            return $this->db;
            }
   		}

    public function update($input) { // Requires table, columns, where, data to be passed
        $columns = explode(',', $input->columns);
        $updateStatement = "";
        if (isset($input->where)) {
            $where = 'WHERE ' . $input->where;
            }
        else {
            $where = "";
            }
        $executeArray = array();
        foreach($columns as $column) {
            $updateStatement .= $column . "=:" . $column . ",";
            $executeArray[':' . $column] = $input->data->{$column};
            }
        preg_match('/\:[A-Za-z0-9\_]+/', $input->where, $whereIds);
        foreach($whereIds as $id) {
            $executeArray[$id] = $input->data->{preg_replace('/:/', '', $id)};
            }
        $db = self::getFactory()->getConnection('write');
        $stmt = $db->prepare("UPDATE " . $input->table . " SET " . $updateStatement . "dateUpdated=UTC_TIMESTAMP() " . $where);
        $stmt->execute($executeArray);
        return $stmt->rowCount() ? true : false;
        }
    public function insert($input) {  // Requires table, columns, data to be passed
        $input->columns = preg_replace('/\s/', '', $input->columns);
        $values = ':' . preg_replace('/,/', ',:', $input->columns);
        $data = array();
        foreach(explode(',', $input->columns) as $column) {
            $data[':' . $column] = $input->data->{$column};
            }
        $db = self::getFactory()->getConnection('write');
        $stmt = $db->prepare("INSERT INTO " . $input->table . " (" . $input->columns . ",dateInserted,dateUpdated) VALUES " .
                                   "(" . $values . ",UTC_TIMESTAMP(),UTC_TIMESTAMP)");
        $stmt->execute($data);
        return $stmt->rowCount() ? $db->lastInsertId('id') : implode('|',$stmt->errorInfo());
        }
    public function delete($input) {  // Requires table, where, data to be passed
        $db = self::getFactory()->getConnection('write');
        $stmt = $db->prepare("DELETE FROM " . $input->table . " WHERE " . $input->where);
        $stmt->execute($input->data);
        return $stmt->rowCount();
        }
	}

?>
