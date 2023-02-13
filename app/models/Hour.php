<?php

class Hour extends \DB\SQL\Mapper
{
    protected $table;
    public function __construct(DB\SQL $db)
    {
        $this->table = 'hour';
        parent::__construct($db,$this->table);
    }

    public function all(): int|array
    {
        return $this->db->exec('SELECT * FROM '.$this->table);
    }

    public function getByID($id): int|array
    {
        return $this->db->exec('SELECT * FROM '.$this->table.' WHERE id = ?',$id);
    }
}