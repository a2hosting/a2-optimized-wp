<div class="wrap">
<script> 
	let data = <?php echo $data['data_json'] ?>;

	// scripts that should be shared in a header or footer
	function toggleInfoDiv(parentDivId, elem){
		let that = jQuery(elem);
		let parentDiv = jQuery('#' + parentDivId);
		let data_div = parentDiv.find('.graph_data');
		let explanation_div = parentDiv.find('.graph_info');

		if (data_div.is(":visible")){
			data_div.hide();
			explanation_div.show();
			that.text('X');
		}
		else{
			explanation_div.hide();
			data_div.show();
			that.text('❔');
		}
	}

	function generateCircle(id, radius, width, graph_data){
		let baseColor = palette[graph_data.color_class];

		var circle_graph = Circles.create({
			id:                  id,
			radius:              radius,
			value:               graph_data.score,
			maxValue:            graph_data.max,
			width:               width,
			text:                graph_data.text,
			colors:              [baseColor + '25', baseColor + '55'],
			duration:            400,
			wrpClass:            'circles-wrp',
			textClass:           'circles-text',
			valueStrokeClass:    'circles-valueStroke',
			maxValueStrokeClass: 'circles-maxValueStroke',
			styleWrapper:        true,
			styleText:           true
		});

		return circle_graph;
	}

	function createLineGraph(elemId, graphData, circleId){
		let lineDiv = document.getElementById(elemId);
		let bodyRect = document.body.getBoundingClientRect();
		let rect = lineDiv.getBoundingClientRect();
		var svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
		svg.setAttribute("width", rect.width);
		svg.setAttribute("height", rect.height);

		var max_length = rect.width - 10;
		var progress_percent = graphData.score / graphData.max;
		if (progress_percent > 1) {
		progress_percent = 1;
		}
		var progress_length = max_length * progress_percent;

		var baseColor = palette[graphData.color_class];
		svg.appendChild(getLinePath(5,5,max_length, baseColor + "25"));
		svg.appendChild(getLinePath(5,5,progress_length, baseColor));

		lineDiv.appendChild(svg);


		if (circleId){
			let circleDiv = document.getElementById(circleId);
			let centerPos = rect.width / 2;
			let maxOffset = centerPos - 35;
			let offset = progress_length - centerPos;
			if (offset < -1 * maxOffset){
				offset = -1 * maxOffset;
			}
			else if (offset > maxOffset){
				offset = maxOffset;
			}
			let graphDiv = circleDiv.querySelector('.circles-wrp');
			graphDiv.style.left = offset + "px";
		}
	}

	function getLinePath(x,y, length, color){
		var path = document.createElementNS("http://www.w3.org/2000/svg", "path");
		path.setAttribute("fill", "transparent");
		path.setAttribute("stroke", color);
		path.setAttribute("stroke-width", "10");
		path.setAttribute("stroke-linecap", "round");
		path.setAttribute("stroke-linejoin", "round");
		path.setAttribute("class", "line-graph-style");
		path.setAttribute("d", "m " + x + "," + y +" h " + length + " Z");

		return path;

	}

	// thanks stackoverflow! https://stackoverflow.com/questions/1740700/how-to-get-hex-color-value-rather-than-rgb-value
	const rgb2hex = (rgb) => `#${rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/).slice(1).map(n => parseInt(n, 10).toString(16).padStart(2, '0')).join('')}`

	let palette = [];
	jQuery( document ).ready( function( $ ) {
		jQuery("#color-palette span").each(function (index, elem){
			var that = jQuery(elem);
			var color = rgb2hex(that.css('color'));
			var colorClass = that.attr('class');
			palette[colorClass] = color;
		});
	});
