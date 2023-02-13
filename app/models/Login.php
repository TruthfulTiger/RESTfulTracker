<?php

class Login extends DB\SQL\Mapper {

    public function checklogin() {
        $user = new User($this->db);
        $user->set('username',$this->f3->get('POST.username'));
        $user->set('password',md5($this->f3->get('POST.password')));
        $auth=new \Auth($user, array('id'=>'username','pw'=>'password'));
        $auth->login($this->f3->get('POST.username'),md5($this->f3->get('POST.password')));
    }
}
