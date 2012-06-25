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

		<script type="text/javascript" src="jquery-1.7.2.min.js"></script>
		<script type="text/javascript" src="js/jquery-ui-1.8.21.custom.min.js"></script>
		<script type="text/javascript" src="development-bundle/ui/minified/jquery.ui.core.min.js"></script>
		<script type="text/javascript" src="development-bundle/ui/minified/jquery.ui.slider.min.js"></script>

		<script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
		<script type="text/javascript" src="bootstrap/js/bootstrap-tab.js"></script>
		<script type="text/javascript" src="bootstrap/js/bootstrap-tooltip.js"></script>
		<script type="text/javascript" src="bootstrap/js/bootstrap-popover.js"></script>
		<script type="text/javascript" src="bootstrap/js/bootstrap-collapse.js"></script>

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
						<li class="active"><a id="firsttabclick" href="#keyword" data-toggle="tab">Keyword Paper Search</a></li>
						<li><a href="#alltopics" data-toggle="tab">Topic Explore</a></li>
						<li><a href="#viz" data-toggle="tab">Graph Explore</a></li>
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
							<h3>Do Topic Exploration <button class="btn btn-primary" id="topic-btn">refresh</button></h3>
							<div id="t_paper_pane"></div>
							<div id="t_pane">
<?php
// TODO add links to the click and go to the new page
// TODO Make a new page such that a user can go back to the original ont
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
							<div id="v_pane">
								<h5>Loading...</h5>	
							</div>
						</div>

					</div>
				</div>

				<footer><p>&copy; Christan Grant 2012</p></footer>
			</div>

		</div>

		<script type="text/javascript">
			$(document).ready(function() {
				$('#topic-btn').click(function() { doTopicChange(); });
				$('#maintab a').click(function (e) {
					e.preventDefault();
					$(this).tab('show');
				});
				$("#firsttabclick").tab('show');
				$("#firsttabclick").tab('show');
				$('#maintab a:first').tab('show');
				$('#t_paper_pane').hide();

			});
				function showError(msg) {
					// Show the error text
					$("#gresham-msg").empty();
					$("#gresham-msg").hide();
					$("#gresham-msg").append("<span class=\"label label-error\">"+msg+"</span>");
					$("#gresham-msg").slideDown('fast').delay(2000).slideUp(800);
				}
				function toggleLoading(show) { 
					if(show) { 
						$("#gresham-loading").empty();
						$("#gresham-loading").append("<img src='img/loader1.gif'></img>");
					}
					else{
						$("#gresham-loading").hide();
						$("#gresham-loading").empty();
					}
				}
				function makePaperDiv(paper) {
					var mydiv = "<div><ul>";
					mydiv += "<li>Paper      : "; mydiv += paper["papertitle"]; mydiv += "</li>";
					mydiv += "<li>Author     : "; mydiv += paper["author"]; mydiv += "</li>";
					mydiv += "<li>Conference : "; mydiv += paper["venue"]; mydiv += " Year : "; mydiv += paper["pubyear"];mydiv += "</li>";
					mydiv += "</ul></div>";

					return mydiv; 
				}
				function hidePaper() {
					$('#t_paper_pane').hide('slow');
					$('#t_pane').show('slow');
				}
				function showPaper(tid) {
					toggleLoading(true);	

					$.ajax({
						type: 'GET',
						url: 'http://neo.cise.ufl.edu/grisham/paper/web/query.php', 
						dataType: 'json', 
						data: {q: tid,
							type: 'rank',
							limit: 50,
							offset: 0},
						success: function(res) {
							$('#t_pane').hide('slow');
								
							$('#t_paper_pane').empty();
							$('#t_paper_pane').append("<i class='icon-remove' onclick='hidePaper(); event.returnValue=false;'></i>");
							$('#t_paper_pane').append("<h4>Topic " + tid + "<h4>");
							for(var i = 0; i != res["rowcount"] && i < 10; ++i) {
								//$('#t_paper_pane').append("<div>"+res[i]["papertitle"]+"</div>"); // TODO fix the show
								$('#t_paper_pane').append(makePaperDiv(res[i])); // TODO fix the show
							}
							$('#t_paper_pane').show('slow');
							toggleLoading(false);
						},
						error: function(xhr, statusText, errorThrown) {
							// TODO Add an Error message
							//$('k_msg').append('<span class=\'label label-error\'>'+statusText+'</span>');
							toggleLoading(false);
						}
					});
				}
			function kwQuery(offsetval, limitval) {
				var offsetval = offsetval || 0;
				var limitval = limitval || 50;
				
				// TODO Show the pane as loading
				$("#k_msg").empty();

				toggleLoading(true);
				$.ajax({
					type: "GET",
					url: "http://neo.cise.ufl.edu/grisham/paper/web/query.php", 
					dataType: "json",
					data: {q: escape($("#kwrd").val()),
						type: "keyword",
						limit: limitval,
						offset: offsetval},
					success: function(res) {
						
						var answertable = [];

						answertable.push("<table class=\"table table-striped\">\n");

						// Add a table header
						answertable.push("<thead><tr>");
						for(var i=0;i < 2 && res["headers"] != undefined && i < res["headers"].length; ++i) {
							answertable.push("<th><h4>"+res["headers"][i]+"</h4></th>");
						}
						answertable.push("</tr></thead>");

						answertable.push("<tbody>");
						for (var i=0; i < res["rowcount"]; ++i){
							answertable.push("<tr>");
							answertable.push("<td>" + res[i]["person"] + "</td>");
							answertable.push("<td>" + res[i]["papertitle"] + "</td>");
							answertable.push("</tr>");
						}
						answertable.push("</tbody>");
						answertable.push("</table>\n");

						// Remove loading message
						$("k_pane").empty();
						$("k_msg").empty();

						// Append to k_pane
						$("#k_pane").append(answertable.join(""));

						$("#k_msg").append("<span class=\"label label-info\">" + "Time: " + res["querytime"] + " seconds</span>");
						toggleLoading(false);
					},
					error: function(xhr, statusText, errorThrown) {
						$("k_pane").empty();
						// Add an Error message
						$("k_msg").append("<span class=\"label label-error\">"+statusText+"</span>");
						toggleLoading(false);
					}
				});
			}
			function doTopicChange() {

				// Get all the topic values in an array
				var totalval = 0.0;
				var topicSize = $("#topic-table tr").length;
				var tscores = [];
				for (var i = 0; i != topicSize; ++i) {
					var t = i+1;
					tscores.push(100 * parseInt($("#slider-topic-"+t+" div").css('width')) / parseInt($("#slider-topic-"+t+" div").parent().css('width')));
					totalval += 100 * parseInt($("#slider-topic-"+t+" div").css('width')) / parseInt($("#slider-topic-"+t+" div").parent().css('width'));
				}

				// Get the min and max values
				var max = -1; var min = 1000;
				for (var i = 0; i != topicSize; ++i) {
					if(max < tscores[i]) max = tscores[i];
					if(min >= tscores[i]) min = tscores[i];
				}
				var ratio = 255.0/(max-min);
				//var ratio = (max-min)/255.0;

				// Iterate over all the weights and change the intensities
				for (var i = 0; i != topicSize; ++i) {
					var t = i+1;
					var red = parseInt(ratio*(100-tscores[i]));
					var green = parseInt(ratio*(100-tscores[i]));
					var blue = parseInt(ratio*(100-tscores[i]));
					//var colorval = parseInt(tscores[i] /*/ totalval*/ * 255);
					$("#divrow-"+t).css('backgroundColor', "rgb(255, "+green+","+blue+")"); // Make the color
					//$("#divrow-"+t).css('backgroundColor', "rgb("+red+", 255, 255)"); // Make the color
				}

			}

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
"\t\tslide: function( event, ui ) {\n".
"\t\t\t\$( '#tval-%d' ).val( '\$' + ui.value );\n".
"\t\t}\n".
"\t});\n".
"\t\$( '#tval-%d' ).val( \$( '#slider-topic-%d' ).slider( 'value' ) );\n".
"});\n\n";


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

