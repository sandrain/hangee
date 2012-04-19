<?php
// Page.php

class Page
{
    private $title = null;      // Page title
    private $master = null;     // HTML template file
    private $headers = array(); // Addtional tag string to be placed in <head>
    private $script = null;     // Main javascript code file in <head>
    private $data = null;       // Encapsulated data for client
    private $content = null;    // Body content

    // setTitle
    // @title   Page title
    function setTitle($title) {
        $this->title = $title;
    }

    // setMaster
    // @name    Name of the master file. The file should be found in /master.
    function setMaster($name) {
        global $docroot;
        $this->master = "$docroot/master/$name.php";
    }

    // addHeader
    // @str     Addtional header string.
    function addHeader($str) {
        $this->headers[] = $str;
    }

    // setScript
    // @path    The main js file path.
    function setScript($path) {
        $this->script = $path;
    }

    // setData
    // @data    Encapculated data. We deliver json encoded @data to client
    //          using hidden field.
    function setData($data) {
        $this->data = $data;
    }

    // setContent
    // @content Page content out of the template.
    function setContent($content) {
        $this->content = $content;
    }

    // render
    // 
    // Render this web page.
    function render() {
        $title = $this->title;
        if ($this->script)
            $this->addHeader('<script type="text/javascript" src="'.$this->script.'"></script>');
        $headerContent = implode("\r\n", $this->headers)."\r\n";
        $pageData = $this->data ? json_encode($this->data) : '';
        $mainContent = $this->content ? $this->content : '';

        include_once($this->master);
    }
}
?>
