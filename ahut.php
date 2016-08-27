<?php

include_once './LoginAhut.php';

$username = $_REQUEST['uid'];
$password = $_REQUEST['pwd'];

$ahut = new Ahut($username, $password);
$course = $ahut->getContents();
echo $course;
// $ahut->saveContents($course);

?>
