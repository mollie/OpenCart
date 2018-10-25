<?php
namespace comercia;

class Patch
{
    var $db;
    function __construct()
    {
        $prefix=DB_PREFIX;
        require_once(__DIR__."/patchTable.php");
        $this->db=Util::registry()->get("db");

        $table = $this->table("comercia_patch");
        if (!$table->exists()) {
            $table->addField("path","varchar(255)");
            $table->addField("patch", "varchar(50)");
            $table->addField("success", "int");
            $table->addField("date", "int");
            $table->create();
        }

    }

    function runPatchesFromFile($path){
        $patches=include($path);
        $this->runPatches($patches,$path);
    }

    function runPatchesFromFolder($folder,$context=false) {
        if(!$context){
            $context=$folder;
        }
        $path = DIR_APPLICATION . 'patch/' . $folder;
        $patches = [];

        if($handle = opendir($path)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != '.' && $entry != '..' && !is_dir($path . '/' . $entry) && substr($entry, -3) === 'php') {
                    $index = substr($entry, 0,-4);
                    $patches[$index] = include $path . '/' . $entry;
                }
            }

            ksort($patches);
            closedir($handle);
        }

        $newPatches=[];
        foreach($patches as $key=>$val){
            if(is_numeric(substr($key,0,1))){
                $explode=explode("_",$key);
                unset($explode[0]);
                $key=implode("_",$explode);
            }
            $newPatches[$key]=$val;
        }

        $this->runPatches($newPatches, $context);
    }

    function runPatches($patches,$path){
        foreach($patches as $key=>$patch){
            if($this->needPatch($path,$key)){
                $patch();
                $this->registerDone($path,$key);
            }
        }
    }

    function registerDone($path,$patch){
        $prefix=DB_PREFIX;
        $this->db->query("insert into ".$prefix."comercia_patch set 
            `path`='".$path."',
            `patch`='".$patch."',
            `success`=1,
            `date`=".time()."
        ");
    }

    function needPatch($path,$patch){
            $prefix = DB_PREFIX;
            $query = $this->db->query("select comercia_patch_id from " . $prefix . "comercia_patch where `path`='".$path."' and `patch`='" . $patch . "' and success=1");
            return !$query->num_rows;
    }

    function table($table){
        return new PatchTable($table,$this->db);
    }
}
