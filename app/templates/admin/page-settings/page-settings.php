<div class="wrap">

<script> 
	let page_data = <?php echo $data['data_json'] ?>;
	page_data.showModal = false;
</script>

<script type="text/x-template" id="info-button-template">
	<div class="info-toggle-button">
		<span @click="toggleInfoDiv(metric, $event);"><span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span></span>
	</div>
</script>

<script type="text/x-template" id="graph-legend-template">
	<div class="col-sm-10 graph-legend ">
		<div class="row graph-legend-header">
			<div class="col-sm-4 success">
				good
			</div>
			<div class="col-sm-4 warn">
				not good
			</div>
			<div class="col-sm-4 danger">
				bad
			</div>
		</div>
		<div class="row">
			<div class="col-sm-4">
				&nbsp;
			</div>
			<div class="col-sm-4 left-label">
				<span>&nbsp;{{thresholds.warn}}</span>
			</div>
			<div class="col-sm-4 left-label">
				<span>&nbsp;{{thresholds.danger}}</span>
			</div>
		</div>
	</div>
</script>


<script type="text/x-template" id="hosting-matchup-template">
	<div class="col-sm-12">
		<div class="row">
			<div class="col-sm-2 side-nav">
				<p><a href="options-general.php?page=a2-optimized&a2_page=server_performance" :class="nav.webperf_class">Web Performance</a></p>
				<p><a href="options-general.php?page=a2-optimized&a2_page=hosting_matchup" :class="nav.hmatch_class">Hosting Matchup</a></p>
			</div>
			<div class="col-sm-10 border-left" id="a2-optimized-hostingmatchup">
				<div class="row">
					<div class="col-sm-6">
						<div id="graph-webperformance" class="box-element success">
							<info-button metric='webperformance'></info-button>
							<div class="graph_info" style="display:none;">
								<div class="row">
									<div class="col-sm-11 col-sm-offset-1">
										<h4>{{graphs.webperformance.display_text}}</h4>
										<p>{{graphs.webperformance.metric_text}}</p>
									</div>
								</div>
								<div class="row">
									<div class="col-sm-10 col-sm-offset-1">
										<p>{{ graphs.webperformance.explanation}}</p>
									</div>
								</div>
							</div>
							<div class="graph_data">
								<div class="row">
									<div class="col-sm-11 col-sm-offset-1">
										<h4>{{graphs.webperformance.display_text}}</h4>
										<p>{{graphs.webperformance.metric_text}}</p>
									</div>
								</div>
								<div class="row" style="max-height:500px;">
									<div v-if="graphs" class="col-sm-11 col-sm-offset-1">
										<canvas id="overall_wordpress_canvas" width="400" height="400"></canvas>
									</div>
									<div v-else>
										<p>&nbsp; no data yet</p>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-sm-6">
						<div id="graph-serverperformance" class="box-element success">
							<info-button metric='serverperformance'></info-button>
							<div class="graph_info" style="display:none;">
								<div class="row">
									<div class="col-sm-11 col-sm-offset-1">
										<h4>{{graphs.serverperformance.display_text}}</h4>
										<p>{{graphs.serverperformance.metric_text}}</p>
									</div>
								</div>
								<div class="row">
									<div class="col-sm-10 col-sm-offset-1">
										<p>{{ explanations.serverperformance}}</p>
									</div>
								</div>
							</div>
							<div class="graph_data">
								<div class="row">
									<div class="col-sm-11 col-sm-offset-1">
										<h4>{{graphs.serverperformance.display_text}}</h4>
										<p>{{graphs.serverperformance.metric_text}}</p>
									</div>
								</div>
								<div class="row" style="max-height:500px">
									<div v-if="graphs" class="col-sm-11 col-sm-offset-1">
										<canvas id="server_perf_canvas" width="400" height="400"></canvas>
									</div>
									<div v-else>
										<p>&nbsp; no data yet</p>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</script>



