<?php
// JsonResponse.php

class JsonResponse
{
    public $result;
    public $data;

    public function setError($message) {
        $this->result = "error";
        $this->data = $message;
    }

    public function setData($data) {
        $this->result = "success";
        $this->data = $data;
    }
}

?>
