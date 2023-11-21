<html>
<head>
<link rel="stylesheet" href="assets/css/coffee.css">

<title>Metadata</title>
<link rel="icon" type="image/png" href="assets/img/favicon.png">
</head>
<body>
    <div class="row">
        <div class="leftcolumn">

            <div class="header">
                <h2>Instance Metadata</h2>
            </div>

            <div class="card">

                <?php include("get-index-meta-data.php"); ?>

                <?php include("get-cpu-load.php"); ?>
            </div>
                
            <hr>
            <div class="feader">

            <?php
            echo "<p>The Current Date and Time is ";
            date_default_timezone_set("Asia/Singapore");
            print date("h:i:s A l, F j Y.", time());
            echo "</p>"
            ?>

            </div>
        </div>
    </div>
</body>
</html>
