<?php
// Initialize curl session
$ch = curl_init();

/**
 * Fetch AWS EC2 instance metadata
 * 
 * @param resource $curlHandler The curl handle
 * @param string $path The metadata path to fetch
 * @param array $headers Headers to include in the request
 * @return string The response from the metadata service
 */
function fetchMetadata($curlHandler, $path, $headers) {
    $baseUrl = 'http://169.254.169.254/latest/';
    $fullUrl = $baseUrl . $path;
    
    curl_setopt($curlHandler, CURLOPT_URL, $fullUrl);
    curl_setopt($curlHandler, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curlHandler, CURLOPT_CUSTOMREQUEST, "GET");
    
    return curl_exec($curlHandler);
}

// Get a valid token for metadata service
$tokenHeaders = ['X-aws-ec2-metadata-token-ttl-seconds: 60'];
$tokenUrl = "http://169.254.169.254/latest/api/token";

curl_setopt($ch, CURLOPT_URL, $tokenUrl);
curl_setopt($ch, CURLOPT_HTTPHEADER, $tokenHeaders);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
$token = curl_exec($ch);

// Set headers for metadata requests
$headers = ['X-aws-ec2-metadata-token: ' . $token];

// Define metadata to retrieve
$metadataItems = [
    'instance-id' => 'Instance ID',
    'instance-type' => 'Instance Type',
    'ami-id' => 'AMI ID',
    'hostname' => 'Hostname',
    'local-ipv4' => 'IPv4 Address',
    'ipv6' => 'IPv6 Address',
    'placement/availability-zone' => 'Availability Zone',
    'placement/region' => 'Region'
];

// Get metadata values
$metadataValues = [];
foreach ($metadataItems as $path => $label) {
    $metadataValues[$path] = fetchMetadata($ch, 'meta-data/' . $path, $headers);
}

// Close curl session
curl_close($ch);
?>

<!DOCTYPE html>
<html>
<head>
    <title>AWS Instance Metadata</title>
    <style>
        body {
            font-family: 'Amazon Ember', Arial, sans-serif;
            margin: 20px;
            background-color: #f8f8f8;
            color: #232f3e;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        h1 {
            color: #232f3e;
            padding-bottom: 10px;
            border-bottom: 1px solid #eaeded;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background-color: #232f3e;
            color: white;
            text-align: left;
            padding: 12px;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #eaeded;
            text-align: left;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .region-img {
            margin-top: 20px;
            text-align: center;
        }
        .aws-orange {
            color: #ff9900;
        }
        i {
            font-style: normal;
            color: #0073bb;
        }
    </style>
</head>
<body>
    <div class="container">
        <table>
            <tr>
                <th>Metadata</th>
                <th>Value</th>
            </tr>
            <?php foreach ($metadataItems as $path => $label): ?>
            <tr>
                <td><?= $label ?>:</td>
                <td><i><?= $metadataValues[$path] ?: 'Not available' ?></i></td>
            </tr>
            <?php endforeach; ?>
        </table>
        
        <?php if (!empty($metadataValues['placement/region'])): ?>
        <div class="region-img">
            <img src="assets/img/<?= $metadataValues['placement/region'] ?>.png" 
                 alt="<?= $metadataValues['placement/region'] ?> Region" 
                 width="250" height="125" 
                 style="border: 1px solid #ddd; border-radius: 4px;">
        </div>
        <?php endif; ?>
    </div>
</body>
</html>