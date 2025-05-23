<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instance Metadata</title>
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
    <style>
        :root {
            /* AWS color scheme */
            --aws-orange: #FF9900;
            --aws-dark: #232F3E;
            --aws-light: #FFFFFF;
            --aws-gray: #EAEDED;
            --aws-border: #D5DBDB;
            --aws-text: #16191F;
        }
		
		.aws-orange {
            color: var(--aws-orange);
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Amazon Ember', Arial, sans-serif;
            background-color: var(--aws-gray);
            color: var(--aws-text);
            line-height: 1.6;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            background-color: var(--aws-dark);
            color: var(--aws-light);
            padding: 1rem;
            border-radius: 6px 6px 0 0;
            margin-bottom: 0;
        }
        
        .card {
            background-color: var(--aws-light);
            border-radius: 0 0 6px 6px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--aws-border);
            border-top: none;
        }
        
        h2 {
            margin: 0;
            font-size: 1.5rem;
        }
        
        .time-section {
            background-color: var(--aws-dark);
            color: var(--aws-light);
            padding: 1rem;
            border-radius: 6px;
            margin-top: 20px;
        }
        
        hr {
            border: 0;
            height: 1px;
            background-color: var(--aws-border);
            margin: 20px 0;
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
			<h2><span class="aws-orange">Amazon EC2 Instance Metadata</span></h2>
        </div>
        
        <div class="card">
            <?php include("get-index-meta-data.php"); ?>
            <?php include("get-cpu-load.php"); ?>
        </div>
        
        <div class="time-section">
            <p id="current-time">The Current Date and Time is loading...</p>
        </div>
    </div>

    <script>
        // Function to update the current time based on browser's location
        function updateCurrentTime() {
            const now = new Date();
            const options = { 
                hour: 'numeric', 
                minute: 'numeric', 
                second: 'numeric', 
                hour12: true,
                weekday: 'long',
                month: 'long',
                day: 'numeric',
                year: 'numeric'
            };
            
            const formattedDateTime = now.toLocaleString('en-US', options);
            document.getElementById('current-time').innerText = `The Current Date and Time is ${formattedDateTime}.`;
        }
        
        // Update time initially and then every second
        updateCurrentTime();
        setInterval(updateCurrentTime, 1000);
    </script>
</body>
</html>