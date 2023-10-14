<?php
/**
 * Copyright 2010-2022 Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *
 * This file is licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License. A copy of
 * the License is located at
 *
 * http://aws.amazon.com/apache2.0/
 *
 * This file is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR
 * CONDITIONS OF ANY KIND, either express or implied. See the License for the
 * specific language governing permissions and limitations under the License.
 *
 * If you need more information about configurations or implementing the sample code, visit the AWS docs:
 * https://aws.amazon.com/developers/getting-started/php/
 *
 */
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

use Aws\Ssm\SsmClient; 
use Aws\Exception\AwsException;

// Name of secret containing the database connection information
$parameterName = '/cpe/dashboard';

  $headers = array (
        'X-aws-ec2-metadata-token: '.$token );
  $url="http://169.254.169.254/latest/dynamic/instance-identity/document";

  curl_setopt( $ch, CURLOPT_URL, $url );
  curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
  curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
  curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "GET" );
  $urlDocument = curl_exec( $ch );

// Link local data provides information bout this instance  
// $urlDocument = "http://169.254.169.254/latest/dynamic/instance-identity/document";
// $document = file_get_contents($urlDocument);
$data = json_decode($urlDocument, true);
$region = $data['region'];

// Create a Secrets Manager Client 
$client = new SsmClient([
    'region' => $region,
]);


try {
    $result = $client->getParameter([
        'Name' => "$parameterName",
        'WithDecryption' => false,
    ]);

} catch (AwsException $e) {
    $error = $e->getAwsErrorCode();
    echo "Error: ".$error."<br/>";
    die("");
}
//
// Decrypts secret using the associated KMS CMK.
// Depending on whether the secret is a string or binary, one of these fields will be populated.
if (!isset($result["Parameter"])) {
    echo "Error: Unable to retrieve parameter";
    die("");
}

#$parameter= json_decode($result['Parameters'], true);
#echo $result["Parameter"];
$dashboard_URL = $result["Parameter"]['Value'];
#echo $result['Parameters'][0]->get( "Value");

// Provide values for DB connection
print($dashboard_URL);

header("Location: $dashboard_URL", true, 301);

exit();

?>