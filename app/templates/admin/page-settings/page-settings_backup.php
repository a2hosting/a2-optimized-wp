<div class="wrap">
	<div class="container-fluid" id="a2-optimized-wrapper">
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
		</div>
		<div class="row">
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

			<div class="col-sm-10 col-sm-offset-1">
				<div class="row" >
					<div class="col-sm-6">
						<div class="box-element success">
							<div class="row header">
								<div class="col-sm-8">
									<h3>{{ page_load_speed }}</h3>
								</div>
								<div class="col-sm-4 text-right">
									<p><a class="btn cta-btn-green">Run check</a><br>
									<span>Last Check: Now</span></p>
								</div>
							</div>
							<div class="row">
								<div class="col-sm-11 col-sm-offset-1">
									<div class="col-sm-5 box-element <?php echo $data['graphs']['pagespeed-mobile']['status'] ?>">
										<p class='box-title'>Mobile</p>
										<div class="circle" id="circles-pls-mobile"></div>
										<?php echo $data['graphs']['pagespeed-mobile']['change'] ?>
									</div>
									<div class="col-sm-5 col-sm-offset-1 box-element <?php echo $data['graphs']['pagespeed-desktop']['status'] ?>">
										<p class='box-title'>Desktop</p>
										<div class="circle" id="circles-pls-desktop"></div>
										<?php echo $data['graphs']['pagespeed-desktop']['change'] ?>
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
						<div class="box-element success">
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
					</div>
				</div>
			</div>
		</div>
	</div>

	<script>

	jQuery( document ).ready( function( $ ) {
		var plsMobile = generateCircle('circles-pls-mobile', 40, 10,  data.graphs['pagespeed-mobile']);
		var plsDesktop = generateCircle('circles-pls-desktop', 40, 10,  data.graphs['pagespeed-desktop']);
		$('#circles-pls-mobile .circles-text, #circles-pls-desktop .circles-text').css({'left': '-32px', 'top': '-10px', 'font-size': '50px'});

		var optPerf = generateCircle('circles-opt-perf', 35, 7,  data.graphs['opt-perf']);
		var optSec = generateCircle('circles-opt-sec', 35, 7,  data.graphs['opt-security']);
		var optBP = generateCircle('circles-opt-bp', 35, 7,  data.graphs['opt-bp']);
	} );
	</script>

	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css?family=Raleway:300,500,700,900|Poppins:300,500,700,900" rel="stylesheet">

	<script>
	let data = <?php echo $data['data_json'] ?>;

	function toggleInfoDiv(parentDivId){
		let parentDiv = jQuery('#' + parentDivId);
		let data_div = parentDiv.find('.graph_data');
		let explanation_div = parentDiv.find('.graph_info');

		if (data_div.is(":visible")){
			data_div.hide();
			explanation_div.show();
		}
		else{
			explanation_div.hide();
			data_div.show();
		}
	}

	function generateCircle(id, radius, width, graph_data){
		var circle_graph = Circles.create({
			id:                  id,
			radius:              radius,
			value:               graph_data.score,
			maxValue:            graph_data.max,
			width:               width,
			text:                graph_data.text,
			colors:              [graph_data.color + '25', graph_data.color + '55'],
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

	</script>

	<script src="https://cdn.jsdelivr.net/npm/vue@2/dist/vue.js"></script>
	<script type="text/javascript">

	var app = new Vue({
	el: '#a2-optimized-wrapper',
		data: {
			page_load_speed: 'Page Load - Vue'
		}
	})
	</script>

</div> <!-- .wrap -->
