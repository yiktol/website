<?php 
include 'rds.php';
?>
<html>
<head>
<title>Order Here</title>
<link rel="icon" type="image/png" href="assets/img/favicon.png">
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
* {
  box-sizing: border-box;
}

input[type=text], select, textarea {
  width: 100%;
  padding: 12px;
  border: 1px solid #ccc;
  border-radius: 4px;
  resize: vertical;
}

label {
  padding: 12px 12px 12px 0;
  display: inline-block;
}

input[type=submit] {
  background-color: #04AA6D;
  color: white;
  padding: 12px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  float: right;
  margin-top: 5px;
}

input[type=submit]:hover {
  background-color: #45a049;
}

.container {
  border-radius: 5px;
  background-color: #f2f2f2;
  padding: 20px;
  width: 50%;
}

.col-25 {
  float: left;
  width: 25%;
  margin-top: 6px;
}

.col-75 {
  float: left;
  width: 75%;
  margin-top: 6px;
}

/* Clear floats after the columns */
.row:after {
  content: "";
  display: table;
  clear: both;
}

/* Responsive layout - when the screen is less than 600px wide, make the two columns stack on top of each other instead of next to each other */
@media screen and (max-width: 600px) {
  .col-25, .col-75, input[type=submit] {
    width: 100%;
    margin-top: 0;
  }
}


.orders {
  border-radius: 5px;
  padding: 20px;
  width: 50%;
}

table {
  border-collapse: collapse;
  border-spacing: 0;
  width: 100%;
  border: 1px solid #ddd;
}

th, td {
  text-align: left;
  padding: 16px;
}

tr:nth-child(even) {
  background-color: #f2f2f2;
}
</style>


<script>
function validateForm() {
  var x = document.forms["orderform"]["firstname"].value;
  if (x == "" || x == null) {
    alert("Name must be filled out");
    return false;
  }
}
</script>
</head>

<body>
<h1>Orders</h1>
<?php

    /* Connect to MySQL and select the database. */
    $connection = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD);

    if (mysqli_connect_errno()) echo "Failed to connect to MySQL: " . mysqli_connect_error();

    $database = mysqli_select_db($connection, DB_DATABASE);

    /* Ensure that the ORDERS table exists. */
    VerifyOrdersTable($connection, DB_DATABASE);

    /* If input fields are populated, add a row to the ORDERS table. */
    $name = htmlentities($_POST['NAME']);
    $coffee = htmlentities($_POST['COFFEE']);
    $milk = htmlentities($_POST['MILK']);
    $size = htmlentities($_POST['SIZE']);
    $qty = htmlentities($_POST['QTY']);

    if (strlen($name) || strlen($coffee) || strlen($milk) || strlen($size) || strlen($qty)) {
    AddOrder($connection, $name, $coffee, $milk, $size, $qty);
    }
?>

<!-- Input form -->
<div class="container">
	<form name="orderform" action="<?PHP echo $_SERVER['SCRIPT_NAME'] ?>" method="POST" onsubmit="return validateForm()" required>
    <div class="row">
      <div class="col-25">
        <label for="fname">First Name</label>
      </div>
      <div class="col-75">
        <input type="text" id="fname" name="firstname" placeholder="Your name..">
      </div>
    </div>
    <div class="row">
      <div class="col-25">
        <label for="coffee">Coffee</label>
      </div>
      <div class="col-75">
        <select list="coffee" name="COFFEE"  maxlength="15" >
            <option value="Flat White">Flat White</option>
            <option value="Americano">Americano</option>
            <option value="Macchiato">Macchiato</option>
            <option value="Cappuccino">Cappuccino</option>
            <option value="Latte">Latte</option>
            <option value="Mocha">Mocha</option>
          </select>
      </div>
    </div>
    <div class="row">
      <div class="col-25">
        <label for="milk">Milk</label>
      </div>
      <div class="col-75">
        <select list="milk" name="MILK" placeholder="Select Milk" maxlength="15" >
            <option value="Full Cream">Full Cream</option>
            <option value="Skinny">Skinny</option>
            <option value="Soy">Soy</option>
            <option value="Almond">Almond</option>
            <option value="Oat">Oat</option>
        </select>
      </div>
    </div>
    <div class="row">
      <div class="col-25">
        <label for="size">Size</label>
      </div>
      <div class="col-75">
        <select list="size" name="SIZE" placeholder="Select Size" maxlength="10" >
            <option value="Small">Small</option>
            <option value="Regular">Regular</option>
            <option value="Large">Large</option>
        </select>
      </div>
    </div>
    <div class="row">
      <div class="col-25">
        <label for="quantity">Quantity</label>
      </div>
      <div class="col-75">
        <select list="qty" name="QTY" placeholder="Select Qty" maxlength="10" >
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
        </select>
      </div>
    </div>
    <div class="row">
      <input type="submit" value="Submit">
    </div>
  </form>
</div>
<!-- Display table data. -->
<div class="orders">
    <table id="ordertable">
        <tr>
        <th>ID</th>
        <th>NAME</th>
        <th>COFFEE</th>
        <th>MILK</th>
        <th>SIZE</th>
        <th>QTY</th>
        </tr>

    <?php

    $result = mysqli_query($connection, "SELECT * FROM ORDERS");

    while($query_data = mysqli_fetch_row($result)) {
        echo "<tr>";
        echo "<td>",$query_data[0], "</td>",
            "<td>",$query_data[1], "</td>",
            "<td>",$query_data[2], "</td>",
            "<td>",$query_data[3], "</td>",
            "<td>",$query_data[4], "</td>",
            "<td>",$query_data[5], "</td>";
        echo "</tr>";
    }
    ?>

    </table>
</div>

<!-- Clean up. -->
<?php

    mysqli_free_result($result);
    mysqli_close($connection);

?>

</body>
</html>


<?php

/* Add an order to the table. */
function AddOrder($connection, $name, $coffee, $milk, $size, $qty) {
    $n = mysqli_real_escape_string($connection, $name);
    $c = mysqli_real_escape_string($connection, $coffee);
    $m = mysqli_real_escape_string($connection, $milk);
    $s = mysqli_real_escape_string($connection, $size);
    $q = mysqli_real_escape_string($connection, $qty);

    $query = "INSERT INTO ORDERS (NAME, COFFEE, MILK, SIZE, QTY) VALUES ('$n', '$c', '$m', '$s', '$q');";

    if(!mysqli_query($connection, $query)) echo("<p>Error adding order data.</p>");
}

/* Check whether the table exists and, if not, create it. */
function VerifyOrdersTable($connection, $dbName) {
    if(!TableExists("ORDERS", $connection, $dbName))
    {
    $query = "CREATE TABLE ORDERS (
        ID int(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        NAME VARCHAR(45),
        COFFEE VARCHAR(20),
        MILK VARCHAR(20),
        SIZE VARCHAR(20),
        QTY VARCHAR(20)
        )";

    if(!mysqli_query($connection, $query)) echo("<p>Error creating table.</p>");
    }
}

/* Check for the existence of a table. */
function TableExists($tableName, $connection, $dbName) {
    $t = mysqli_real_escape_string($connection, $tableName);
    $d = mysqli_real_escape_string($connection, $dbName);

    $checktable = mysqli_query($connection,
        "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_NAME = '$t' AND TABLE_SCHEMA = '$d'");

    if(mysqli_num_rows($checktable) > 0) return true;

    return false;
}
?>
