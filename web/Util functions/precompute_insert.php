<?php


$dbconn = pg_connect("host=128.227.176.46 dbname=dblp user=virup password=kanjilal options='--client_encoding=UTF8'") or die('Could not connect: ' . pg_last_error());

// Decode query

$smallestDouble = "0.0000000000000001";
		

for($i=1;$i<=35;$i++){
$id = $i; //for now
$query = "INSERT INTO precomputed_rank 
	select $id,  comparetable.pid, comparetable.weight 
	from (select sum(ln(1 - value.t - $smallestDouble)) + ln(value.pi+$smallestDouble) - ln(1-value.pi-$smallestDouble) as 		weight, value.pid as pid 
       from (select unnest(tab.topic_distribution) as t, tab.pid as pid, tab.topic_distribution[$id] as Pi       
	from  (select pid, topic_distribution from theta) as tab)
as value GROUP BY value.pid, value.pi) as comparetable ORDER BY comparetable.weight DESC LIMIT 2000;";
echo $query."\n";
	$result = pg_query($query) or die('Query failed: ' . pg_last_error());
		// Free the result set
		pg_free_result($result);
		

}

		// Close the connection
		pg_close($dbconn);
?>
