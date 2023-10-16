<html>
<head>
<style>
h1 {
  font-family: Arial, Helvetica, sans-serif;
  color: green;
}
h2 {
  font-family: Arial, Helvetica, sans-serif;
}

h3 {
  font-family: Arial, Helvetica, sans-serif;
}

p {
  font-size: 20px;
}

table {
  border: 0px solid;
  font-size: 20px;
  width: 60%;
  word-wrap: normal;
  table-layout: auto;
}
th, td {
  padding: 5px;
  text-align: left;
}

th {
  background-color: #04AA6D;
  color: white;
  height: 40px;
}
</style>

<title>Metadata</title>
<link rel="icon" type="image/png" href="assets/img/favicon.png">
</head>
<body>
<br>

<div align="left">
<?php include("get-index-meta-data.php"); ?>
</div>

<div align="left">
<?php include("get-cpu-load.php"); ?>
</div>


<hr />


<?php
echo "<p>The Current Date and Time is ";
date_default_timezone_set("Asia/Singapore");
print date("h:i:s A l, F j Y.", time());
echo "</p>"
?>


</body>
</html>
