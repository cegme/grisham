<?php

	// Query processor

	header('Content-type: application/json');

	// Process the keyword query
	if(isset($_POST['q']) && isset($_POST['keyword'])) {

		$dbconn = pg_connect("host=128.227.176.46 dbname=dblp user=john password=madden options='--client_encoding=UTF8'") or die('Could not connect: ' . pg_last_error());

		// Decode query
		$keyword = rawurldecode($_POST['q']); // Get the keyword

		$query = "SELECT person, papertitle, pubyear, venue, abstract, ".
						 "CASE WHEN (person ILIKE '%$keyword%') THEN 'author' ".
	     			 "		 WHEN (papertitle ILIKE '%$keyword%' OR abstract ILIKE '%$keyword%') THEN 'paper' ".
	     			 "ELSE 'none' END as type ".
						 "FROM paper left join author ON id=pid ".
						 "WHERE person ILIKE '%$keyword%' OR ".
						 "papertitle ILIKE '%$keyword%' OR abstract ILIKE '%$keyword%' ".
						 "ORDER BY type, pubyear DESC;";

		// Make a query to the DB
		list($tic_usec, $tic_sec) = explode(" ", microtime());
		$result = pg_query($query) or die('Query failed: ' . pg_last_error());
		list($toc_usec, $toc_sec) = explode(" ", microtime());

		$querytime = $toc_sec + $toc_usec - ($tic_sec + $tic_usec); // Query time

		// Iterate over results
		$rows = array();
		while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
			$rows[] = $line;
		}
		
		$rows["q"] = urldecode($query);
		$rows["querytime"] = $querytime;
		$rows["rowcount"] = pg_num_rows($result);

		if ($rows["rowcount"] > 0) {
			$rows["headers"] = array_keys($rows[0]);
		}
		else {
			// Some default header for no result
			$rows["headers"] = array(0 => 100, "color" => "red"); 
		}

		// Show the json result
		print json_encode($rows); 

		// Free the result set
		pg_free_result($result);
		
		// Close the connection
		pg_close($dbconn);
	}
?>