<?php
$upload_dir = "devoirs";
$devoirs = array(
    "all" => "Choisissez une option",
    "intrusion-windows" => "Test d'intrusion Windows",
    "intrusion-metasploitable" => "Test d'intrusion Metasploitable",
    "audit-web" => "Audit Site Web",
);

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$hostname = $_SERVER['HTTP_HOST'];
$baseUrl = $protocol . '://' . $hostname;