<script type="text/x-template" id="optimizations-performance-template">
	<div class="col-sm-12">
		<div class="row">
			<div class="col-sm-2 side-nav">
				<p><a href="#" v-on:click.prevent="sidenav = 'optperf'" :class="nav.optperf_class">Performance</a></p>
				<p><a href="#" v-on:click.prevent="sidenav = 'optsec'" :class="nav.optsec_class">Security</a></p>
				<p><a href="#" v-on:click.prevent="sidenav = 'optbestp'" :class="nav.optbestp_class">Best Practices</a></p>
				<p><a href="#" v-on:click.prevent="sidenav = 'optresults'" :class="nav.optresult_class">Results</a></p>
			</div>
			<div class="col-sm-10 border-left" id="a2-optimized-opt_performance">

				<!-- Performance -->
				<div class="row" v-show="sidenav == 'optperf'">
					<div class="col-sm-9">
						<div id="" class="box-element success">
							<div class="padding-15">
								<h3>Optimization Essentials</h3>
								<div class="row" v-for="optimization in optimizations.performance" :key="optimization.slug">
									<div class="col-sm-10">
										<h4>{{ optimization.name }} <a class="glyphicon glyphicon-chevron-down toggle" aria-hidden="true" v-on:click.prevent="desc_toggle(optimization.slug)" :id="'opt_item_toggle_' + optimization.slug"></a></h4>
										<div :id="'opt_item_desc_' + optimization.slug" style="display: none" v-html="optimization.description"></div>
									</div>
									<div class="col-sm-2 padding-top-30">
										<li class="tg-list-item">
											<input class="tgl tgl-ios" :id="'toggle-' + optimization.slug" :name="optimization.slug" v-model="optimization.configured" true-value="true" false-value="false" type="checkbox"/>
											<label class="tgl-btn" :for="'toggle-' + optimization.slug"></label>
										</li>
										<?php
										/*
										TODO: disable status on these for items that are just "on"
										list of other_optimizations
										*/
										?>
									</div>
								</div>
								<div class="row">
									<div class="col-sm-12 text-center">
										<p><a href="#" class="more-optimizations-toggle" @click.prevent="perf_more = 'true'">More Optimizations</a></p>
									</div>
								</div>
								<div v-show="perf_more == 'true'" style="display:none;">
									<div class="row" v-for="optimization in optimizations.performance" :key="optimization.slug">
										<div class="col-sm-10">
											<h4>{{ optimization.name }} <a class="glyphicon glyphicon-chevron-down toggle" aria-hidden="true" v-on:click.prevent="desc_toggle(optimization.slug)" :id="'opt_item_toggle_' + optimization.slug"></a></h4>
											<div :id="'opt_item_desc_' + optimization.slug" style="display: none" v-html="optimization.description"></div>
										</div>
										<div class="col-sm-2 padding-top-30">
											<li class="tg-list-item">
												<input class="tgl tgl-ios" :id="'toggle-' + optimization.slug" :name="optimization.slug" v-model="optimization.configured" true-value="true" false-value="false" type="checkbox"/>
												<label class="tgl-btn" :for="'toggle-' + optimization.slug"></label>
											</li>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-sm-3">
						<div class="opt-completed">	
							<h4><span>Completed</span><br />Performance<br />Optimization</h4>
							<div class="row">
								<div class="col-sm-6 col-sm-offset-3">
									<div class="box-element" style="padding-top: 5px;">
										<div class="circle" id="circles-opt-perf"></div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Security -->
				<div class="row" v-show="sidenav == 'optsec'" style="display: none">
					<div class="col-sm-9">
						<div id="" class="box-element success">
							<div class="padding-15">
								<h3>Security</h3>
								<div class="row" v-for="optimization in optimizations.security" :key="optimization.slug">
									<div class="col-sm-10">
										<h4>{{ optimization.name }} <a class="glyphicon glyphicon-chevron-down toggle" aria-hidden="true" v-on:click.prevent="desc_toggle(optimization.slug)" :id="'opt_item_toggle_' + optimization.slug"></a></h4>
										<div :id="'opt_item_desc_' + optimization.slug" style="display: none" v-html="optimization.description"></div>
									</div>
									<div class="col-sm-2 padding-top-30">
										<li class="tg-list-item">
											<input class="tgl tgl-ios" :id="'toggle-' + optimization.slug" :name="optimization.slug" v-model="optimization.configured" true-value="true" false-value="false" type="checkbox"/>
											<label class="tgl-btn" :for="'toggle-' + optimization.slug"></label>
										</li>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-sm-3">
						<div class="opt-completed">	
							<h4><span>Completed</span><br />Security<br />Optimization</h4>
							<div class="row">
								<div class="col-sm-6 col-sm-offset-3">
									<div class="box-element" style="padding-top: 5px;">
										<div class="circle" id="circles-opt-security"></div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				

				<!-- Best Practices -->
				<div class="row" v-show="sidenav == 'optbestp'" style="display: none">
					<div class="col-sm-9">
						<div class="box-element success">
							<div class="padding-15">
								<h3>Best Practices</h3>
								<div class="row" v-for="item in best_practices">
									<div class="col-sm-12">
										<h4>{{ item.title }} 
											<span v-if="item.is_warning">
												<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
											</span>
											<span v-else>
												<span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
											</span>
										</h4>
										<p v-html="item.description"></p>
										<p><a :href="item.config_url">Configure now</a></p>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-sm-3">
						<div class="opt-completed">	
							<h4><span>Completed</span><br />Best<br />Practices</h4>
							<div class="row">
								<div class="col-sm-6 col-sm-offset-3">
									<div class="box-element" style="padding-top: 5px;">
										<div class="circle" id="circles-opt-bestp"></div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>


				<!-- Results, when ready -->


				<!-- update button -->
				<div class="col-sm-9 text-right" style="padding-top: 1em;">
					<a href="#" @click.prevent="updateOptimizations" class="cta-btn-green btn-xlg btn-lg cta-btn-green text-right">Update</a>
				</div>
			</div>
		</div>
	</div>
