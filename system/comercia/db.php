<?php
namespace comercia;
class db
{
    public function saveDataObject($table, $data, $keys = null)
    {
        if (!$keys) {
            $keys[] = $table . "_id";
        }
        $exists = $this->recordExists($table, $data, $keys);

        if ($exists) {
            $query = "UPDATE `" . DB_PREFIX . $table . "` SET ";
        } else {
            $query = "INSERT INTO `" . DB_PREFIX . $table . "` SET ";
        }

        $i = 0;
        foreach ($data as $key => $value) {
            if (!$this->columnExists($table, $key)) {
                continue;
            }
            if ($i++) {
                $query .= ",";
            }
            $query .= "`" . $key . "` = '" . $this->_db()->escape($value) . "'";
        }
        if ($exists) {
            $query .= " WHERE ";
            $query .= $this->whereForKeys($table, $data, $keys);
        }
        $this->_db()->query($query);
        if (!$exists) {
            return $this->_db()->getLastId();
        }

        if (count($keys) == 1) {
            return $data[$keys[0]];
        }
        $result = [];
        foreach ($keys as $key) {
            $result[] = $data[$key];
        }

        return $result;
    }

    public function recordExists($table, $data, $keys = null)
    {

        $query = "SELECT * FROM `" . DB_PREFIX . $table . "` WHERE ";
        $query .= $this->whereForKeys($table, $data, $keys);
        $query .= " LIMIT 0,1";
        $query = $this->_db()->query($query);

        return (bool)$query->num_rows;
    }

    public function columnExists($table, $column)
    {
        static $columns = [];

        if (!isset($columns[$table][$column])) {
            $query = "SHOW COLUMNS FROM `" . DB_PREFIX . $table . "` LIKE '" . $column . "'";
            $columns[$table][$column] = (bool)$this->_db()->query($query)->num_rows;
        }

        return $columns[$table][$column];
    }

    private function whereForKeys($table, $data, $keys = null)
    {
        if (!$keys) {
            $keys[] = $table . "_id";
        }
        $result = "";
        foreach ($keys as $akey => $key) {
            if ($akey > 0) {
                $result .= " && ";
            }
            $result .= " `" . $key . "` = '" . @$data[$key] . "' ";
        }

        return $result;
    }

    private function whereForData($data)
    {
        $result = '';

        $i = 0;
        foreach ($data as $key => $value) {
            if ($i++) {
                $result .= ' && ';
            }

            $result .= "`" . $key . "` = '" . $this->_db()->escape($value) . "'";
        }

        return $result;
    }

    public function saveDataObjectArray($table, $data,$keys=null)
    {
        foreach ($data as $obj) {
            $this->saveDataObject($table, $obj,$keys);
        }
    }
    private function _db()
    {
        $registry = Util::registry();
        if (!$registry->has('db')) {
            $registry->set('db', new db());
        }

        return $registry->get("db");
    }

    public function select($table, $fields = [], $where = [])
    {
        if (empty($fields)) {
            $fields[] = '*';
        }

        $query = "SELECT ";

        $i = 0;
        foreach ($fields as $field)
        {
            if($i++) {
                $query .= ',';
            }
            $query .= $field == '*' ? $field : '`' . $field . '`';
        }


        $query .= " FROM `" . DB_PREFIX . $table . "`";

        if (!empty($where)) {
            $query .= " WHERE ";
            $query .= $this->whereForData($where);
        }

        $result = $this->_db()->query($query);

        if ($result->num_rows > 1) {
            return $result->rows;
        }

        return $result->row;
    }
    public function query($query)
    {
        $result = $this->_db()->query($query);

        if ($result) {
            return $result->rows;
        }

        return [];
    }
}
?>
