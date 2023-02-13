<?php

class HoursController extends Controller
{
    function beforeroute()
    {

    }

    function afterroute()
    {

    }

    function apiTest() {
        $hours = new Hour($this->db);
        $data = $hours->all();
        echo json_encode($data);
    }

    public function get($id) {
        $hours = new Hour($this->db);
        $data = $hours->getByID($id);
        echo json_encode($data);
    }
}