<?php
$pdo = new PDO('mysql:host=localhost;dbname=skjjapan_tracking','root','');
$st = $pdo->query("SELECT id,track_no,weight,cod,status FROM tracks WHERE REPLACE(track_no,'-','') = '492399141463'");
$rows = $st->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($rows, JSON_PRETTY_PRINT);
