<?php

class TestController extends Controller
{
    function beforeroute()
    {

    }

    function afterroute()
    {

    }

    function apiTest() {
        $hours = new Hours($this->db);
        $data = $hours->all();
        echo json_encode($data);
    }

    public function get() {
        $test = new Test();
        $data = $test->all();
        echo json_encode($data);
    }
    public function post() {
        $test = new Hours($this->db);
        $data = $test->post();
        echo json_encode($data);
    }
    public function put() {
        $test = new Test($this->db);
        $id = $this->f3->get('PARAMS.1');
        $name = $this->f3->get('PARAMS.2');
        $test->put($id,$name);
    }
    public function delete() {
        $test = new Test($this->db);
        $id = $this->f3->get('PARAMS.1');
        $test->delete($id);
    }
}