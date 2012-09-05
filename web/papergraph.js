var labelType, useGradients, nativeTextSupport, animate;

(function() {
  var ua = navigator.userAgent,
      iStuff = ua.match(/iPhone/i) || ua.match(/iPad/i),
      typeOfCanvas = typeof HTMLCanvasElement,
      nativeCanvasSupport = (typeOfCanvas == 'object' || typeOfCanvas == 'function'),
      textSupport = nativeCanvasSupport 
        && (typeof document.createElement('canvas').getContext('2d').fillText == 'function');
  //I'm setting this based on the fact that ExCanvas provides text support for IE
  //and that as of today iPhone/iPad current text support is lame
  labelType = (!nativeCanvasSupport || (textSupport && !iStuff))? 'Native' : 'HTML';
  nativeTextSupport = labelType == 'Native';
  useGradients = nativeCanvasSupport;
  animate = !(iStuff || !nativeCanvasSupport);
})();

var Log = {
  elem: false,
  write: function(text){
    if (!this.elem) 
      this.elem = document.getElementById('log');
    this.elem.innerHTML = text;
    this.elem.style.left = (500 - this.elem.offsetWidth / 2) + 'px';
  }
};

// These are the graph object we will manipulate
var myfd = null;
var myjson = null;


function initializeGraphExplorer() {
	$("#infoviz").css('min-height','600px');
	$("#infoviz").css('min-width','600px');
	$("#infoviz").css('height','600px');
	$("#infoviz").css('width','600px');
  // init data
  var json = [
      
  ];
  // end
  // init ForceDirected
  var fd = new $jit.ForceDirected({
		height: 600,
		width: 600, 
    //id of the visualization container
    injectInto: 'infovis',
    //Enable zooming and panning
    //by scrolling and DnD
    Navigation: {
      enable: true,
      //Enable panning events only if we're dragging the empty
      //canvas (and not a node).
      panning: 'avoid nodes',
      zooming: 10 //zoom speed. higher is more sensible
    },
    // Change node and edge styles such as
    // color and width.
    // These properties are also set per node
    // with dollar prefixed data-properties in the
    // JSON structure.
    Node: {
      overridable: true
    },
    Edge: {
      overridable: true,
      color: '#23A4FF',
      lineWidth: 0.4
    },
    //Native canvas text styling
    Label: {
      type: labelType, //Native or HTML
      size: 10,
      style: 'bold'
    },
    //Add Tips
    Tips: {
      enable: true,
      onShow: function(tip, node) {
        //count connections
        var count = 0;
        node.eachAdjacency(function() { count++; });
        //display node info in tooltip
        tip.innerHTML = "<div class=\"tip-title\">" + node.name + "</div>"
          + "<div class=\"tip-text\"><b>pid:</b> " + node.id + "</div>"
          + "<div class=\"tip-text\"><b>connections:</b> " + count + "</div>";
      }
    },
    // Add node events
    Events: {
      enable: true,
      type: 'Native',
      //Change cursor style when hovering a node
      onMouseEnter: function() {
        fd.canvas.getElement().style.cursor = 'move';
      },
      onMouseLeave: function() {
        fd.canvas.getElement().style.cursor = '';
      },
      //Update node positions when dragged
      onDragMove: function(node, eventInfo, e) {
          var pos = eventInfo.getPos();
          node.pos.setc(pos.x, pos.y);
          fd.plot();
      },
      //Implement the same handler for touchscreens
      onTouchMove: function(node, eventInfo, e) {
        $jit.util.event.stop(e); //stop default touchmove event
        this.onDragMove(node, eventInfo, e);
      },
      //Add also a click handler to nodes
      onClick: function(node) {
        if(!node) return;
        // Build the right column relations list.
        // This is done by traversing the clicked node connections.
        var html = "<h4>" + node.name + "</h4><b> connections:</b><ul><li>",
            list = [];
        node.eachAdjacency(function(adj){
          list.push(adj.nodeTo.name);
        });
        //append connections information
        $jit.id('inner-details').innerHTML = html + list.join("</li><li>") + "</li></ul>";
      }
    },
    //Number of iterations for the FD algorithm
    iterations: 200,
    //Edge length
    levelDistance: 130,
    // Add text to the labels. This method is only triggered
    // on label creation and only for DOM labels (not native canvas ones).
    onCreateLabel: function(domElement, node){
      domElement.innerHTML = node.name; 
      var style = domElement.style;
      style.fontSize = "0.8em";
      style.color = "#ddd";
    },
    // Change node styles when DOM labels are placed
    // or moved.
    onPlaceLabel: function(domElement, node){
      var style = domElement.style;
      var left = parseInt(style.left);
      var top = parseInt(style.top);
      var w = domElement.offsetWidth;
      style.left = (left - w / 2) + 'px';
      style.top = (top + 10) + 'px';
      style.display = '';
    }
  });
	myjson = json;
  // load JSON data.
  //fd.loadJSON(myjson);
  //fd.loadJSON(json);

  // compute positions incrementally and animate.
  /*fd.computeIncremental({
    iter: 40,
    property: 'end',
    onStep: function(perc){
      Log.write(perc + '% loaded...');
    },
    onComplete: function(){
      Log.write('done');
      fd.animate({
        modes: ['linear'],
        transition: $jit.Trans.Elastic.easeOut,
        duration: 2500
      });
    }
  });
	*/
  // end
	myfd = fd; // Make it global so we can use it again.
}
// TODO Use Ajax/events to change. See: http://thejit.org/static/v20/Jit/Examples/Treemap/example2.code.html


