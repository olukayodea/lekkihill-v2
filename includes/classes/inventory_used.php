<?php
class inventory_used extends common {
    public $id;
    public $minify = false;
    public $filter = null;
    public $search = null;

    public function getUsedount($id) {
        $query = "SELECT SUM(`inventory_used`) FROM ".table_prefix . table_name_prefix."inventory_used WHERE `inventory_id` = :id";
        $prepare[':id'] = $id;

        return $this->query($query,  $prepare, "getCol");
    }
}
?>