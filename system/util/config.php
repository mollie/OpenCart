<?php

namespace util;
class Config
{
    var $model;
    var $store_id;
    var $data = [];
    var $db;

    function __construct($store_id = 0)
    {
        $this->model = Util::load()->model("setting/setting");
        $this->store_id = $store_id;
        $this->db = Util::registry()->get("db");
        $data = Util::db()->query("SELECT * FROM " . DB_PREFIX . "setting WHERE store_id = " . $store_id . "");
        foreach ($data as $value) {
            if (!$value['serialized']) {
                $this->data[$value["key"]] = $value["value"];
            } else {
                $this->data[$value["key"]] = (Util::version()->isMinimal(2.1)) ? json_decode($value["value"], true) : unserialize($value['value']);
            }
        }
    }

    function __get($name)
    {
        return $this->get($name);
    }

    function __set($name, $value)
    {
        $code = explode("_", $name)[0];
        $this->set($code, $name, $value);
    }

    function get($key, $ignoreMainStore = false)
    {
        if (isset($this->data[$key])) {
            return @$this->data[$key] ?: "";
        } elseif ($this->store_id && !$ignoreMainStore) {
            return Util::config(0)->$key;
        }
        return "";
    }

    function getGroup($code)
    {
        return $this->model->getSetting($code, $this->store_id);
    }

    function set($code, $key, $value = false)
    {
        if (!is_array($key)) {
            $key = [$key => $value];
        }
        $items = Util::arrayHelper()->allPrefixed($key, $code, false);
        $this->model->editSetting($code, $items, $this->store_id);
        foreach ($items as $key => $val) {
            $this->data[$key] = $val;
        }
    }

    function setValue($code, $key, $value = '')
    {
        if(Util::version()->isMaximal('1.5.6.4')) {
            $code = 'group';
        } else {
            $code = 'code';
        }

        if (!is_array($value)) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int)$this->store_id . "', `" . $code . "` = '" . $this->db->escape($code) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape($value) . "'");
        } else {
            $this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int)$this->store_id . "', `" . $code . "` = '" . $this->db->escape($code) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape(json_encode($value, true)) . "', serialized = '1'");
        }
        $this->data[$key] = $value;
    }
}

?>