function updatePaperGraph(paperid) {

	// TODO ajax call to update the proper paper citations and add the to the graph

	toggleLoading(true);
	$.ajax({
		type: 'GET',
		url: '/grisham/web/query.php',
		//url: 'http://neo.cise.ufl.edu/grisham/paper/web/query.php',
		dataType: 'json',
		data: {q: paperid,
			type: 'citations',
			limit: 20,
			offset: 0},
		success: function(res) {

			var testobj = [
				{ "adjacencies" : [
						{ 
							"nodeTo": "citation1",
							"nodeFrom": "papertitle1",
							"data" : {
									"$color" : "#557EAA",
									"$lineWidth": 0.8
							}
						},
						{
							"nodeTo": "citation2",
							"nodeFrom": "papertitle1",
							"data" : {
									"$color" : "#007EAA",
									"$lineWidth": 1.8
							}
						}
					],
					"data": {
						"$color": "#83548B",
						"$type": "circle",
						"$dim": 10
					},
					"id":"pid",
					"name": "the paper title",
					"venue": "The venue",
					"year": 1993,
					"abstract": "The abstract of the paper"//,
					//"topic": JSON.parse("{0.0100963818836,0.0165756126332,0.103387881871,0.0109071161021,0.325175976789,0.013806549356}0.0100963818836,0.0165756126332,0.103387881871,0.0109071161021,0.325175976789,0.013806549356}".replace("{","[").replace("}","]") )
				}
			];

			myfd.graph.addNode({id: paperid} );
			var citations = [];
			for (var i = 0; i != res["rowcount"]; ++i) {
				var ref = res[i];
				ref['topic'] = JSON.parse(res[i]['topic'].replace("{","[").replace("}","]"));
				ref['name'] = res[i]['title'];
				ref['id'] = res[i]['pid'];
				ref['adjacencies'] = [{'nodeTo': ''+paperid, "data": {'$color': '#ff00ff', '$lineWidth': 1.0 }  }];
				ref['data'] = {'$color': '#ff00ff', '$type': 'circle', '$dim': 8 };
				
				citations.push(ref);
				myfd.graph.addNode(ref);
				myfd.graph.addAdjacence({id:paperid},ref,ref['data']);
			}
			if(res["rowcount"] == 0) {
				showError("Paper " + paperid + " has no citations.");
			}
			else {
				showInfo("Paper " + paperid + " has " + res["rowcount"] + "citations.");
			}

			myjson = $jit.util.extend(myjson, citations);
			//myjson = $jit.util.extend(myjson, testobj);

			myfd.loadJSON(myjson);
			//myfd.refresh();

			myfd.computeIncremental({
				iter: 40,
				property: 'end',
				onStep: function(perc){
					Log.write(perc + '% loaded...');
				},
				onComplete: function(){
					Log.write('done');
					myfd.animate({
						modes: ['linear'],
						transition: $jit.Trans.Elastic.easeOut,
						duration: 2500
					});
				}
			});

			toggleLoading(false);
		},
		error: function(xhr, statusText, errorThrown) {
			toggleLoading(false);

		}
	});
	return false;
}
			$(document).ready(function() {
				//$('#topic-btn').click(function() { doTopicChange(); });
				$('#maintab a').click(function (e) {
					e.preventDefault();
					$(this).tab('show');
				});
				//$("#firsttabclick").tab('show');
				$('#maintab a:last').tab('show');
				$('#t_paper_pane').hide();
				//$('a[data-toggle="tab"]').on('shown', function (e) {
				//	initializeGraphExplorer();
				//});
			});
				function showError(msg) {
					// Show the error text
					$("#gresham-msg").empty();
					//$("#gresham-msg").hide();
					$("#gresham-msg").append("<span class=\"label label-error\">"+msg+"</span>");
					$("#gresham-msg").slideDown('fast').delay(2000).slideUp(800);
				}
				function showInfo(msg) {
					// Show the info text
					$("#gresham-msg").empty();
					$("#gresham-msg").append("<span class=\"label label-info\">"+msg+"</span>");
					$("#gresham-msg").slideDown('fast').delay(2000).slideUp(800);
				}
				function toggleLoading(show) { 
					if(show) { 
						$("#gresham-loading").append("<img src='img/loader1.gif'></img>");
						$("#gresham-loading").show();
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
						url: '/grisham/web/query.php', 
						//url: 'http://neo.cise.ufl.edu/grisham/paper/web/query.php', 
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
					url: '/grisham/web/query.php', 
					//url: 'http://neo.cise.ufl.edu/grisham/paper/web/query.php', 
					dataType: "json",
					data: {q: escape($("#kwrd").val()),
						type: "keyword_realtime",
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
						showError(statusText);
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
			function setMainPaper() {
				var paperid = $("#graphpaperid").val();

				// Defined in papergraph.js
				updatePaperGraph(paperid);
				return false;
			}
