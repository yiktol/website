<?php
  # This code performs a simple vmstat and grabs the current CPU idle time
  $idleCpu = exec('vmstat 1 2 | awk \'{ for (i=1; i<=NF; i++) if ($i=="id") { getline; getline; print $i }}\'');

  # Print out the idle time, subtracted from 100 to get the current CPU utilization
  $load = 100-$idleCpu;
  echo "<p>";
  echo "<table>";
  echo "<tr><td>Current CPU Load: $load %</td></tr>";
  echo "</table>";
  echo "</p>";
  
?>
