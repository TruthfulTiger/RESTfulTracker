<?php

class Controller {

    protected $f3;
    protected $db;
    protected $flash;
    protected $hptrap;
    protected $user;

    function beforeroute() {
        if($this->f3->get('SESSION.logged_in'))
        {
            if(time() - $this->f3->get('SESSION.timestamp') > $this->f3->get('auto_logout'))
            {
                $this->f3->clear('SESSION');
                $this->f3->reroute('/login');
            }
            else {
                $this->f3->set('SESSION.timestamp', time());
            }
        }
        $csrf_page = $this->f3->get('PARAMS.0'); //URL route !with preceding slash!

        if( NULL === $this->f3->get('POST.session_csrf') )
        {
            $this->f3->CSRF = $this->f3->session->csrf();
            $this->f3->copy('CSRF','SESSION.'.$csrf_page.'.csrf');
        }
        if ($this->f3->VERB==='POST')
        {
            if(  $this->f3->get('POST.session_csrf') ==  $this->f3->get('SESSION.'.$csrf_page.'.csrf') )
            {	// Things check out! No CSRF attack was detected.
                $this->f3->set('CSRF', $this->f3->session->csrf()); // Reset csrf token for next post request
                $this->f3->copy('CSRF','SESSION.'.$csrf_page.'.csrf');  // copy the token to the variable
            }
            else
            {	// DANGER: CSRF attack!
                $this->f3->error(403);
            }
        }

        $access=Access::instance();
        $access->policy('allow'); // allow access to all routes by default
        $access->deny('/admin*');

        // admin routes
        $access->allow('/admin*','100'); //100 = admin ; 10 = superuser ; 1 = user
        $access->deny('/user*');
        // user login routes
        $access->allow('/user*',['100','10','1']);

        $access->authorize($this->f3->exists('SESSION.user_type') ? $this->f3->get('SESSION.user_type') : 0 );

    }

    function afterroute() {
        $dt = new DateTime();
        if($this->f3->get('SESSION.logged_in')) {
            $user_id=$this->f3->get('SESSION.user_id');
            $this->user->getById($user_id);
            $this->f3->set('user', $this->user);
            $dt->setTimezone(new DateTimeZone($this->user->timezone));
            $this->user->timeFormat === 12 ? $time = $dt->format("h:i A") : $time = $dt->format("H:i");
            $date = $dt->format($this->user->dateFormat);
            $this->f3->set('SESSION.user_theme', $this->user->theme);
            $dir=$this->f3->get('UPLOADS').$user_id.'/';
            if (!file_exists($dir)) {
                mkdir($dir,0755,TRUE);
            }
        } else {
            $dt->setTimezone(new DateTimeZone($this->f3->get('TZ')));
            $time = $dt->format("H:i");
            $date = $dt->format("Y-m-d");
        }
        $mdblight = '/assets/css/mdb/mdb.min.css';
        $stylelight = '/assets/css/style.min.css';
        $mdbdark = '/assets/css/mdb/mdb.dark.min.css';
        $styledark = '/assets/css/style.dark.min.css';
        $themeChoice = $_SESSION['user_theme'] ?? $_COOKIE['theme'] ?? null;
        if (!empty($themeChoice) && $themeChoice == 'dark') {
            $this->f3->set('mdbcss', $mdbdark);
            $this->f3->set('stylecss', $styledark);
        } else {
            $this->f3->set('mdbcss', $mdblight);
            $this->f3->set('stylecss', $stylelight);
        }
        $this->f3->set('date', $date);
        $this->f3->set('time', $time);
        echo Template::instance()->render('layout.htm');
    }

    function __construct() {
        $tpl=\Template::instance();
        $f3=Base::instance();
        $flash = Flash::instance();
        $hptrap = $f3->get('POST.hptrap');
        $db=new DB\SQL(
            $f3->get('db_dns') . $f3->get('db_name'),
            $f3->get('db_user'),
            $f3->get('db_pass'), array( \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION )
        );
        $user = new User(($db));

        // declare a new filter named 'price'
        $tpl->filter('simoleons',function($price) {
            return '§'.number_format($price);
        });

        $this->f3=$f3;
        $this->db=$db;
        $this->flash=$flash;
        $this->hptrap=$hptrap;
        $this->user=$user;
    }

    /**
     * Checks if the honeytrap field has been filled in - if it has, it's a bot
     * @return void
     */
    public function honeytrap(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($this->hptrap)) {
            $logger = new Log('app/logs/'.date("Ymd").'bots.log');
            $logger->write('Spambot thwarted at '.date("d/m/Y h:i:s a", time()));
            die('Nice try, Spam-A-Lot');
        }
    }

    public static function appendDateTimeToFileName($fileName) {
        $appended=date('_Y_m_d_H_i_s');
        $dotCount=substr_count($fileName,'.');
        if (!$dotCount) {
            return $fileName.$appended;
        }
        $extension=pathinfo($fileName,PATHINFO_EXTENSION);
        $fileName=pathinfo($fileName,PATHINFO_FILENAME);
        return $fileName.$appended.'.'.$extension;
    }
function error (){
    // recursively clear existing output buffers:
    while (ob_get_level())
        ob_end_clean();
    $this->f3->set('title', $this->f3->get('ERROR.code'));
    echo Template::instance()->render('error.html');
    $e = $this->f3->get('EXCEPTION');
    // There isn't an exception when calling `Base->error()`.
    if (!$e instanceof Throwable) {
        $logger = new Log($this->f3->get('LOGS').date("Ymd").'error.log');
        $logger->write( $this->f3->get('ERROR.code') . ": ". $this->f3->get('ERROR.text'). " trace: ". $this->f3->get('ERROR.trace'),'r'  );
      }
    }
}