</script>






<script type="text/x-template" id="page-speed-score-template">
	<div class="row">
		<div class="col-sm-10 col-sm-offset-1">
			<div class="row">
				<div class="col-sm-6">
					<div id="graph-pagespeed" class="box-element success">
						<info-button metric='pagespeed'></info-button>
						<div class="graph_info" style="display:none;">
							<div class="row header">
								<div class="col-sm-8">
									<h3>Page Load Speed</h3>
								</div>
								<div class="col-sm-4 text-right">
									<p><a class="btn cta-btn-green" @click.prevent="pageSpeedCheck('page_speed_score')">Run check</a><br>
									<span>Last Check: {{ last_check_date }}</span></p>
								</div>
							</div>
							<div class="row">
								<div class="col-sm-10 col-sm-offset-1">
									<p>{{ explanations.pagespeed}}</p>
								</div>
							</div>
						</div>
						<div class="graph_data">
							<div class="row header">
								<div class="col-sm-8">
									<h3>Page Load Speed</h3>
								</div>
								<div class="col-sm-4 text-right">
									<p><a class="btn cta-btn-green" @click="pageSpeedCheck('page_speed_score')">Run check</a><br>
									<span>Last Check: {{ last_check_date }}</span></p>
								</div>
							</div>
							<div class="row">
								<div class="col-sm-11 col-sm-offset-1">
									<div v-if="graphs.pagespeed_mobile" class="col-sm-5 box-element" :class="graphs.pagespeed_mobile.overall_score.color_class">
										<p class='box-title'>Mobile</p>
										<div class="circle" id="circles-pls-mobile"></div>
										<div v-if="graphs.pagespeed_mobile.overall_score.last_check_percent != 0">
										<p><span :class="graphs.pagespeed_mobile.overall_score.color_class"><span class="glyphicon" :class="'glyphicon-arrow-' + graphs.pagespeed_mobile.overall_score.last_check_dir" aria-hidden="true"></span>{{ graphs.pagespeed_mobile.overall_score.last_check_percent }}%</span> Since Last Check</p>
										</div>
										<div v-else>
											<p>&nbsp;</p>
										</div>
									</div>
									<div v-if="graphs.pagespeed_desktop" class="col-sm-5 col-sm-offset-1 box-element" :class="graphs.pagespeed_desktop.overall_score.color_class">
										<p class='box-title'>Desktop</p>
										<div class="circle" id="circles-pls-desktop"></div>
										<div v-if="graphs.pagespeed_desktop.overall_score.last_check_percent != 0">
										<p><span :class="graphs.pagespeed_desktop.overall_score.color_class"><span class="glyphicon" :class="'glyphicon-arrow-' + graphs.pagespeed_desktop.overall_score.last_check_dir" aria-hidden="true"></span>{{ graphs.pagespeed_desktop.overall_score.last_check_percent }}%</span> Since Last Check</p>
										</div>
										<div v-else>
											<p>&nbsp;</p>
										</div>
									</div>
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
						<div class="graph_info" style="display:none;">
							<div class="row header">
								<div class="col-sm-12">
									<h3>Optimization Status</h3>
								</div>
							</div>
							<div class="row">
								<div class="col-sm-10 col-sm-offset-1">
									<p>{{ explanations.opt}}</p>
								</div>
							</div>
						</div>
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
								<div class="col-sm-10 col-sm-offset-1 text-right">
									<p><a href="#" class="cta-link">Go to Recommendations</a></p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</script>

