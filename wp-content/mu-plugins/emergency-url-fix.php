<?php
// Emergency URL Fix - Output Buffer
ob_start(function($buffer) {
    return str_replace(
        ["http://www.eyalamit.co.il", "https://www.eyalamit.co.il"],
        "http://localhost:9090",
        $buffer
    );
});
