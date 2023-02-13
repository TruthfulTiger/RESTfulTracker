<?php
class MainController extends Controller
{
    private string $table = 'test';

    function beforeroute()
    {

    }

    function render($f3){
        $data = $this->db->exec('SELECT * FROM '.$this->table);

        if ($f3->get('SESSION.logged_in')) {
            $this->user->getById($f3->get('SESSION.user_id'));
            $f3->set('user',$this->user);
        }
        $f3->set('name','world');
        $f3->set('test', $data);
        $f3->set('title', 'Tutorial page');
        $f3->set('view','tutorial.html');
    }

    public function add() {
        $this->honeytrap();
        $this->f3->scrub($_POST,'p; br;');
        $name = $this->f3->get('POST.name');
        try {
            if (isset($_POST['addSubmit'])) {
                $logger = new Log(date("Ymd").'add.log');
                $this->db->exec('INSERT INTO '.$this->table.' (name) VALUES (?)', $name);
                $logger->write($this->db->log());
                $this->flash->addMessage('Entry added.', 'success');
            }
        }
        catch (Exception $e) {
            $this->flash->addMessage('Error: '.$e, 'danger');
        }
        $this->f3->reroute('/');
    }

    public function edit() {
        $this->honeytrap();
        $this->f3->scrub($_POST,'p; br;');
        $id = $this->f3->get('POST.id');
        $name = $this->f3->get('POST.name');
        try {
            if (isset($_POST['editSubmit'])) {
                $logger = new Log(date("Ymd").'edit.log');
                $this->db->exec("UPDATE ".$this->table." SET name = '".$name."' WHERE id = ?",$id);
                $logger->write($this->db->log());
                $this->flash->addMessage('Entry updated.', 'success');
            }
        }
        catch (Exception $e) {
            $this->flash->addMessage('Error: '.$e, 'danger');
        }
        $this->f3->reroute('/');
    }

    public function delete() {
        try {
            $logger = new Log(date("Ymd").'delete.log');
            $id = $this->f3->get('PARAMS.id');
            $this->db->exec('DELETE FROM '.$this->table.' WHERE id=?',$id);
            $logger->write($this->db->log());
        }
        catch (Exception $e) {
            $this->flash->addMessage('Error: '.$e, 'danger');
        }
        $this->f3->reroute('/');
    }

    public function purge() {
        try {
            $logger = new Log(date("Ymd").'truncate.log');
            $this->db->exec('TRUNCATE TABLE '.$this->table);
            $logger->write($this->db->log());
        }
        catch (Exception $e) {
            $this->flash->addMessage('Error: '.$e, 'danger');
        }
        $this->f3->reroute('/');
    }
}