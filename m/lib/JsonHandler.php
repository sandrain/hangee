<?php
// JsonHandler.php

class JsonHandler
{
    private $request;
    private $response;

    function __construct() {
        $this->response = new JsonResponse();
    }

    private function send() {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($this->response);
    }

    public function getRequest() {
        if (!isset($_POST['request'])) {
            $this->request = null;
            return null;
        }

        try {
            $this->request = json_decode(stripslashes($_POST['request']));
        }
        catch (Exception $e) {
            $this->request = null;
            return null;
        }

        return $this->request;
    }

    public function sendError($message) {
        $this->response->setError($message);
        $this->send();
    }

    public function sendData($data) {
        $this->response->setData($data);
        $this->send();
    }
}
?>
