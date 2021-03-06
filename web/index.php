<!DOCTYPE html>
<html>
<?php
		$dbconn = pg_connect("host=128.227.176.46 dbname=dblp user=john password=madden options='--client_encoding=UTF8'") or die('Could not connect: ' . pg_last_error());
		$topicrows = Array();
?>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title>Grisham</title>
	<head>
		<link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.css" />
		<link rel="stylesheet" type="text/css" href="css/ui-lightness/jquery-ui-1.8.21.custom.css" />
		<link rel="stylesheet" type="text/css" href="papergraph.css" />

		<script type="text/javascript" src="jquery-1.7.2.min.js"></script>
		<script type="text/javascript" src="js/jquery-ui-1.8.21.custom.min.js"></script>
		<script type="text/javascript" src="development-bundle/ui/minified/jquery.ui.core.min.js"></script>
		<script type="text/javascript" src="development-bundle/ui/minified/jquery.ui.slider.min.js"></script>

		<script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
		<script type="text/javascript" src="bootstrap/js/bootstrap-tab.js"></script>
		<script type="text/javascript" src="bootstrap/js/bootstrap-tooltip.js"></script>
		<script type="text/javascript" src="bootstrap/js/bootstrap-popover.js"></script>
		<script type="text/javascript" src="bootstrap/js/bootstrap-collapse.js"></script>

		<script type="text/javascript" src="js/Jit/jit.js"></script>
		<script type="text/javascript" src="papergraph.js"></script>
		

	</head>
	<body>
		<div class="navbar">
			<div class="navbar-inner">
				<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</a>
				<a class="brand" href="#">Grisham</a>
				<div class="nav-collapse">
					<ul class="nav">
						<li class="active"><a href="#">Home</a></li>
						<li><a href="http://www.christangrant.com">Christan Grant</a></li>
						<li><a href="http://www.cise.ufl.edu/~cgeorge/">Clint P. George</a></li>
						<li><a href="https://sites.google.com/site/virukanjilal/">Viru Kanjilal</a></li>
					</ul>
					<p class="navbar-text pull-right"><a href="#">@cegme</a></p>
				</div>
			</div>
		</div>

		<div class="container-fluid">
			<div class="row-fluid">

				<div class="span3">
					<h3>User Options</h3>
<?php
$twquery = "SELECT tid, words FROM topic_words;";

$result = pg_query($twquery) or die('Query failed: ' . pg_last_error());

while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
	$topicrows[] = $line;
}

// Allow the user to use the slider to adject properties
foreach($topicrows as $row) {
	$tid = $row["tid"];
	print "<label for='tval-$tid'>Topic: $tid</label>\n";
	// print "<input id='tval-$tid' type='text' readonly='readonly' />\n";
	print "<div id='slider-topic-$tid'></div>\n";
	print "<hr/>";
}
?>
				</div>

				<div class="span9">
					<div class="alert alert-warning">This is under construction... there may be outages and speed issues</div> 
					<div id="gresham-msg"></div>
					<div id="gresham-loading"></div>
					<ul class="nav nav-tabs" id="maintab">
						<li><a id="firsttabclick" href="#keyword" data-toggle="tab">Keyword Paper Search</a></li>
						<li class="active"><a href="#alltopics" data-toggle="tab">Topic Explore</a></li>
						<li><a href="#viz" id="ge" data-toggle="tab">Graph Explore</a></li>
					</ul>

					<div class="tab-content" id="maintabpane"> 
						<div id="keyword" class="tab-pane fade">
							<h3>Do Keyword Search</h3><div id="k_msg"></div>
							<form class="well form-search" >
								<input id="kwrd" type="text" class="input-medium search-query" placeholder="Enter Keywords"/>
								<button type="submit" class="btn" onclick="kwQuery(); event.returnValue=false;">Search</button>
							</form>
							<div id="k_pane">
							</div>
						</div>

						<div class="tab-pane fade" id="alltopics">
							<h3>Do Topic Exploration <!-- <button class="btn btn-primary" id="topic-btn">refresh</button>--></h3>
							<div id="t_paper_pane"></div>
							<div id="t_pane">
<?php
// Give the tables some style
print "<table id='topic-table'>";
foreach($topicrows as $line) { 
	$tid = $line["tid"];
	$words = explode(",", substr($line["words"], 1, strlen($line["words"])-2));
	print "<tr>";

	print "<div id='divrow-$tid' onclick=\"showPaper('$tid');event.returnValue=false;\">\n";
		//print "<button class='btn btn-inverse' data-toggle='collapse in' data-target='#topic-docs-$tid'>\n";
		//print "\t<span class='label label-info icon-plus'>$tid&nbsp;</span>&nbsp;";
		print "<span class='badge badge-inverse'>$tid</span>";
		//print "</button>";
		print "<span>";
			foreach(array_slice($words, 0, 10) as $word) { print "<span class=''>$word</span> "; }
		print "</span>";
		//print "<div id='topic-docs-$tid' class='collapse'></div>\n";
		print "<hr/>\n";
	print "</div>\n";
	print "</tr>\n";
}
print "</table>";
// Free the result set
pg_free_result($result);
?>
							</div>
						</div>

						<div class="tab-pane fade" id="viz">
							<h3>Do topic visualization</h3>
							<div id="log"></div>
							<div id="v_pane">
								<h5 id="h5graph" onclick="initializeGraphExplorer(); event.returnValue=false;">Click Here to Show the graph</h5>	

								<div id="right-container">
									<form class="well form-search">
										<label for="graphpaperid">Paper id:</label><br/>
										<input id="graphpaperid" value="24" type="text" class="input-mini search-query" placeholder="Enter Keywords"/>
										<button type="submit" class="btn" onclick="setMainPaper(); event.returnValue=false;">Show</button>
									</form>
									<div id="inner-details">
									</div>
								</div>
								<div id='color-container'><div id="center-container"><div id="infoviz"></div></div></div>
							</div>
						</div>

					</div>
				</div>

				<footer><p>&copy; Christan Grant 2012</p></footer>
			</div>

		</div>

		<script type="text/javascript">

// Code for the sliders
<?php
// This is a slider template, it takes an integer for topic id starting at 1
// Params: (1,topicid), (2,topicid), (3, topicid), (4, topicid)
$sldr = "\$(function() {\n".
"\t\$('#slider-topic-%d').slider({\n".
"\t\trange: 'min', \n".
"\t\tvalue: 30, // (1/35)*100 is uniform, \n".
"\t\tmin: 0,\n".
"\t\tmax: 100,\n".
"\t\tstop: function(event, ui) { doTopicChange(); },\n".
"\t\tslide: function( event, ui ) {\n".
"\t\t\t\$( '#tval-%d' ).val( '\$' + ui.value );\n".
"\t\t}\n".
"\t});\n".
"\t\$( '#tval-%d' ).val( \$( '#slider-topic-%d' ).slider( 'value' ) );\n".
"});\n";

foreach($topicrows as $row) {
	printf($sldr, $row["tid"], $row["tid"], $row["tid"], $row["tid"]);
}
?>

		</script>
	</body>
<?php 
// Close the connection
pg_close($dbconn);
?>
</html>

