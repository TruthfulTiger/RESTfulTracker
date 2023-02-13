<?php

use n0nag0n\Xss_Filter as Xss_FilterAlias;

class UserController extends Controller
{

    public function confirm_registration()
    {
        $user = new User($this->db);
        $user->getByHash($this->f3->get('GET.h'));
        if(strcmp($this->f3->get('POST.hash'),$this->f3->get('GET.h'))===0)
        {
            $user->activate($user->id);
            $this->f3->set('POST.registration_ok',true);
            $this->f3->set('view','user/confirm_registration.htm');
        }
        else
        { //check if account is already activated
            $user->checkActivatedHash($this->f3->get('GET.h'));
            if(strcmp($this->f3->get('POST.hash'),$this->f3->get('GET.h'))===0)
            {
                $this->flash->addMessage($this->f3->get('i18n_alreadyactivated'), 'danger');
                $this->f3->reroute('/');
            }
            else
            {
                $this->flash->addMessage($this->f3->get('i18n_reg_conf_failed'), 'danger');
                $this->f3->reroute('/');
            }
        }
    }

    public function update_registration()
    {
        $this->honeytrap();
        // first activation posted
        $user = new User($this->db);
        $sessionlogin = false;

        $this->f3->set('POST.activated',1);

        $user->edit($this->f3->get('POST.user_id'),$this->f3->get('POST'));

        $this->f3->copy('POST','SESSION');
        $this->flash->addMessage($this->f3->get('i18n_reg_update_success'), 'success');
        $this->f3->reroute('/login');
    }

    public function pw_reset()
    {
        $this->honeytrap();
        $user = new User($this->db);
        $user->checkActivatedHash($this->f3->get('GET.h'));
        $this->f3->set('SESSION.user_id',$user->id);

        if($this->f3->exists('POST.reset_pw')){
            $pwcheck = $this->check_password( $this->f3->get('POST.new_password') , $this->f3->get('POST.confirm'));
            if (strlen($pwcheck) > 0) //pwcheck error message returned
            {
                $this->flash->addMessage($pwcheck, 'danger');
                $this->f3->set('view','user/change-pw.htm');
            }
            else{
                if($this->setpw( $this->f3->get('POST.new_password'), $user->id))
                {
                    $this->f3->reroute('/login');
                }
                else{
                    $this->f3->error(403);
                }
            }
        }
        else if(strcmp($this->f3->get('POST.hash'),$this->f3->get('GET.h'))===0)
        {
            $this->f3->set('view','user/change-pw.htm');
        }
        else
        {
            $this->flash->addMessage($this->f3->get('i18n_register_oops'), 'danger');
            $this->f3->set('view','user/change-pw.htm');
        }
    }

    private function setpw( $newpw, $user_id )
    {
        $this->honeytrap();
        $user = new User($this->db);
        $user->getById($user_id);

        $password = password_hash($newpw, PASSWORD_BCRYPT);

        //check if user id = session id for security
        if($user_id == $this->f3->get('SESSION.user_id'))
        {
            $this->f3->set('POST.password', $password);
            $user->edit($user_id, $this->f3->get('POST'));
            return true;
        }
        else {
            return false;
        }
    }

    public function edit_registration()
    {
        $this->honeytrap();
        $user = new User($this->db);
        if($this->f3->VERB==="POST")
        {
            $user_id=$this->f3->get('SESSION.user_id');
            if($this->f3->get('POST.user_id') == $user_id)
            {
                if(null!==$this->f3->get('POST.password'))
                {
                    $passwordcheck = $this->check_password($this->f3->get('POST.password'), $this->f3->get('POST.confirm'));
                    if( $passwordcheck==="" && $this->setpw( $this->f3->get('POST.password'), $this->f3->get('POST.user_id')) )
                    {
                        $this->flash->addMessage($this->f3->get('i18n_password_changed'), 'success');
                    }
                    else
                    {
                        $this->flash->addMessage($passwordcheck, 'danger');
                    }
                }

                $user->getById($user_id);
                $user->edit($user_id, $this->f3->get('POST'));
                $this->f3->set('SESSION.logged_in', 1);
                $user->login($user->id);
            }
            else {
                $this->f3->error(403);
            }
        }
        $this->f3->copy('SESSION','POST');
        $this->f3->set('user',$user);
        $this->f3->set('title',$this->f3->get('i18n_changepassword'));
        $this->f3->set('view','user/editregistration.htm');
    }

