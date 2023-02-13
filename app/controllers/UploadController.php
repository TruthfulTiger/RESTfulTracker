<?php
/**
 * Created by PhpStorm.
 * User: Sam
 * Date: 23/09/2018
 * Time: 22:59
 */

class UploadController extends Controller {
    private string $message;
    private string $status;
    private string $filename;
    private string $uploaded;
    private string $table;

    public function upload($table, $url) {
        $web = \Web::instance();
        $this->table = $table;
            $files = $web->receive(function($file,$formFieldName){
                /* looks like:
                  array(5) {
                      ["name"] =>     string(19) "csshat_quittung.png"
                      ["type"] =>     string(9) "image/png"
                      ["tmp_name"] => string(14) "/tmp/php2YS85Q"
                      ["error"] =>    int(0)
                      ["size"] =>     int(172245)
                    }
                */
                // $file['name'] already contains the slugged name now
                $this->uploaded = ($file['name']);
                $this->filename = basename($file['name']);

                // maybe you want to check the file size
                if($file['size'] > (5 * 1024 * 1024))  {
                    $this->message = 'Sorry, your file is too large.';
                    $this->status = 'danger';
                    return false; // this file is not valid, return false will skip moving it
                }

                // everything went fine, hurray!
                $this->message = 'Your file has been uploaded.';
                $this->status = 'success';
                return true; // allows the file to be moved from php tmp dir to your defined upload dir
            },
                true,
                true
            );
        $this->flash->addMessage($this->message, $this->status);
        if ($files)
            $this->moveFiles($this->table);
        $this->f3->reroute($url);
    }

    public function moveFiles($table) {
        if ($table) {
            $user_id=$this->f3->get('SESSION.user_id');
            $dir=$this->f3->get('UPLOADS').$user_id.'/';
            $target = $dir.$this->filename;

            rename($this->uploaded, $target);
            switch ($table) {
                case 'users':
                    $this->db->exec(
                        'UPDATE users SET image="/'.$target.'" WHERE id=?',
                        $user_id
                    );
                    break;
                    default:
                        break;
            }
        }
    }
}