</script>
	<div class="container-fluid" id="a2-optimized-wrapper">
		<!--
		<div class="row" id="a2-optimized-header">
			<div class="col-sm-6 title">
				<h2>Optimization <span class='normal'>Dashboard</span></h2>
			</div>
			<div class="col-sm-4 search">
				<input type="text" />
				<p class='small'>Data relates to your homepage</p>
			</div>
			<div class="col-sm-2 text-right utility">
				<p><a href="#"><span class="glyphicon glyphicon-bell" aria-hidden="true"></span></a>
				<a href="#"><span class="glyphicon glyphicon-option-vertical" aria-hidden="true"></span></a></p>
			</div>
			<div id="color-palette" style="display:none;">
				<span class="success"></span>
				<span class="warn"></span>
				<span class="danger"></span>
			</div>
		</div>
		<div class="row" id="a2-optimized-nav">
			<div class="col-sm-10 col-sm-offset-1">
				<div class="row" id="a2-optimized-navigation">
					<div class="col-sm-4 text-center">
						<p><a href="options-general.php?page=a2-optimized&a2_page=page_speed_score" class="<?php echo $data['nav']['pls_class'] ?>">Page Load Speed Score</a></p>
					</div>
					<div class="col-sm-4 text-center">
						<p><a href="options-general.php?page=a2-optimized&a2_page=server_performance" class="<?php echo $data['nav']['wsp_class'] ?>">Website &amp; Server Performance</a></p>
					</div>
					<div class="col-sm-4 text-center">
						<p><a href="#" class="<?php echo $data['nav']['opt_class'] ?>">Optimization</a></p>
					</div>
				</div>
			</div>
		</div>
		-->
		<div class="row">
			<div class="col-sm-10 col-sm-offset-1">
				<div class="row">
					<div class="col-sm-6">
						<div id="graph-pagespeed" class="box-element success">
							<info-button metric='pagespeed'></info-button>
							<div class="graph_data">
								<div class="row header">
									<div class="col-sm-8">
										<h3>Page Load Speed</h3>
									</div>
									<div class="col-sm-4 text-right">
										<p><a class="btn cta-btn-green">Run check</a><br>
										<span>Last Check: Now</span></p>
									</div>
								</div>
								<div class="row">
									<div class="col-sm-11 col-sm-offset-1">
										<div class="col-sm-5 box-element" :class="graphs.pagespeed_mobile.status">
											<p class='box-title'>Mobile</p>
											<div class="circle" id="circles-pls-mobile"></div>
											<span v-html="graphs.pagespeed_mobile.change"></span>
										</div>
										<div class="col-sm-5 col-sm-offset-1 box-element" :class="graphs.pagespeed_desktop.status">
											<p class='box-title'>Desktop</p>
											<div class="circle" id="circles-pls-desktop"></div>
											<span v-html="graphs.pagespeed_desktop.change"></span>
										</div>
									</div>
								</div>
							</div>
							<div class="graph_info" style="display:none;">
								<div class="row">
									<div class="col-sm-10">
										<p>{{ explanations.pagespeed}}</p>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-7">
								<p>Data pulled from Google PageSpeed Insights</p>
							</div>
							<div class="col-sm-5 text-right">
								<p>Compare with <a href="https://gtmetrix.com/" target="_blank">GTMetrix</a></p>
							</div>
						</div>
					</div>
					<div class="col-sm-6" id="a2-optimized-optstatus">
						<div id="graph-opt" class="box-element success">
							<info-button metric='opt'></info-button>
							<div class="graph_data">
								<div class="row header">
									<div class="col-sm-12">
										<h3>Optimization Status</h3>
									</div>
								</div>
								<div class="row">
									<div class="col-sm-11 col-sm-offset-1">
										<div class="col-sm-11 box-element normal">
											<div class="row">
												<div class="col-sm-4 text-center">
													<div class="circle" id="circles-opt-perf"></div>
													Performance
												</div>
												<div class="col-sm-4 text-center">
													<div class="circle" id="circles-opt-sec"></div>
													Security
												</div>
												<div class="col-sm-4 text-center">
													<div class="circle" id="circles-opt-bp"></div>
													Best Practices
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="graph_info" style="display:none;">
								<div class="row">
									<div class="col-sm-10">
										<p>{{ explanations.opt}}</p>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<script>

	jQuery( document ).ready( function( $ ) {
		var plsMobile = generateCircle('circles-pls-mobile', 40, 10,  data.graphs.pagespeed_mobile);
		var plsDesktop = generateCircle('circles-pls-desktop', 40, 10,  data.graphs.pagespeed_desktop);
		$('#circles-pls-mobile .circles-text, #circles-pls-desktop .circles-text').css({'left': '-32px', 'top': '-10px', 'font-size': '50px'});

		var optPerf = generateCircle('circles-opt-perf', 35, 7,  data.graphs.opt_perf);
		var optSec = generateCircle('circles-opt-sec', 35, 7,  data.graphs.opt_security);
		var optBP = generateCircle('circles-opt-bp', 35, 7,  data.graphs.opt_bp);
	} );
	</script>

	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link rel="preconnect" href="./InfoButton.js">
	<link href="https://fonts.googleapis.com/css?family=Raleway:300,500,700,900|Poppins:300,500,700,900" rel="stylesheet">

	<script src="https://cdn.jsdelivr.net/npm/vue@2/dist/vue.js"></script>
	<script type="text/javascript">

	Vue.component('info-button', {
		props: {metric: {type: String}},
		template: `
		<div class="info-toggle-button">
			<span v-on:click="toggleInfoDiv2(metric, $event);">❔</span>
		</div>
		`,
		methods: {
			toggleInfoDiv2: function (metric, elem) {
				toggleInfoDiv('graph-' + metric, elem);
			}
		}
	});

	var app = new Vue({
		el: '#a2-optimized-wrapper',
		data: data,
		methods: {
		}
	});
	</script>

</div> <!-- .wrap -->
