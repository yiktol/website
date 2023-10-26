<?php 
include 'rds.php';
?>
<html>
<head>
<title>Order Here</title>
<link rel="icon" type="image/png" href="assets/img/favicon.png">
<style>
#customers {
    font-family: Arial, Helvetica, sans-serif;
    border-collapse: collapse;
    width: 100%;
    table-layout: fixed;
}

#customers td, #customers th {
    border: 1px solid #ddd;
    padding: 8px;
}

#customers tr:nth-child(even){background-color: #f2f2f2;}
#customers tr:hover {background-color: #ddd;}
#customers th {
    padding-top: 12px;
    padding-bottom: 12px;
    text-align: left;
    background-color: #4CAF50;
    color: white;
}
.form {
  border: 2px outset red;
  background-color: lightblue;
  text-align: center;
}
</style>
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
<div class="form">
<form action="<?PHP echo $_SERVER['SCRIPT_NAME'] ?>" method="POST">
    <table id="customers">
    <tr>
        <td>NAME</td>
        <td>COFFEE</td>
        <td>MILK</td>
        <td>SIZE</td>
        <td>QTY</td>
        <td>SUBMIT</td>
    </tr>
    <tr>
        <td>
        <input type="text" name="NAME" placeholder="Enter Customer Name" maxlength="30" size="25" />
        </td>
        <td>
        <select list="coffee" name="COFFEE"  maxlength="15" >
            <option value="Flat White">Flat White</option>
            <option value="Americano">Americano</option>
            <option value="Macchiato">Macchiato</option>
            <option value="Cappuccino">Cappuccino</option>
            <option value="Latte">Latte</option>
            <option value="Mocha">Mocha</option>
        </select>
        </td>
        <td>
        <select list="milk" name="MILK" placeholder="Select Milk" maxlength="15" >
            <option value="Full Cream">Full Cream</option>
            <option value="Skinny">Skinny</option>
            <option value="Soy">Soy</option>
            <option value="Almond">Almond</option>
            <option value="Oat">Oat</option>
        </select>
        </td>
        <td>
        <select list="size" name="SIZE" placeholder="Select Size" maxlength="10" >
            <option value="Small">Small</option>
            <option value="Regular">Regular</option>
            <option value="Large">Large</option>
        </select>
        </td>
        <td>
        <select list="qty" name="QTY" placeholder="Select Qty" maxlength="10" >
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
        </select>
        </td>
        <td>
        <input type="submit" value="Order" />
        </td>
    </tr>
    </table>
</form>
</div>
<!-- Display table data. -->
<table id="customers">
    <tr>
    <td>ID</td>
    <td>NAME</td>
    <td>COFFEE</td>
    <td>MILK</td>
    <td>SIZE</td>
    <td>QTY</td>
    </tr>

<?php

$connectionro = mysqli_connect(DB_SERVER_RO, DB_USERNAME, DB_PASSWORD);

if (mysqli_connect_errno()) echo "Failed to connect to MySQL: " . mysqli_connect_error();

$database = mysqli_select_db($connectionro, DB_DATABASE);

$result = mysqli_query($connectionro, "SELECT * FROM ORDERS");

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
