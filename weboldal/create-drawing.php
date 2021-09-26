<?php
$img = filter_input(INPUT_POST, 'image', FILTER_SANITIZE_URL);

$img = str_replace(' ', '+', str_replace('data:image/png;base64,', '', $img));
$data = base64_decode($img);

file_put_contents(__DIR__.'/images/'. 'image2' .'.png', $data);

header($_SERVER['REQUEST_URI'].'/')
?>