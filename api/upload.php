<?php
require_once('../vendor/autoload.php');

$client = new \Rutubeloader\RutubeClient();
$video = json_decode($_POST["video"]);
$response = $client->uploadVideo(...$video);

echo json_encode($response);
