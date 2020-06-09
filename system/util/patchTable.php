<?php
namespace util;

class PatchTable
{
    var $db;
    var $actions = array();
    var $name;

    function __construct($name,$db)
    {
        $this->db=$db;
        $this->name=$name;
    }

    function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    function save()
    {
        if ($this->exists()) {
            $this->update();
        } else {
            $this->create();
        }
    }

    function exists()
    {
        $prefix = DB_PREFIX;
        $query = $this->db->query("SHOW TABLES LIKE '" . $prefix . $this->name . "';");
        return $query->num_rows;
    }

    function columnExists($columnName = '')
    {
        $prefix = DB_PREFIX;
        $query = $this->db->query("SHOW COLUMNS FROM `" . $prefix . $this->name . "` LIKE '" . $columnName . "';");
        return $query->num_rows;
    }

    function update()
    {
        $prefix = DB_PREFIX;
        $query = "ALTER TABLE `" . $prefix . $this->name . "` ";

        $i = 0;
        if (isset($this->actions["addField"])) {
            foreach ($this->actions["addField"] as $action) {
                if (!$this->columnExists($action['name'])) {
                    if ($i > 0) {
                        $query .= ",";
                    }

                    if ($action["default"]!==null && $action["default"]!==false && $action["default"]!=='primary'){
                        $action["default"]="'".$action["default"]."'";
                    }

                    $query .= "ADD `" . $action["name"] . "` " . $action["type"]. (($action["default"]!==false && $action["default"]!=='primary')?" DEFAULT " . $action["default"]:"");

                    if (strtolower($action['default']) == 'primary') {
                        $query .= ", ADD PRIMARY KEY (" . $action["name"] . ")";
                    }

                    $i++;
                }
            }
        }

        if (isset($this->actions["editField"])) {
            foreach ($this->actions["editField"] as $action) {
                if ($this->columnExists($action['name'])) {
                    if ($i > 0) {
                        $query .= ",";
                    }

                    if($action["default"]!==null && $action["default"]!==false && $action["default"]!=='primary'){
                        $action["default"]="'".$action["default"]."'";
                    }

                    $query .= "MODIFY `" . $action["name"] . "` " . $action["type"]. (($action["default"]!==false && $action["default"]!=='primary')?" DEFAULT ".$action["default"]:"");

                    if (strtolower($action['default']) == 'primary') {
                        $query .= ", ADD PRIMARY KEY (" . $action["name"] . ")";
                    }

                    $i++;
                }
            }
        }


        if (isset($this->actions['removeField'])) {
            foreach ($this->actions['removeField'] as $action) {
                if ($this->columnExists($action['name'])) {
                    if ($i > 0) {
                        $query .= ',';
                    }
                    $query .= "DROP COLUMN `" . $action['name'] . "`";
                    $i++;
                }
            }
        }
        $this->db->query($query);

        if (isset($this->actions["addIndex"])) {
            foreach ($this->actions["addIndex"] as $action) {
                if ($this->columnExists($action['name'])) {
                    $this->db->query("CREATE INDEX `" . $action["name"] . "` ON `" . $prefix . $this->name . "` (`" . $action["name"] . "`);");
                }
            }
        }
		
        if (isset($this->actions["addUnique"])) {
            foreach ($this->actions["addUnique"] as $action) {
                $this->db->query("ALTER TABLE `" . $prefix . $this->name . "` ADD UNIQUE (`" . $action["name"] . "`);");
            }
        }
    }

    function create()
    {
		if (!$this->exists($this->name)) {
			$prefix = DB_PREFIX;
			$primary = false;
			$query = "create table `" . $prefix . $this->name . "` (";

			$i = 0;
			if (isset($this->actions["addField"])) {
				foreach ($this->actions["addField"] as $action) {
					if ($i > 0) {
						$query .= ",";
					}
					
					$query .= "`" . $action["name"] . "` " . $action["type"];
					
					if (strtolower($action['default']) == 'primary') {
						$primary = $action["name"];
					}
					
					$i++;
				}
			}

			if ($primary) {
                $query .= ",PRIMARY KEY (" . $primary . ")";
            }

			$query .= ")";


			$this->db->query($query);

			if (isset($this->actions["addIndex"])) {
				foreach ($this->actions["addIndex"] as $action) {
					$this->db->query("CREATE INDEX `" . $action["name"] . "` ON `" . $prefix . $this->name . "` (`" . $action["name"] . "`);");
				}
			}
			
			if (isset($this->actions["addUnique"])) {
				foreach ($this->actions["addUnique"] as $action) {
					$this->db->query("ALTER TABLE `" . $prefix . $this->name . "` ADD UNIQUE (`" . $action["name"] . "`);");
				}
			}

			return $this;
		}
    }

    function addField($field, $type, $default=false)
    {
        $this->actions["addField"][] = array(
            "name"    => $field,
            "type"    => $type,
            "default" => $default
        );

        return $this;
    }


    function editField($field, $type, $default=false)
    {
        $this->actions["editField"][] = array(
            "name"    => $field,
            "type"    => $type,
            "default" => $default
        );

        return $this;
    }

    function removeField($field)
    {
        $this->actions['removeField'][] = array(
            'name' => $field
        );

        return $this;
    }

    function addIndex($field)
    {
        $this->actions["addIndex"][] = array(
            "name" => $field,
        );

        return $this;
    }

    function addUnique($field)
    {
        $this->actions["addUnique"][] = array(
            "name" => $field,
        );

        return $this;
    }
}
