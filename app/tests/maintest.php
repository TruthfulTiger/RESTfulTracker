<?php
$f3 = Base::instance();
include_once('app/controllers/MainController.php');
// Set up
$f3->route('GET /maintest', function(){
    $test=new Test;
    echo "Are we working?";
    // This is where the tests begin
/*    $test->expect(
        is_callable('edit'),
        'edit() is a function'
    );*/
});


