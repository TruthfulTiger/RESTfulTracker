<?php

class APIController extends Controller
{
    function beforeroute()
    {

    }

    function afterroute()
    {

    }

    function getTest() {
        $json = $this->db->exec('SELECT * FROM test');
        echo json_encode($json);
    }
}