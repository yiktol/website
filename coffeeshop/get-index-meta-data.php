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

  echo "<table>";
  echo "<tr><th>MetaData</th><th>Value</th></tr>";

  #The URL root is the AWS meta data service URL where metadata
  # requests regarding the running instance can be made
  # $urlRoot="http://169.254.169.254/latest/meta-data/";

  # Get the instance ID from meta-data and print to the screen
  $headers = array (
        'X-aws-ec2-metadata-token: '.$token );
  $url="http://169.254.169.254/latest/meta-data/";

  curl_setopt( $ch, CURLOPT_URL, $url . 'instance-id' );
  curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
  curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
  curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "GET" );
  $result = curl_exec( $ch );

  echo "<tr><td>InstanceId:</td><td><i>" . $result . "</i></td><tr>";

  # Instant Type
  curl_setopt( $ch, CURLOPT_URL, $url . 'instance-type' );
  $type = curl_exec( $ch );

  echo "<tr><td>Instance Type:</td><td><i>" . $type . "</i></td><tr>";

  # Hostname
  curl_setopt( $ch, CURLOPT_URL, $url . 'hostname' );
  $hostname = curl_exec( $ch );

  echo "<tr><td>Hostname:</td><td><i>" . $hostname . "</i></td><tr>";

  # Private IPv4
  curl_setopt( $ch, CURLOPT_URL, $url . 'local-ipv4' );
  $localipv4 = curl_exec( $ch );

  echo "<tr><td>Private IP:</td><td><i>" . $localipv4 . "</i></td><tr>";

  # IPv6
   curl_setopt( $ch, CURLOPT_URL, $url . 'ipv6' );
   $ipv6 = curl_exec( $ch );

   echo "<tr><td>IPv6 Address:</td><td><i>" . $ipv6 . "</i></td><tr>";

  # Availability Zone
  curl_setopt( $ch, CURLOPT_URL, $url . 'placement/availability-zone' );
  $az = curl_exec( $ch );

  echo "<tr><td>Availability Zone:</td><td><i>" . $az . "</i></td><tr>";

  # Region
  curl_setopt( $ch, CURLOPT_URL, $url . 'placement/region' );
  $region = curl_exec( $ch );

  if ($region == 'ap-southeast-1'){
      echo "<tr><td>Region(Singapore):</td><td><i>" . $region . "</i></td><tr>";
      echo "<tr><td><img src='assets/img/singapore.svg.png' width='320' height='213' border='1px solid #55' /></td><tr>";
   }
  if ($region == 'ap-southeast-2'){
      echo "<tr><td>Region(Australia):</td><td><i>" . $region . "</i></td><tr>";
      echo "<tr><td><img src='assets/img/australia.svg.png' width='320' height='213' border='1px solid #55' /></td><tr>";
         }
  echo "</table>";
?>
