<?php
/**
 * System monitoring utility
 * Retrieves CPU utilization and uptime information
 */

// Get CPU idle percentage using vmstat
$idleCpu = exec('vmstat 1 2 | awk \'{ for (i=1; i<=NF; i++) if ($i=="id") { getline; getline; print $i }}\'');

// Get system uptime in human-readable format
$uptime = exec('uptime -p');

// Calculate CPU load percentage
$load = 100 - (int)$idleCpu;

// Define load severity thresholds
function getLoadClass($loadValue) {
    if ($loadValue < 50) {
        return 'load-normal';
    } elseif ($loadValue < 80) {
        return 'load-medium';
    } else {
        return 'load-high';
    }
}

$loadClass = getLoadClass($load);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Monitoring</title>
    <style>
        /* AWS Color Scheme */
        body {
            font-family: 'Amazon Ember', Arial, sans-serif;
            background-color: #f8f8f8;
            color: #232f3e;
            padding: 15px;
            margin: 0;
        }
        
        .monitoring-card {
            background-color: #ffffff;
            border-radius: 6px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.08);
            padding: 15px 20px;
            max-width: 500px;
            margin: 20px auto;
        }
        
        h2 {
            color: #232f3e;
            border-bottom: 1px solid #eaeded;
            padding-bottom: 10px;
            margin-top: 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        
        td {
            padding: 12px 5px;
            border-bottom: 1px solid #eaeded;
        }
        
        td:first-child {
            font-weight: 500;
        }
        
        .metric-value {
            padding: 4px 8px;
            border-radius: 4px;
            margin-left: 10px;
            font-weight: bold;
        }
        
        .load-normal {
            background-color: #1d8102;
            color: white;
        }
        
        .load-medium {
            background-color: #ff9900;
            color: black;
        }
        
        .load-high {
            background-color: #d13212;
            color: white;
        }
        
        .uptime {
            color: #0073bb;
        }
    </style>
</head>
<body>
    <div class="monitoring-card">
        <h2>System Status</h2>
        <table>
            <tr>
                <td>Current CPU Load:</td>
                <td>
                    <?= $load ?>%
                    <span class="metric-value <?= $loadClass ?>"><?= $load ?>%</span>
                </td>
            </tr>
            <tr>
                <td>Instance Uptime:</td>
                <td class="uptime"><?= htmlspecialchars($uptime) ?></td>
            </tr>
        </table>
    </div>
</body>
</html>