<script type="text/x-template" id="server-performance-template">
	<div class="col-sm-12">
		<div class="row" style="">
			<div class="col-sm-2 side-nav">
				<p><a href="options-general.php?page=a2-optimized&a2_page=server_performance" :class="nav.webperf_class">Web Performance</a></p>
				<p><a href="options-general.php?page=a2-optimized&a2_page=hosting_matchup" :class="nav.hmatch_class">Hosting Matchup</a></p>
			</div>
			<div class="col-sm-10 border-left" id="a2-optimized-serverperformance">
				<div class="row padding-bottom">
					<div class="col-sm-12">
						<select name="server-perf-strategy" id="server-perf-strategy-select" class="form-element" @change="strategyChanged($event)">
							<option selected value="desktop">Desktop</option>
							<option value="mobile">Mobile</option>
						</select>
						<a class="btn cta-btn-green" @click="pageSpeedCheck()">Run Check</a> <span class="last-check">Last Check: {{ last_check_date }}</span>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-4"> <!-- using graphs.ttfb -->
						<div id="graph-ttfb" class="box-element normal graph-card wave-bg" :class="graphs.ttfb.color_class">
							<info-button metric='ttfb'></info-button>
							<div class="graph_data">
								<div class="row">
									<div class="col-sm-8">
										<h4>{{graphs.ttfb.display_text}}</h4>
										<p>{{graphs.ttfb.metric_text}}</p>
									</div>
								</div>
								<div class="row">
									<div class="col-sm-6">
										<div class="circle" id="circles-ttfb"></div>
									</div>
									<div class="col-sm-6">
										<span class="glyphicon" :class="['glyphicon-arrow-' + graphs.ttfb.last_check_dir, graphs.ttfb.color_class]" style="font-size: 2em;"></span>
										<br>
										<span :class="graphs.ttfb.color_class">{{graphs.ttfb.last_check_percent}}</span>
										<span>vs <br> last check</span>
									</div>
								</div>
							</div>
							<div class="graph_info" style="display:none;">
								<div class="row">
									<div class="col-sm-12">
										<h4>{{graphs.ttfb.display_text}}</h4>
										<p>{{ graphs.ttfb.explanation}}</p>
									</div>
								</div>
							</div>
							<div class="row graph-card_bottom" style="min-height: 50px;">
								&nbsp;
							</div>
						</div>
						<div id="graph-lcp" class="box-element normal graph-card" :class="graphs.lcp.color_class">
							<info-button metric='lcp'></info-button>
							<div class="graph_data">
								<div class="row">
									<div class="col-sm-10">
										<h4>{{graphs.lcp.display_text}}</h4>
										<p>{{graphs.lcp.metric_text}}</p>
									</div>
								</div>
								<div class="row">
									<div class="col-sm-10">
										<div class="circle" id="circles-lcp"></div>
										<div class="line-graph" id="line-graph-lcp"></div>
									</div>
								</div>
								<div class="row">
									<graph-legend metric='lcp'></graph-legend>
								</div>
							</div>
							<div class="graph_info" style="display:none;">
								<div class="row">
									<div class="col-sm-10">
										<h4>{{graphs.lcp.display_text}}</h4>
										<p>{{ graphs.lcp.explanation}}</p>
									</div>
								</div>
							</div>
						</div>
						<div id="graph-fid" class="box-element normal graph-card" :class="graphs.fid.color_class">
							<info-button metric='fid'></info-button>
							<div class="graph_data">
								<div class="row">
									<div class="col-sm-12">
										<h4>{{graphs.fid.display_text}}</h4>
										<p>{{graphs.fid.metric_text}}</p>
									</div>
								</div>
								<div class="row">
									<div class="col-sm-10">
										<div class="circle" id="circles-fid"></div>
										<div class="line-graph" id="line-graph-fid"></div>
									</div>
								</div>
								<div class="row">
									<graph-legend metric='fid'></graph-legend>
								</div>
							</div>
							<div class="graph_info" style="display:none;">
								<div class="row">
									<div class="col-sm-12">
										<h4>{{graphs.fid.display_text}}</h4>
										<p>{{ graphs.fid.explanation}}</p>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-sm-4 bg-green">
						<div id="graph-overall_score" class="box-element normal graph-card-centered" :class="graphs.overall_score.color_class">
							<info-button metric='overall_score'></info-button>
							<div class="graph_data">
								<div class="row">
									<div class="col-sm-12">
										<h4>{{graphs.overall_score.display_text}}</h4>
										<p>{{graphs.overall_score.metric_text}}</p>
									</div>
								</div>
								<div class="row">
									<div class="col-sm-12">
										<div class="circle" id="circles-overall_score"></div>
									</div>
								</div>
								<div class="row">
									<div class="col-sm-12">
										<span class="glyphicon" :class="['glyphicon-arrow-' + graphs.overall_score.last_check_dir,graphs.overall_score.color_class]" style="font-size: 2em;"></span>
										<br>
										<span :class="graphs.overall_score.color_class">{{graphs.overall_score.last_check_percent}}</span>
										<span>vs <br> last check</span>
									</div>
								</div>
							</div>
							<div class="graph_info" style="display:none;">
								<div class="row">
									<div class="col-sm-12">
										<h4>{{graphs.overall_score.display_text}}</h4>
										<p>{{ graphs.overall_score.explanation}}</p>
									</div>
								</div>
							</div>
						</div>
						<div id="graph-Recommendations" class="box-element normal graph-card-centered ">
							<info-button metric='Recommendations'></info-button>
							<div class="graph_data">
								<div class="row">
									<div class="col-sm-12">
										<h4>{{graphs.recommendations.display_text}}</h4>
									</div>
								</div>
								<div class="row text-left" >
									<div class="col-sm-12">
									<ul>
										<li v-for="recommendation in graphs.recommendations.list" :id="'rec_item_' + recommendation.lcv">
											{{recommendation.display_text}} <a v-on:click.prevent='rec_toggle(recommendation.lcv)'><span :id="'rec_item_toggle_' + recommendation.lcv" class="glyphicon glyphicon-chevron-right toggle" aria-hidden="true"></span></a>
											<span style="display:none" :id="'rec_item_desc_' + recommendation.lcv ">
												<span v-html='recommendation.description'></span>
											</span>
										</li>
									</ul>
									</div>
								</div>
							</div>
							<div class="graph_info" style="display:none;">
								<div class="row">
									<div class="col-sm-12">
										<h4>{{graphs.recommendations.display_text}}</h4>
										<p>{{ graphs.recommendations.explanation}}</p>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-sm-4">
						<div id="graph-fcp" class="box-element normal graph-card" :class="graphs.fcp.color_class" >
							<info-button metric='fcp'></info-button>
							<div class="graph_data">
								<div class="row">
									<div class="col-sm-10">
										<h4>{{graphs.fcp.display_text}}</h4>
										<p>{{graphs.fcp.metric_text}}</p>
									</div>
								</div>
								<div class="row">
									<div class="col-sm-10">
										<div class="circle" id="circles-fcp"></div>
										<div class="line-graph" id="line-graph-fcp"></div>
									</div>
								</div>
								<div class="row">
									<graph-legend metric='fcp'></graph-legend>
								</div>
							</div>
							<div class="graph_info" style="display:none;">
								<div class="row">
									<div class="col-sm-10">
										<h4>{{graphs.fcp.display_text}}</h4>
										<p>{{ graphs.fcp.explanation}}</p>
									</div>
								</div>
							</div>
						</div>
						<div id="graph-cls" class="box-element normal graph-card wave-bg" :class="graphs.cls.color_class">
							<info-button metric='cls'></info-button>
							<div class="graph_data">
								<div class="row">
									<div class="col-sm-10">
										<h4>{{graphs.cls.display_text}}</h4>
										<p>{{graphs.cls.metric_text}}</p>
									</div>
								</div>
								<div class="row">
									<div class="col-sm-6">
										<div class="circle" id="circles-cls"></div>
									</div>
									<div class="col-sm-6">
										<span class="glyphicon" :class="['glyphicon-arrow-' + graphs.cls.last_check_dir,graphs.cls.color_class]" style="font-size: 2em;"></span>
										<br>
										<span :class="graphs.cls.color_class">{{graphs.cls.last_check_percent}}</span>
										<span>vs <br> last check</span>
									</div>
								</div>
								<div class="row">
									<graph-legend metric='cls'></graph-legend>
								</div>
							</div>
							<div class="graph_info" style="display:none;">
								<div class="row">
									<div class="col-sm-12">
										<h4>{{graphs.cls.display_text}}</h4>
										<p>{{ graphs.cls.explanation}}</p>
									</div>
								</div>
							</div>
							<div class="row">
							</div>
							<div class="row graph-card_bottom" style="min-height: 50px;">
								&nbsp;
							</div>
						</div>
						<div class="text-center">
							<a href="#" class="btn btn-lg cta-btn-green text-right">Improve Score</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</script>

