<!DOCTYPE html>
<html>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title>Grisham</title>
	<head>
		<link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.css" />

		<script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
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
				</div>

				<div class="span9">
					<ul class="nav nav-tabs" id="maintab">
						<li class="active"><a href="#keyword" data-toggle="tab">Keyword Paper Search</a></li>
						<li class=""><a href="#alltopics" data-toggle="tab">Topic Explore</a></li>
						<li class=""><a href="#viz" data-toggle="tab">Graph Explore</a></li>
					</ul>
					<div id="maintabpane" class="tab-content">
						<div class="tab-pane fade" id="keyword">
							<h3>Do Keyword Search</h3>
						</div>

						<div class="tab-pane fade" id="alltopics">
							<h3>Do Topic exploration</h3>
						</div>

						<div class="tab-pane fade" id="viz">
							<h3>Do topic visualization</h3>
						</div>

					</div>
				</div>

				<footer><p>&copy; Christan Grant 2012</p></footer>
			</div>

		</div>

	<script>
		$(function () {
			$('#maintab a:first').tab('show');
		})
	</script>
	</body>
</html>
