<?php
class settings extends common {
    protected $defaultSettings = [
        "consultationFee-cost" => 20000,
        "consultationFee-component-id" => 1,
        "lateFee" => 5000,
        "registrationFee-cost" => 5000,
        "registrationFee-component-id" => 6,
        "medicationCategory" => 1,
        "lowInventoryCount" => 0,
        "alertGroup" => 'lekki_hill_admin',
        "resultPerPage" => 20
    ];


    public function getSettings() {
        $default = $this->defaultSettings;
        $data = $this->lists(table_name_prefix."settings", false, false, "title", "ASC");

        $this->return = $this->successResponse;
        foreach($data as $row) {
            $this->return['data'][$row['title']] = $row['value'];
            unset($default[$row['title']]);
        }

        foreach($default as $key => $row) {
            $this->return['data'][$key] = $row;
        }

        return $this->return;
    }
		
    public function get($id) {
        $data = $this->query("SELECT `value` FROM `".table_prefix.table_name_prefix."settings` WHERE `title` =:id", array(':id' => $id), "getCol");
        if ($data) {
            return $data;
        } else {
            return $this->defaultSettings[$id];
        }
    }

    public function setSettings($data) {

        foreach($data as $key => $row) {
            $this->modify($key, $row);
        }
        return $this->successResponse;
    }
		
    private function modify($title, $value) {        
        if ($this->checkExisit($title)) {
            $sql = $this->insert(table_name_prefix.'settings',array('title' => $title, 'value' => $value));
        } else {
            $data = array('title' => $title, 'value' => $value);
            $where = array("title" => $title);

            $sql = $this->update(table_name_prefix."settings", $data, $where, "AND");
        }			
        if ($sql) {
            return true;
        } else {
            return false;
        }
    }

    private function checkExisit($title) {
        $sql = $this->query("SELECT * FROM `".table_prefix.table_name_prefix."settings` WHERE `title` = :title", array(	':title' => $title), "count");

        if ($sql == 0) {
            return true;
        } else {
            return false;
        }
    }
}
?>