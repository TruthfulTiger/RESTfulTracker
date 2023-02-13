<?php

class FamiliesController extends Controller
{
    function beforeroute()
    {

    }
    function render($f3){
        $id = $this->f3->get('SESSION.user_id');
        $users = new User($this->db);
        $users->getById($id);
        if (!empty($users))
            $this->f3->set('user', $users);
        $f3->set('title', 'Family Generator');
        $f3->set('view','families.html');
    }
}