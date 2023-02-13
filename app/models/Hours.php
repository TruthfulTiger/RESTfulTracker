<?php

class Hours extends \DB\SQL\Mapper
{
    public function __construct(DB\SQL $db)
    {
        parent::__construct($db,'hours');
    }

    public function all() {
        return $this->db->exec('SELECT * FROM hours');
    }

    public function post($military, $meridian) {
        return $this->db->exec('INSERT INTO hours (militaryTime, meridianTime) VALUES (?, ?)',
        $military, $meridian
        );
    }

    public function put($id, $field, $value) {
        $this->db->exec('UPDATE hours SET '.$field.' = ? WHERE id=?',$value, $id);
    }

    public function delete($id) {
        $this->db->exec('DELETE FROM hours WHERE id=?',$id);
    }
}