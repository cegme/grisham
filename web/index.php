<!DOCTYPE html>
<html>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title>Grisham</title>
	<head>
		<link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.css" />

		<script type="text/javascript" src="jquery-1.7.2.min.js"></script>
		<script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
		<script type="text/javascript" src="bootstrap/js/bootstrap-tab.js"></script>
		<script type="text/javascript" src="bootstrap/js/bootstrap-tooltip.js"></script>
		<script type="text/javascript" src="bootstrap/js/bootstrap-popover.js"></script>
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
					<h3>User Options...</h3>
					<?php
						// Load some user options/trigger from the db so the "user" can 
						// configure them.
					?>
				</div>

				<div class="span9">
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
							<h3>Do Topic exploration</h3>
							<div id="t_pane">
								<h5>Loading...</h5>	
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
				$('#maintab a').click(function (e) {
					e.preventDefault();
					$(this).tab('show');
				});
				$("#firsttabclick").tab('show');
				$(function () {
					$("#firsttabclick").tab('show');
					$('#maintab a:first').tab('show');
				});
			});
			function kwQuery(offset, limit) {
				var offset = offset || 0;
				var limit = limit || 50;
				
				// TODO Show the pane as loading
				$("#k_msg").empty();

				$.ajax({
					type: "GET",
					url: "http://neo.cise.ufl.edu/grisham/paper/web/query.php", 
					dataType: "json",
					data: {q: escape($("#kwrd").val()),
						type: "keyword",
						limit: limit,
						offset: offset},
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

						// Append to k_pane
						$("#k_pane").append(answertable.join(""));

						$("#k_msg").append("<span class=\"label label-info\">" + "Time: " + res["querytime"] + "</span>");
					},
					error: function(xhr, statusText, errorThrown) {
						$("k_pane").empty();
						// Add an Error message
						$("k_msg").append("<div class=\"alert alert-error\">"+statusText+"</div>");
					}
				});
			}
		</script>
	</body>
</html>