    public function success()
    {
        $this->f3->set('title', $this->f3->get('Success'));
        $this->f3->set('view', 'user/success.htm');
    }

    public function sendactmail($email, $hash)
    {
        $confirmation_link = $this->f3->get('SCHEME')."://".$this->f3->get('HOST')."/confirm_registration?h=".$hash;
        $mail = new Mail();
        $mail->send( // sender, recipient, subject, msg
            $this->f3->get('from_email') ,
            $email,
            $this->f3->get('i18n_confirmation_mail_subject') . " " . $this->f3->get('HOST'),
            $this->f3->get('i18n_confirmation_mail_message')."<a href=\"".$confirmation_link."\">".$confirmation_link . "</a>"
        );

    }

    private function pw_reset_mail($email, $hash)
    {
        $confirmation_link = $this->f3->get('SCHEME')."://".$this->f3->get('HOST')."/pw_reset?h=".$hash;
        $mail = new Mail();
        $mail->send( // sender, recipient, subject, msg
            $this->f3->get('from_email') ,
            $email,
            $this->f3->get('i18n_confirmation_mail_subject') . " " . $this->f3->get('HOST'),
            $this->f3->get('i18n_reset_pw_mail_message')."<a href=\"".$confirmation_link."\">".$confirmation_link . "</a>"
        );

    }

    public function sendactivationmail()
    {
        if($this->f3->exists('POST.sendmail'))
        {
            $hash=$this->createHash();
            $user = new User($this->db);
            $user->getByEmail($this->f3->get('POST.email'));
            $this->f3->set('POST.hash', $hash);
            $user->edit($user->id);
            $this->sendactmail($this->f3->get('POST.email'), $hash);
            $this->f3->set('page_head',$this->f3->get('i18n_registration'));
            $this->f3->set('title',$this->f3->get('i18n_registration'));
            $this->flash->addMessage($this->f3->get('i18n_conf_mail_sent'), 'success');
            $this->f3->reroute('/');
        }
        else
        {
            $this->f3->set('title','Send activation email');
            $this->f3->set('view','user/send_activation_mail.htm');
        }
    }

    private function check_password($pw, $confirm)
    {
        if(strlen($pw) < 8)
        {
            return $this->f3->get('i18n_password_too_short');
        }
        else if($pw !== $confirm)
        {
            return $this->f3->get('i18n_user_wrong_confirm');
        }
        else
        {
            return "";
        }
    }

    public function create()
    {
        $this->honeytrap();
        if($this->f3->exists('POST.create'))
        {
            $pwcheck = $this->check_password( $this->f3->get('POST.password'), $this->f3->get('POST.confirm'));
            if (strlen($pwcheck) > 0)
            {
                $this->flash->addMessage($pwcheck, 'danger');
                $this->f3->reroute('/register');
            }
            else{
                $password = password_hash($this->f3->get('POST.password'), PASSWORD_BCRYPT);
                $this->f3->set('POST.password', $password);

                $hash = $this->createHash();
                $this->f3->set('POST.hash', $hash);
                $user = new User($this->db);
                $user_added=$user->add($this->f3->get('POST'));

                if($user_added==1)
                {
                    $this->sendactmail($this->f3->get('POST.email'), $hash);

                    $this->f3->set('page_head',$this->f3->get('i18n_registration'));
                    $this->f3->set('title',$this->f3->get('i18n_registration'));
                    $this->flash->addMessage($this->f3->get('i18n_conf_mail_sent'), 'info');
                    $this->f3->reroute('/');
                }
                else if($user_added==10) //user taken
                {
                    $this->flash->addMessage($this->f3->get('i18n_username_taken'), 'danger');
                    $this->f3->reroute('/register');
                }
                else if($user_added==11) //email taken
                {
                    if($user->activated==0)
                    {
                        $this->flash->addMessage($this->f3->get('i18n_not_activated'), 'danger');
                    }
                    else
                    {
                        $this->flash->addMessage($this->f3->get('i18n_email_taken'), 'danger');
                    }
                    $this->f3->reroute('/register');
                }
            }
        }
        else
        {
            $this->f3->set('title',$this->f3->get('i18n_registration'));
            $this->f3->set('view','user/create.htm');
        }
    }

