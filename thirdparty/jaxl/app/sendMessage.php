<?php

    $jaxl = require dirname(__FILE__) . '/init.php';

    // Post successful auth send desired message
    function send_message($payload, $jaxl) {
        global $argv;
        $type = empty($argv[5]) ? DEFAULT_MESSAGE_TYPE : $argv[5];
        $jaxl->sendMessage($argv[3], $argv[4], false, $type);
        $result = array('type' => 'result');
        echo serialize($result);
        $jaxl->shutdown();
    }

    // Register callback on required hooks 
    $jaxl->addPlugin('jaxl_post_auth', 'send_message');

    // Fire start Jaxl core
    $jaxl->startCore("stream");


