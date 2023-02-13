<?php
// Setup MySQL DB Connection
$fw->set('DB', new DB\SQL(
		'mysql:host='.$fw->get('mysql.host').';port='.$fw->get('mysql.port').';dbname='.$fw->get('mysql.database').';charset='.$fw->get('mysql.charset'),
		$fw->get('mysql.username'),
		$fw->get('mysql.password',
		[
			PDO::ATTR_EMULATE_PREPARES => false,
			PDO::ATTR_STRINGIFY_FETCHES => false,
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		]
	)
));
$fw->set('copyyear',date("Y"));
// Additional Template Headers for Convenience
$template = Template::instance();
$template->filter('length', 'Template_Helper::length');
$template->filter('convertDate', 'Date::convertUtcDateToTimeZone');
$template->filter('convertDateTime', 'Date::convertUtcTimestampToTimeZone');
// declare a new filter named 'simoleons'
$template->filter('simoleons',function($price) {
  return '§'.number_format($price);
});

// Extra plugins
Falsum\Run::handler(); // pretty error handling in debug mode

// Template sub-sections
\Template\Tags\Section::init('section');
\Template\Tags\Inject::init('inject');

// Error handler in prod mode
if ($fw->get('DEBUG') < 3) {
  $fw->set('ONERROR',function($fw){
    echo \Template::instance()->render('error.html');
    $e = $fw->get('EXCEPTION');
    // There isn't an exception when calling `Base->error()`.
    if (!$e instanceof Throwable) {
      $logger = new Log(date("Ymd").'error.log');
      $logger->write( $fw->get('ERROR.code') . ": ". $fw->get('ERROR.text'). " trace: ". $fw->get('ERROR.trace'),'r'  );
    }
  });
}
date_default_timezone_set("UTC");
$fw->LANGUAGE = $fw->get('sitelang');

$fw->logger = new Log(date("Ymd").'.log');
$fw->session = new Session();
// Gravatar handler
\Gravatar::instance()->get('email', [
  's' => 64, // size
  'd' => 'retro', // Default image
  'r' => 'g', // rating
  'mx' => false,
]);
