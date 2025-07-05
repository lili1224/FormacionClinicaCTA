<?php
$files = glob('/mnt/videos/*.mp4');
$names = array_map('basename', $files);
header('Content-Type: application/json');
echo json_encode($names);