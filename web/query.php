<?php

	// Query processor


	// Process the keyword query
	if(isset($_GET['q']) && isset($_GET['type']) && $_GET['type'] == "keyword_realtime") {
		header('Content-type: application/json');

		$dbconn = pg_connect("host=128.227.176.46 dbname=dblp user=john password=madden options='--client_encoding=UTF8'") or die('Could not connect: ' . pg_last_error());

		// Decode query
		$keyword = rawurldecode($_GET['q']); // Get the keyword
		

		$query = "SELECT person, papertitle, pubyear, venue, abstract, ".
						 "CASE WHEN (person ILIKE '%$keyword%') THEN 'author' ".
	     			 "		 WHEN (papertitle ILIKE '%$keyword%' OR abstract ILIKE '%$keyword%') THEN 'paper' ".
	     			 "ELSE 'none' END as type ".
						 "FROM paper left join author ON id=pid ".
						 "WHERE person ILIKE '%$keyword%' OR ".
						 "papertitle ILIKE '%$keyword%' OR abstract ILIKE '%$keyword%' ".
						 "ORDER BY type, pubyear DESC ";

		// Add LIMIT and OFFSET to the query if present
		if(isset($_GET['limit']))
			$thelimit = rawurldecode($_GET['limit']); 
		else
			$thelimit = 50;
		
		$query = $query . " LIMIT $thelimit ";

		if(isset($_GET['offset']))
			$theoffset = rawurldecode($_GET['offset']);
		else
			$theoffset = 0;
		
		$query = $query . " OFFSET $theoffset";
		
		
		// END THE QUERY
		$query = $query . ";";

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
	else if(isset($_GET['q']) && isset($_GET['type']) && $_GET['type'] == "keyword") {
		header('Content-type: application/json');

		$dbconn = pg_connect("host=128.227.176.46 dbname=dblp user=john password=madden options='--client_encoding=UTF8'") or die('Could not connect: ' . pg_last_error());

		// Decode query
		$keyword = rawurldecode($_GET['q']); // Get the keyword
		

		$splitwords = explode(" ", $keyword);
		$size = count($splitwords);

		$query = "SELECT person, papertitle, pubyear, venue, abstract, ".
				" CASE WHEN (person ILIKE '%$keyword%') THEN 'author' ".
		 		" WHEN (papertitle ILIKE '%$keyword%' OR abstract ILIKE '%$keyword%') THEN 'paper' ".
		 		" ELSE 'none' END as type ".
		 "from paper, paperindex
		 where paper.id = paperindex.pid ";

		for($i = 0; $i<$size; $i++)
		{
			$wordsi = $splitwords[$i];
			$query = $query . " OR paperindex.word iLIKE '%$splitwordsi%' ";
		}
		
		$query = $query . " ORDER BY type, pubyear DESC ";

		// Add LIMIT and OFFSET to the query if present
		if(isset($_GET['limit'])) {
			$thelimit = rawurldecode($_GET['limit']); 
		}
		else {
			$thelimit = 50;
		}
		
		$query = $query . " LIMIT $thelimit ";

		if(isset($_GET['offset']))
			$theoffset = rawurldecode($_GET['offset']);
		else
			$theoffset = 0;
		
		$query = $query . " OFFSET $theoffset";
		
		
		// END THE QUERY
		$query = $query . ";";

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
///////////////////////////
//RANKING FUNCTION STARTS//
///////////////////////////
	else if(isset($_GET['q']) && isset($_GET['type']) && $_GET['type'] == "rank_realtime") {
		header('Content-type: application/json');

		$dbconn = pg_connect("host=128.227.176.46 dbname=dblp user=john password=madden options='--client_encoding=UTF8'") or die('Could not connect: ' . pg_last_error());

		// Decode query
		$id = rawurldecode($_GET['q']); // Get the keyword
		$smallestDouble = "0.000000000001";
		


		$query = "select comparetable.weight, comparetable.pid".
			  " from (select sum(ln(1 - value.t-$smallestDouble)) + ln(value.pi+$smallestDouble) - ln(1-value.pi-$smallestDouble) as weight, value.pid as pid ".
       				" from (select unnest(tab.topic_distribution) as t, tab.pid as pid, tab.topic_distribution[$id] as Pi".
					" from  (select pid, topic_distribution from theta) as tab)".
			"as value GROUP BY value.pid, value.pi) as comparetable ORDER BY comparetable.weight DESC";

		// Add LIMIT and OFFSET to the query if present
		if(isset($_GET['limit']))
			$thelimit = rawurldecode($_GET['limit']); 
		else
			$thelimit = 50;
		
		$query = $query . " LIMIT $thelimit ";

		if(isset($_GET['offset']))
			$theoffset = rawurldecode($_GET['offset']);
		else
			$theoffset = 0;
		
		$query = $query . " OFFSET $theoffset";
		
		// END THE QUERY
		$query = $query . ";";
		
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
////////////////// RANK precomputed ///////////////////
	else if(isset($_GET['q']) && isset($_GET['type']) && $_GET['type'] == "rank") {
		header('Content-type: application/json');

		$dbconn = pg_connect("host=128.227.176.46 dbname=dblp user=john password=madden options='--client_encoding=UTF8'") or die('Could not connect: ' . pg_last_error());

		// Decode query
		$id = rawurldecode($_GET['q']); // Get the keyword
			


		$query = "select pid, papertitle, pubyear, venue, abstract from precomputed_rank, paper where topic_id=$id and id=pid ";
		

		// Add LIMIT and OFFSET to the query if present
		if(isset($_GET['limit']))
			$thelimit = rawurldecode($_GET['limit']); 
		else
			$thelimit = 50;
		if(isset($_GET['offset']))
			$theoffset = rawurldecode($_GET['offset']);
		else
			$theoffset = 0;
		
		$query = $query . " OFFSET $theoffset";
		
		// END THE QUERY
		$query = $query . ";";	

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
///////////////////////////
//RANKING FUNCTION ENDS ///
///////////////////////////
	else {
		//header('Content-type: text/plain');
		//header('Content-type: text/html');
		header('Content-type: application/json');

		$rows["q"] = "null";
		$rows["querytime"] = -1;
		$rows["rowcount"] = 0;
		$rows["GET"] = $_GET;

		print json_encode($rows);
	}
	// phpinfo();
?>
