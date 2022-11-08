<?php
class inventory_count extends inventory_used {
    public function createCount($array) {
        return self::insert(table_name_prefix."inventory_count", $array);
    }

    public function getSortedInventoryCount($id, $tag, $tag2 = false, $id2 = false, $tag3 = false, $id3 = false, $order = 'ref', $dir = "DESC", $logic = "AND", $start = false, $limit = false) {
        return self::sortAll(table_name_prefix."inventory_count", $id, $tag, $tag2, $id2, $tag3, $id3, $order, $dir, $logic, $start, $limit);
    }

    public function getCount($id) {
        $query = "SELECT SUM(`inventory_added`) FROM ".table_prefix . table_name_prefix."inventory_count WHERE `inventory_id` = :id";
        $prepare[':id'] = $id;

        return self::query($query,  $prepare, "getCol");
    }

    public function formatCountResult($data, $single=false) {
        if ($single == false) {
            for ($i = 0; $i < count($data); $i++) {
                $data[$i] = $this->cleanCount($data[$i]);
            }
        } else {
            $data = $this->cleanCount($data);
        }
        return $data;
    }

    private function cleanCount($data) {
        global $admin;
        
        $result['count']['after'] = intval($data['inventory_before_added']) + intval($data['inventory_added']);
        $result['count']['inventoryAdded'] = intval($data['inventory_added']);
        $result['count']['before'] = intval($data['inventory_before_added']);

        $result['createdBy'] = $admin->formatResult( $admin->listOne( $data['added_by'] ), true, true);
        $result['date']['created'] = $data['create_time'];
        $result['date']['modified'] = $data['modify_time'];

        return $result;
    }
}
?>