<script type="text/x-template" id="modal-template">
	<transition name="modal">
		<div class="modal-mask">
			<div class="modal-wrapper">
				<div class="modal-container">

					<div class="modal-header">
						<slot name="header"></slot>
						<!-- <span class="glyphicon glyphicon-remove" style="font-size: 2em;" @click="$emit('close')"></span> -->
					</div>

					<div class="modal-body">
						<slot name="body">
							<p>It'll just take a few moments to update your scores</p>
						</slot>
					</div>

					<div class="modal-footer">
						<slot name="footer"></slot>
						<div class="row">
							<div class="col-sm-4"></div>
							<div class="col-sm-4">
								<div class="item-loader-container">
									<div class="la-line-spin-fade-rotating la-2x la-dark">
										<div></div>
										<div></div>
										<div></div>
										<div></div>
										<div></div>
										<div></div>
										<div></div>
										<div></div>
									</div>
								</div> 
							</div>
							<div class="col-sm-4"></div>
						</div>
					</div>
				</div><!-- blah -->
			</div>
		</div>
	</transition>
</script>

	<div class="container-fluid"  id="a2-optimized-wrapper">
		<modal v-if="showModal" @close="showModal = false">
		</modal>
		<div class="row" id="a2-optimized-header">
			<div class="col-sm-6 title">
				<h2>Optimization <span class='normal'>Dashboard</span></h2>
			</div>
			<div class="col-sm-4 search">
				<input type="text" value="<?php echo get_site_url(); ?>" />
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
				<span class="thishost"></span>
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
						<p><a href="options-general.php?page=a2-optimized&a2_page=optimizations" class="<?php echo $data['nav']['opt_class'] ?>">Optimization</a></p>
					</div>
				</div>
			</div>
		</div>
		<?php echo $data['content-element'] ?>
	</div>

</div> <!-- .wrap -->

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>