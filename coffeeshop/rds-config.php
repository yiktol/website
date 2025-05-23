<?php

  $ch = curl_init();

  // get a valid TOKEN
  $headers = array (
        'X-aws-ec2-metadata-token-ttl-seconds: 60' );
  $url = "http://169.254.169.254/latest/api/token";

  curl_setopt( $ch, CURLOPT_URL, $url );
  curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
  curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
  curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "PUT" );
  curl_setopt( $ch, CURLOPT_URL, $url );
  $token = curl_exec( $ch );

require 'aws-autoloader.php';

use Aws\SecretsManager\SecretsManagerClient; 
use Aws\Rds\RdsClient;
use Aws\Exception\AwsException;

// Name of secret containing the database connection information
$secretName = 'RDSSecret';

  $headers = array (
        'X-aws-ec2-metadata-token: '.$token );
  $url="http://169.254.169.254/latest/dynamic/instance-identity/document";

  curl_setopt( $ch, CURLOPT_URL, $url );
  curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
  curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
  curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "GET" );
  $urlDocument = curl_exec( $ch );

// Parse instance metadata to get region
$data = json_decode($urlDocument, true);
$region = $data['region'];

// Create a Secrets Manager Client 
$client = new SecretsManagerClient([
    'version' => 'latest',
    'region' => $region,
]);

try {
    $result = $client->getSecretValue([
        'SecretId' => $secretName,
    ]);

} catch (AwsException $e) {
    $error = $e->getAwsErrorCode();
    echo "Error: ".$error."<br/>";
    die("");
}

// Check if we can retrieve the secret string
if (!isset($result['SecretString'])) {
    echo "Error: Unable to retrieve secret";
    die("");
}

$secret = json_decode($result['SecretString'], true);

// Provide values for DB connection
$DB_SERVER = $secret['host'];
$DB_DATABASE = $secret['dbname'];
$DB_USERNAME = $secret['username'];
$DB_PASSWORD = $secret['password'];

// Create RDS client to get reader endpoint for Aurora cluster
$rdsClient = new RdsClient([
    'version' => 'latest',
    'region' => $region,
]);

// For Aurora clusters, the host is the cluster endpoint
// Extract the cluster identifier from the endpoint
// Format: cluster-name.cluster-xxxxxxxxx.region.rds.amazonaws.com
$hostParts = explode('.', $DB_SERVER);
$clusterIdentifier = $hostParts[0];

// Get the reader endpoint from Aurora cluster
try {
    $result = $rdsClient->describeDBClusters([
        'DBClusterIdentifier' => $clusterIdentifier
    ]);
    
    // Aurora clusters have a specific reader endpoint
    if (isset($result['DBClusters'][0]['ReaderEndpoint'])) {
        $DB_SERVER_RO = $result['DBClusters'][0]['ReaderEndpoint'];
    } else {
        // Fallback to writer endpoint if reader endpoint isn't available
        $DB_SERVER_RO = $DB_SERVER;
    }
} catch (AwsException $e) {
    // If the cluster isn't found or any other error, fall back to writer endpoint
    $DB_SERVER_RO = $DB_SERVER;
    
    // Optionally log the error
    // error_log("Error retrieving Aurora cluster reader endpoint: " . $e->getMessage());
}

return array(
    'DB_SERVER' => $DB_SERVER,
    'DB_USERNAME' => $DB_USERNAME,
    'DB_PASSWORD'=> $DB_PASSWORD,
    'DB_DATABASE'=> $DB_DATABASE,
    'DB_SERVER_RO' => $DB_SERVER_RO,
); 

?>