    public function login()
    {
        $this->honeytrap();
        if( $this->f3->get('SESSION.logged_in'))
        {
            $this->f3->reroute('/');
        }
        else if($this->f3->exists('POST.login')) // OR $this->f3->VERB=='POST'
        {
            $ip = $_SERVER['REMOTE_ADDR'];
            if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
            $user_id="not logged in";

            $user = new User($this->db);
            $user->getByName( $this->f3->get('POST.username') );

            if($user->dry() || ! password_verify($this->f3->get('POST.password'), $user->password))
            {
                $this->f3->logger->write( "LOG IN: ".$this->f3->get('POST.username')." login failed (ip: " .$ip .")",'r'  );
                sleep(2);
                $this->flash->addMessage($this->f3->get('i18n_wrong_login'), 'danger');
                $this->f3->set('page_head','Login');
                $this->f3->set('title','Login');
                $this->f3->set('view','user/login.htm');
            }
            else if ($user->activated===0)
            {
                $this->f3->logger->write( "LOG IN: ".$this->f3->get('POST.username')." not activated (ip: " .$ip .")",'r'  );
                $this->flash->addMessage($this->f3->get('i18n_not_activated'), 'danger');
                $this->f3->set('page_head','Login');
                $this->f3->set('title','Login');
                $this->f3->set('view','user/login.htm');
            }
            else
            {
                $this->f3->set('SESSION.user_id', $user->id);
                $user->login($user->id);
                $this->f3->logger->write( "LOG IN: ".$this->f3->get('POST.username')." login success (ip: " .$ip .")",'r'  );
                $this->f3->set('SESSION.logged_in', 'true');
                $this->f3->set('SESSION.timestamp', time());
                $this->f3->reroute('/');
            }
        }
        else
        {
            $this->f3->set('page_head','Login');
            $this->f3->set('title','Login');
            $this->f3->set('view','user/login.htm');
        }
    }

    public function logout()
    {
        $this->f3->clear('SESSION');
        $this->flash->addMessage('You have now logged out.', 'info');
        $this->f3->reroute('/');
    }

    public function update()
    {
        $this->honeytrap();
        $user = new User($this->db);

        if($this->f3->exists('POST.update'))
        {
            $user->edit($this->f3->get('POST.id'),$this->f3->get('POST'));
            $this->flash->addMessage('User Updated', 'success');
        }
        else
        {
            $user->getById($this->f3->get('PARAMS.id'));
            $this->f3->set('user',$user);
            $this->f3->set('page_head',$this->f3->get('i18n_changepassword'));
            $this->f3->set('title',$this->f3->get('i18n_changepassword'));
            $this->f3->set('view','admin/update.htm');
        }
    }

