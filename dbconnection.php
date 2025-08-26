<?php
 $servername = "localhost";   
 $username = $_POST["txtarmyid"];
 $password = $_POST["txtpass"];
 $dbname = "militarydb";

 
//Connection
$con=mysqli_connect($servername, $username, $password);

//Check connection
if($con->connect_error){
    die("Connection failed:" . $con->connect_error);
}
?>