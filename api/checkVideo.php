<?php
require_once('../vendor/autoload.php');

$client = new \Rutubeloader\RutubeClient();
$id = json_decode($_POST["id"]);
$response = $client->getVideo($id);

echo json_encode($response);
