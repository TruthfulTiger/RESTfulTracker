<?php

class AspgenController extends Controller
{
    function beforeroute()
    {

    }
    function render($f3){
        $id = $this->f3->get('SESSION.user_id');
        $users = new User($this->db);
        $users->getById($id);
        $f3->set('title', 'Aspiration Randomiser');
        if (!empty($users))
            $this->f3->set('user', $users);
        $f3->set('view','aspgen.html');
    }
}