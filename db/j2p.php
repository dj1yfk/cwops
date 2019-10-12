<?php

# convert calls.json (output from parse.pl) to calls.phpserial 

$data =  json_decode(file_get_contents('calls.json'));
file_put_contents("calls.phpserial", serialize($data));


