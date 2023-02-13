<?php

class Test extends Controller
{
    public function all() {
        $this->db->exec('SELECT * FROM hours');
    }
    public function post($name) {
        $this->db->exec('INSERT INTO test (name) VALUES (?)',$name);
    }
    public function put($id, $name) {
        $this->db->exec('UPDATE test SET name = "'.$name.'" WHERE id=?',$id);
    }
    public function delete($id) {
        $this->db->exec('DELETE FROM test WHERE id=?',$id);
    }
}