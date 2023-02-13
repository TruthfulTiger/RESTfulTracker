<?php

class UserGP extends \DB\SQL\Mapper
{
    protected $table;
    public function __construct(DB\SQL $db)
    {
        $this->table = 'usergp';
        parent::__construct($db,$this->table);
    }

    public function all(): int|array
    {
        return $this->db->exec('SELECT * FROM '.$this->table);
    }

    public function getByID($id): void
    {
        $this->load(array('userID=?',$id));
        $this->copyTo('POST');
    }

    public function add($id) {
        $this->db->exec('insert into '.$this->table.' VALUES (?, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0)', $id);
    }

    public function edit($id) {
        $this->load(array('userID=?',$id));
        $this->copyFrom('POST');
        $this->update();
    }
}