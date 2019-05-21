<?php
try{
  $DB = new PDO("mysql:host=localhost;dbname=s18501","s18501","Kam.Skrz");
}catch(PDOException $e) {
  echo 'Błąd połączenia: '.$e->getMessage();
  exit;
}
?>