    public function lostpassword()
    {
        $this->honeytrap();
        if($this->f3->exists('POST.reset_pw'))
        {
            $hash=$this->createHash();
            $user = new User($this->db);
            $user->getByEmail($this->f3->get('POST.email'));
            if(! $user->dry()){
                $this->f3->set('POST.hash', $hash);
                $user->edit($user->id, $this->f3->get('POST'));
                $this->pw_reset_mail($this->f3->get('POST.email'), $hash);
            }
            $this->f3->set('page_head', $this->f3->get('i18n_new_password_request_header'));
            $this->f3->set('title', $this->f3->get('i18n_new_password_request_header'));
            $this->flash->addMessage($this->f3->get('i18n_new_password_request'), 'success');
            $this->f3->reroute('/');
        }
        else
        {
            $this->f3->set('view','user/reset-pw.htm');
        }
    }

    private function createHash()
    { //this should be somewhat unpredictible
        return md5( str_shuffle(time(). $this->f3->get('POST.username') . $this->f3->get('POST.email') ) );
    }

    public function delete()
    {
        if($this->f3->exists('PARAMS.id'))
        {
            $user = new User($this->db);
            $user->delete($this->f3->get('PARAMS.id'));
        }
        $this->f3->reroute('/admin/users');
    }

    public function profile()
    {
        $this->honeytrap();
        $user = new User($this->db);
        $url = $this->f3->get('PARAMS.0');
        $id = $this->f3->get('SESSION.user_id');
        if ($this->f3->exists('POST.edit')) {
            $post = Xss_FilterAlias::filter('POST');
            $user->edit($this->f3->get('SESSION.user_id'),$post);
            $this->flash->addMessage($this->f3->get('i18n_profile_updated'), 'success');
        } else {
            $user->getById($id);

            if ($user->dry()) { //throw a 404, order does not exist
                $this->f3->error(404);
            }
        }
        if (isset($_POST['upload'])) {
            $is = new UploadController();
            $is->upload('users', $url);
        }
        $this->f3->set('title', 'Profile');
        $this->f3->set('user',$user);
        $this->f3->set('view', 'user/profile.html');
    }

