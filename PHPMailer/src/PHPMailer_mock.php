<?php

namespace PHPMailer\PHPMailer;

// Mock class for PHPMailer
class PHPMailer {
    public $Host;
    public $SMTPAuth;
    public $Username;
    public $Password;
    public $SMTPSecure;
    public $Port;
    public $From;
    public $FromName;
    public $addAddress_calls = [];
    public $isHTML_value;
    public $Subject;
    public $Body;
    public $ErrorInfo = "";
    public $exceptions = false;

    public function __construct($exceptions = false) {
        $this->exceptions = $exceptions;
    }

    public function isSMTP() { return true; }
    public function setFrom($from, $name = "") {
        $this->From = $from;
        $this->FromName = $name;
    }
    public function addAddress($address, $name = "") {
        $this->addAddress_calls[] = ["address" => $address, "name" => $name];
    }
    public function isHTML($ishtml) {
        $this->isHTML_value = $ishtml;
    }
    public function send() {
        // Simulate success by default
        if ($this->exceptions && empty($this->Host)) {
            $this->ErrorInfo = "SMTP host not set.";
            throw new \Exception($this->ErrorInfo); // Use the global Exception class
        }
        return true;
    }
}

?>