    public function games()
    {
        $this->honeytrap();
        $user = new User($this->db);
        $ug = new UserGame($this->db);
        $uep2 = new UserEP2($this->db);
        $uep3 = new UserEP3($this->db);
        $uep4 = new UserEP4($this->db);
        $ugp = new UserGP($this->db);
        $id = $this->f3->get('SESSION.user_id');
        if ($this->f3->exists('POST.edit')) {
            // Ticking boxes
            // Simns 2
            $this->f3->set('POST.sims2',isset($_POST["sims2"])?1:0);
            $this->f3->set('POST.sims3',isset($_POST["sims3"])?1:0);
            $this->f3->set('POST.sims4',isset($_POST["sims4"])?1:0);
            $this->f3->set('POST.uni2',isset($_POST["uni2"])?1:0);
            $this->f3->set('POST.nl',isset($_POST["nl"])?1:0);
            $this->f3->set('POST.ofb',isset($_POST["ofb"])?1:0);
            $this->f3->set('POST.pets2',isset($_POST["pets2"])?1:0);
            $this->f3->set('POST.sns2',isset($_POST["sns2"])?1:0);
            $this->f3->set('POST.ft',isset($_POST["ft"])?1:0);
            $this->f3->set('POST.bv',isset($_POST["bv"])?1:0);
            $this->f3->set('POST.al',isset($_POST["al"])?1:0);
            // Sims 3
            $this->f3->set('POST.uni3',isset($_POST["uni3"])?1:0);
            $this->f3->set('POST.wa',isset($_POST["wa"])?1:0);
            $this->f3->set('POST.amb',isset($_POST["amb"])?1:0);
            $this->f3->set('POST.pets3',isset($_POST["pets3"])?1:0);
            $this->f3->set('POST.sns3',isset($_POST["sns3"])?1:0);
            $this->f3->set('POST.ln',isset($_POST["ln"])?1:0);
            $this->f3->set('POST.gns',isset($_POST["gns"])?1:0);
            $this->f3->set('POST.st',isset($_POST["st"])?1:0);
            $this->f3->set('POST.sn',isset($_POST["sn"])?1:0);
            $this->f3->set('POST.ip',isset($_POST["ip"])?1:0);
            $this->f3->set('POST.itf',isset($_POST["itf"])?1:0);
            // Sims 4
            $this->f3->set('POST.uni4',isset($_POST["uni4"])?1:0);
            $this->f3->set('POST.gtw',isset($_POST["gtw"])?1:0);
            $this->f3->set('POST.gt',isset($_POST["gt"])?1:0);
            $this->f3->set('POST.pets4',isset($_POST["pets4"])?1:0);
            $this->f3->set('POST.sns4',isset($_POST["sns4"])?1:0);
            $this->f3->set('POST.cl',isset($_POST["cl"])?1:0);
            $this->f3->set('POST.gf',isset($_POST["gf"])?1:0);
            $this->f3->set('POST.il',isset($_POST["il"])?1:0);
            $this->f3->set('POST.eco',isset($_POST["eco"])?1:0);
            $this->f3->set('POST.se',isset($_POST["se"])?1:0);
            $this->f3->set('POST.cot',isset($_POST["cot"])?1:0);
            $this->f3->set('POST.hsy',isset($_POST["hsy"])?1:0);
            $this->f3->set('POST.otr',isset($_POST["otr"])?1:0);
            $this->f3->set('POST.sd',isset($_POST["sd"])?1:0);
            $this->f3->set('POST.dno',isset($_POST["dno"])?1:0);
            $this->f3->set('POST.vp',isset($_POST["vp"])?1:0);
            $this->f3->set('POST.ph',isset($_POST["ph"])?1:0);
            $this->f3->set('POST.ja',isset($_POST["ja"])?1:0);
            $this->f3->set('POST.sv',isset($_POST["sv"])?1:0);
            $this->f3->set('POST.rom',isset($_POST["rom"])?1:0);
            $this->f3->set('POST.jtb',isset($_POST["jtb"])?1:0);
            $this->f3->set('POST.dhd',isset($_POST["dhd"])?1:0);
            $this->f3->set('POST.mws',isset($_POST["mws"])?1:0);
            $this->f3->set('POST.ww',isset($_POST["ww"])?1:0);

            $post = Xss_FilterAlias::filter('POST');
            $user->edit($this->f3->get('POST.id'),$post);
            $ug->edit($id);
            $uep2->edit($id);
            $uep3->edit($id);
            $uep4->edit($id);
            $ugp->edit($id);
            $this->flash->addMessage($this->f3->get('i18n_account_updated'), 'success');
        } else {
            $ug->getByID($id);
            $uep2->getByID($id);
            $uep3->getByID($id);
            $uep4->getByID($id);
            $ugp->getByID($id);
            if ($ug->dry()) { //throw a 404, order does not exist
                $this->f3->error(404);
            }
        }
        $this->f3->set('usergame', $ug);
        $this->f3->set('userep2', $uep2);
        $this->f3->set('userep3', $uep3);
        $this->f3->set('userep4', $uep4);
        $this->f3->set('usergp', $ugp);
        $this->f3->set('title', 'Games owned');
        $this->f3->set('view', 'user/games.html');
    }

    public function account()
    {
        $this->honeytrap();
        $user = new User($this->db);
        $id = $this->f3->get('SESSION.user_id');
        if ($this->f3->exists('POST.edit')) {
            $post = Xss_FilterAlias::filter('POST');
            $user->edit($this->f3->get('POST.id'),$post);
            $this->flash->addMessage($this->f3->get('i18n_account_updated'), 'success');
        } else {
            $user->getById($id);
            if ($user->dry()) { //throw a 404, order does not exist
                $this->f3->error(404);
            }
        }
        $this->f3->set('user',$user);
        $this->f3->set('title', 'Account settings');
        $this->f3->set('view', 'user/account.html');
    }
}