<div class="wrap">
<script> 
	let page_data = <?php echo $data['data_json'] ?>;
	page_data.login_url = '<?php echo esc_url(get_home_url()) . "/wp-login.php" ?>';
	page_data.show_coaching = false;
	page_data.showModal = false;
	page_data.modalMsg = '';
	page_data.openModal = function(msg){
		page_data.showModal = true;
		page_data.modalMsg = msg;
	}
	page_data.closeModal = function(){
		page_data.showModal = false;
		page_data.modalMsg = '';
	}
	page_data.showA2Only = false;
	page_data.yesNoDialog = {
		showYesNo: false,
		message: 'default',
		doYes: () => {
			if (page_data.yesNoDialog.yesAction){
				page_data.yesNoDialog.yesAction();
			}
			page_data.yesNoDialog.showYesNo = false;
		},
		doNo: () => {
			if (page_data.yesNoDialog.noAction){
				page_data.yesNoDialog.noAction();
			}
			page_data.yesNoDialog.showYesNo = false;
		},
		noAction: null,
		yesAction: null,
	};
	page_data.strategies = [
		{option: 'Desktop', value: 'desktop'},
		{option: 'Mobile', value: 'mobile'},
	];
	page_data.frontend_benchmark_status = '';
</script>

<script type="text/x-template" id="info-button-template">
	<div class="info-toggle-button">
		<span @click="toggleInfoDiv(metric, $event);"><span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span></span>
	</div>
</script>

<script type="text/x-template" id="flip-panel-template">
	<div :id="content_id" class="flip-card">
		<div class="flip-card-inner">
			<div class="flip-card-front">
				<div class="box-element" :class="[additional_classes, status_class]">
					<div v-if="!disable_show_button" class="info-toggle-button" title="More Information">
						<span @click="toggleFlipPanel(content_id, $event);"><span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span></span>
					</div>
					<slot name="content1"></slot>
				</div>
			</div>
			<div class="flip-card-back" style="display:none;">
				<div class="box-element" :class="[additional_classes, status_class]">
					<div class="info-toggle-button" title="Close">
						<span @click="toggleFlipPanel(content_id, $event);"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></span>
					</div>
					<slot name="content2"></slot>
				</div>
			</div>
		</div>
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

<script type="text/x-template" id="speed-metric-card-template">
<flip-panel :content_id="'graph-' + metric_name" 
	:status_class="metric.color_class" 
	:additional_classes="'normal graph-card ' + (show_wave == 'true' ? 'wave-bg ' : '')">
	<template v-slot:content1>
		<div class="row">
			<div class="col-sm-10">
				<h4>{{metric.display_text}}</h4>
				<p class="sub-heading">{{metric.metric_text}}</p>
			</div>
		</div>
		<div v-if="show_line == 'true'" class="row">
			<div class="col-sm-10">
				<div class="circle" :id="'circles-' + metric_name"></div>
				<div class="line-graph" :id="'line-graph-' + metric_name"></div>
			</div>
		</div>
		<div v-else class="row">
			<div class="col-sm-6">
				<div class="circle" :id="'circles-' + metric_name"></div>
			</div>
			<div class="col-sm-6">
				<span class="glyphicon" :class="['glyphicon-arrow-' + metric.last_check_dir,metric.color_class]" style="font-size: 2em;"></span>
				<br>
				<span :class="metric.color_class">{{metric.last_check_percent}}</span>
				<span>vs <br> last check</span>
			</div>
		</div>
		<div v-if="show_legend == 'true'" class="row">
			<graph-legend :metric='metric_name'></graph-legend>
		</div>
		<div v-if="show_wave == 'true'" class="row graph-card_bottom" style="min-height: 50px;">
			&nbsp;
		</div>
	</template>
	<template v-slot:content2>
		<div class="row">
			<div class="col-sm-10">
				<h4>{{metric.display_text}}</h4>
				<p>{{metric.explanation}}</p>
			</div>
		</div>
	</template>
</flip-panel>
</script>

<script type="text/x-template" id="hosting-matchup-template">
	<div class="col-sm-12">
		<div class="row">
			<performance-sidebar @nav-change-url="this.$parent.loadSubPage"></performance-sidebar>
			<div class="col-md-12 col-lg-10 border-left" id="a2-optimized-hostingmatchup">
				<div class="row">
					<div class="col-sm-12">
						<a class="btn cta-btn-green" @click="pageSpeedCheck()">Run Check</a> <span class="last-check">Last Check: {{ last_check_date }}</span>
					</div>
				</div>
				<div v-if="show_coaching" class="row">
					<div class="col-sm-12">
						<div class="notice notice-warning"><p>Click Run Check to see the overall performance of your site.</p></div>
					</div>
				</div>
				<div class="row padding-bottom"></div>
				<div class="row">
					<div class="col-md-6 col-md-offset-3 col-lg-6 col-lg-offset-0 hosting-matchup-graph-container">
						<flip-panel content_id="graph-webperformance" 
							status_class="success" 
							additional_classes="">
							<template v-slot:content1>
								<div class="row">
									<div class="col-sm-11 col-sm-offset-1">
										<h4>{{graphs.webperformance.display_text}}</h4>
										<p><span v-html="graphs.webperformance.metric_text"></span></p>
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
							</template>
							<template v-slot:content2>
								<div class="row">
									<div class="col-sm-11 col-sm-offset-1">
										<h4>{{graphs.webperformance.display_text}}</h4>
									</div>
								</div>
								<div class="row">
									<div class="col-sm-10 col-sm-offset-1">
										<p><span v-html="graphs.webperformance.explanation"></span></p>
									</div>
								</div>
							</template>
						</flip-panel>
					</div>
					<div class="col-md-6 col-md-offset-3 col-lg-6 col-lg-offset-0 hosting-matchup-graph-container">
						<flip-panel content_id="graph-serverperformance" 
							status_class="success" 
							additional_classes="">
							<template v-slot:content1>
								<div class="row">
									<div class="col-sm-11 col-sm-offset-1">
										<h4>{{graphs.serverperformance.display_text}}</h4>
										<p><span v-html="graphs.serverperformance.metric_text"></span></p>
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
							</template>
							<template v-slot:content2>
								<div class="row">
									<div class="col-sm-11 col-sm-offset-1">
										<h4>{{graphs.serverperformance.display_text}}</h4>
									</div>
								</div>
								<div class="row">
									<div class="col-sm-10 col-sm-offset-1">
										<p><span v-html="graphs.serverperformance.explanation"></span></p>
									</div>
								</div>
							</template>
						</flip-panel>
					</div>
				</div>
			</div>
		</div>
	</div>
</script>

<script type="text/x-template" id="advanced-settings-template">
	<div class="col-sm-12">
		<div class="row">
			<performance-sidebar @nav-change-url="this.$parent.loadSubPage"></performance-sidebar>
			<div class="col-md-12 col-lg-10 border-left" id="a2-optimized-advanced-settings">
				<opt-extra-settings :extra_settings="advanced_settings" slug_override="advanced">
				</opt-extra-settings>
			</div>

			<div class="col-sm-9 text-right" style="padding-top: 1em;">
				<a href="#" @click.prevent="updateAdvancedOptions()" class="cta-btn-green btn-xlg btn-lg cta-btn-green text-right">Update</a>
			</div>
		</div>
	</div>
</script>


<script type="text/x-template" id="optimization-entry">
<div>
	<div class="row margin-bottom-20">
		<div class="col-md-8 col-lg-9 ">
			<h4 class="less-vertical-space">{{ name }} <a class="glyphicon glyphicon-chevron-down toggle" aria-hidden="true" v-on:click.prevent="desc_toggle(slug)" :id="'opt_item_toggle_' + slug"></a></h4>
			<span v-html="extra_info"></span>
			<div :id="'opt_item_desc_' + slug" style="display: none" v-html="description" class="desc"></div>
		</div>
		<div class="col-md-1 col-lg-1  padding-top-30">
			<a v-if="extra_setting" href="javascript:void(0)" @click="toggleExtraSettings(slug, $event)">Modify</a>
		</div>
		<div class="col-md-3 col-lg-2  padding-top-30" >
			<li class="tg-list-item" @click="optimizationClicked(disabled)" @change="settingToggled($event)">
				<input class="tgl tgl-ios" :id="'toggle-' + slug" :name="slug" v-model="configured" true-value="true" false-value="false" type="checkbox" :disabled="disabled"/>
				<label class="tgl-btn" :for="'toggle-' + slug"></label>
			</li>
		</div>
	</div>
</div>
</script>

<script type="text/x-template" id="opt-extra-settings-template">
	<div v-if="opt_group" :key="selected_slug">
		<div class="row header">
			<div class="col-lg-8">
				<h3>{{ opt_group.title }}</h3>
			</div>
		</div>
		<div v-for="section in opt_group.settings_sections" class="padding-15 opt-extra-settings-items">
			<div class="row">
				<div class="col-lg-10">
					<h4 v-if="section.title" class="less-vertical-space section-title">{{ section.title }}</h4>
				</div>
			</div>
			<div v-for="(setting, setting_name) in section.settings" :key="setting_name" :id="'setting-' + setting_name" class="setting-item">
				<div class="row">
					<div v-if="setting.extra_fields">
						<div class="col-md-6 col-lg-6">
							<h4 class="less-vertical-space setting-desc-extra">{{ setting.description }}</h4>
							<p v-if="setting.label">
								<span v-html="setting.label"></span>
							</p>
						</div>
						<div class="col-md-3 col-lg-4">
							<div v-for="(field, field_name) in setting.extra_fields">
								<input :name="field_name" :id="field_name" :type="field.input_type" v-model="field.value" :disabled="field.disabled == '1'"></input>
							</div>
						</div>
					</div>
					<div v-else>
						<div class="col-md-9">
							<h4 class="less-vertical-space setting-desc">{{ setting.description }}</h4>
							<p v-if="setting.label">
								<span v-html="setting.label"></span>
							</p>
						</div>
					</div>
					<p v-if="setting.input_type == 'text'">
						<input type="text" :id="'cb-' + setting_name" :name="setting_name" v-model="setting.value" class="opt-setting-input text-input" :disabled="setting.disabled == '1'" />
					</p>
					<div v-else-if="setting.input_type == 'options'" class="col-md-3">
						<select :id="'select-' + setting_name" :name="setting_name" v-model="setting.value" @change="adjustSettingVisibility()">
							<option v-for="(opt_value,label) in setting.input_options" :value="opt_value" :selected="opt_value == setting.value">{{ label }}</option>
						</select>
					</div>
					<div v-else class="col-md-3 col-lg-2 text-right" :class="setting.extra_fields ? '' : 'col-lg-offset-1'">
						<li class="tg-list-item">
							<input class="tgl tgl-ios" :id="'toggle-' + setting_name" :name="setting_name" 	
							true-value="true" false-value="false" v-model="setting.value" type="checkbox"/>
							<label class="tgl-btn" :for="'toggle-' + setting_name"></label>
						</li>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-10">
						{{ setting.explanation }}
					</div>
				</div>
			</div>
		</div>
	</div>
</script>

<script type="text/x-template" id="optimizations-performance-template">
	<div class="col-sm-12">
		<div class="row">
			<div class="col-md-12 col-lg-2 side-nav">
				<div id='optperf-wrapper' class="col-md-3 col-lg-12 navlink-wrapper">
					<button type="button" v-on:click.prevent="updateNavLinks('optperf')" 
					class="navlink-button" :class="[sidenav == 'optperf' ? 'current' : '']">Performance</button>
				</div>
				<div id='optsec-wrapper' class="col-md-3 col-md-offset-1 col-lg-12 col-lg-offset-0 navlink-wrapper">
					<button type="button" v-on:click.prevent="updateNavLinks('optsec')" 
					class="navlink-button" :class="[sidenav == 'optsec' ? 'current' : '']">Security</button>
				</div>
				<div  id='optbestp-wrapper' class="col-md-3 col-md-offset-1 col-lg-12 col-lg-offset-0 navlink-wrapper">
					<button type="button" v-on:click.prevent="updateNavLinks('optbestp')" 
					class="navlink-button" :class="[sidenav == 'optbestp' ? 'current' : '']">Best Practices</button>
				</div>
				<!--
				<div class="col-md-4 col-lg-12 navlink-wrapper" :class="nav.optresult_class">
					<a name='optresults' v-on:click.prevent="updateNavLinks('optresults')" class="navlink">Results</a>
				</div> -->
			</div>
			<div class="col-md-12 col-lg-10 border-left" id="a2-optimized-opt_performance">
				<!-- Performance -->
				<div class="row" v-show="sidenav == 'optperf'">
					<div class="col-sm-9">
						<flip-panel content_id="optimizations_performance_front" 
							status_class="success" 
							additional_classes=""
							disable_show_button=true>
							<template v-slot:content1>
								<div class="padding-15">
									<h3>Optimization Essentials</h3>
									<optimization-entry v-for="optimization in optimizations.performance" :key="optimization.slug" :opt="optimization" wrapper_id="optimizations_performance_front"></optimization-entry>
									<div class="row" v-show="perf_more == 'false'">
										<div class="col-sm-12 text-center">
											<p><a href="#" class="more-optimizations-toggle" @click.prevent="perf_more = 'true'">More Optimizations</a></p>
										</div>
									</div>
									<optimization-entry v-show="perf_more == 'true'"  v-for="optimization in other_optimizations.performance" :key="optimization.slug" :opt="optimization" wrapper_id="optimizations_performance_front"></optimization-entry>
									<div class="row" v-show="perf_more == 'true'">
										<div class="col-sm-12 text-center">
											<p><a href="#" class="more-optimizations-toggle" @click.prevent="perf_more = 'false'">Less Optimizations</a></p>
										</div>
									</div>
								</div>
							</template>
							<template v-slot:content2>
								<opt-extra-settings :extra_settings="extra_settings">
								</opt-extra-settings>
							</template>
						</flip-panel>
					</div>
					<div class="col-sm-3">
						<div class="opt-completed">	
							<h4><span>Completed</span><br />Performance<br />Optimization</h4>
							<div class="row">
								<div class="col-sm-6 col-sm-offset-3">
									<div class="box-element hide-small" style="padding-top: 5px;">
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
								<optimization-entry v-for="optimization in optimizations.security" :key="optimization.slug" :opt="optimization"></optimization-entry>
								<div class="row" v-show="sec_more == 'false'">
									<div class="col-sm-12 text-center">
										<p><a href="#" class="more-optimizations-toggle" @click.prevent="sec_more = 'true'">More Optimizations</a></p>
									</div>
								</div>
								<optimization-entry v-show="sec_more == 'true'" v-for="optimization in other_optimizations.security" :key="optimization.slug" :opt="optimization"></optimization-entry>
								<div class="row" v-show="sec_more == 'true'">
									<div class="col-sm-12 text-center">
										<p><a href="#" class="more-optimizations-toggle" @click.prevent="sec_more = 'false'">Less Optimizations</a></p>
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
									<div class="box-element hide-small" style="padding-top: 5px;">
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
						<div class="box-element clear">
							<div class="padding-15">
								<h3>Best Practices</h3>
								<div class="row" v-for="item in best_practices">
									<div class="col-sm-12 box-element" :class="item.color_class">
										<h4 class="less-vertical-space">
											<div class="row">
												<div class="col-sm-8 col-lg-9">
													{{ item.title }}
													<span v-if="item.status.is_warning" :class="item.color_class"> - WARNING</span>
													<span v-else :class="item.color_class"> - GOOD</span>
												</div>
												<div v-if="!item.hasOwnProperty('slug')" class="col-md-4 col-lg-3 text-right line-height-15">
	 												<span v-if="item.status.is_warning">
														<span class="glyphicon glyphicon-remove-circle"  :class="item.color_class" aria-hidden="true"></span>
													</span>
													<span v-else>
														<span class="glyphicon glyphicon-ok-circle"  :class="item.color_class" aria-hidden="true"></span>
													</span>
													<a :href="item.config_url" target="a2opt_config">Modify</a><br><span class="small">via wordpress</span>
												</div>
												<div v-else class="col-md-4 col-lg-3 text-right">
													<span v-if="item.status.is_warning">
														<span class="glyphicon glyphicon-remove-circle"  :class="item.color_class" aria-hidden="true"></span>
													</span>
													<span v-else>
														<span class="glyphicon glyphicon-ok-circle"  :class="item.color_class" aria-hidden="true"></span>
													</span>
													<a href="" @click.prevent="promptToUpdate($event, 'Are you sure?', 'This will log you out.  Click Yes to proceed.', item.slug, 'true')" >Update</a>
												</div>
											</div>
										</h4>
										<div class="best-practices-status">
											<strong>Status: </strong><span :class="item.color_class">{{ item.status.current }}</span><br>
											<strong>Best Practice: </strong><span v-html="item.description"></span>
										</div>
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
									<div class="box-element hide-small" style="padding-top: 5px;">
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
		<div class="col-md-10 col-md-offset-2 col-lg-10 col-lg-offset-1">
			<div class="row">
				<div class="col-sm-6" id="a2-optimized-pagespeed">
					<flip-panel content_id="graph-pagespeed" status_class="success">
						<template v-slot:content1>
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
								<div class="col-sm-10 col-sm-offset-1">
									<div v-if="show_coaching" class="notice notice-warning">
										<p>Click Run Check to see how fast your page loads. The higher the score the better!</p>
									</div>
									<div v-if="frontend_benchmark_status" class="notice notice-warning">
										<p v-html="frontend_benchmark_status"></p>
									</div>
								</div>
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
						</template>
						<template v-slot:content2>
							<div class="row header">
								<div class="col-sm-8">
									<h3>Page Load Speed</h3>
								</div>
							</div>
							<div class="row">
								<div class="col-sm-10 col-sm-offset-1">
									<p>{{ explanations.pagespeed}}</p>
								</div>
							</div>
						</template>
					</flip-panel>
					<div class="row">
						<div class="col-sm-7">
							<p>Data pulled from Google PageSpeed Insights</p>
						</div>
						<div class="col-sm-5 text-right">
							<p>Compare with <a href="https://gtmetrix.com/?url=<?php echo home_url(); ?>" target="_blank">GTMetrix</a></p>
						</div>
					</div>
				</div>
				<div class="col-sm-6" id="a2-optimized-optstatus">
					<flip-panel content_id="graph-opt" status_class="success">
						<template v-slot:content1>
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
												{{ graphs.performance.display_text }}
											</div>
											<div class="col-sm-4 text-center">
												<div class="circle" id="circles-opt-sec"></div>
												{{ graphs.security.display_text }}
											</div>
											<div class="col-sm-4 text-center">
												<div class="circle" id="circles-opt-bestp"></div>
												{{ graphs.bestp.display_text }}
											</div>
										</div>
									</div>
								</div>
								<div class="col-sm-10 col-sm-offset-1 text-right">
									<p><a href="admin.php?page=a2-optimized&a2_page=optimizations" class="cta-link">Go to Recommendations</a></p>
								</div>
							</div>
						</template>
						<template v-slot:content2>
							<div class="row header">
								<div class="col-sm-12">
									<h3>Optimization Status</h3>
								</div>
							</div>
							<div class="row">
								<div class="col-sm-10 col-sm-offset-1">
									<p v-html="explanations.opt"></p>
								</div>
							</div>
						</template>
					</flip-panel>
				</div>
			</div>
		</div>
	</div>
</script>

<script type="text/x-template" id="performance-sidebar-template">
	<div class="col-md-12 col-lg-2 side-nav">
		<div class="col-md-5 col-md-offset-1 col-lg-12 col-lg-offset-0 navlink-wrapper">
			<button type="button" @click="$emit('nav-change-url', 'admin.php?page=a2-optimized&a2_page=server_performance')" 
			class="navlink-button"  :class="nav.webperf_class">Web Performance</button>
		</div>
		<div class="col-md-5 col-md-offset-1 col-lg-12 col-lg-offset-0 navlink-wrapper">
			<button type="button" @click="$emit('nav-change-url', 'admin.php?page=a2-optimized&a2_page=hosting_matchup')" 
			class="navlink-button" :class="nav.hmatch_class">Hosting Matchup</button>
		</div>
		<div class="col-md-5 col-md-offset-1 col-lg-12 col-lg-offset-0 navlink-wrapper">
			<button type="button" @click="$emit('nav-change-url', 'admin.php?page=a2-optimized&a2_page=advanced_settings')" 
			class="navlink-button" :class="nav.advs_class">Advanced Settings</button>
		</div>
	</div>
</script>

<script type="text/x-template" id="server-performance-template">
	<div class="col-sm-12">
		<div class="row">
			<performance-sidebar @nav-change-url="this.$parent.loadSubPage"></performance-sidebar>
			<div class="col-md-12 col-lg-10 border-left" id="a2-optimized-serverperformance">
				<div class="row">
					<div class="col-sm-12">
						<select name="server-perf-strategy" id="server-perf-strategy-select" class="form-element" @change="strategyChanged($event)">
							<option v-for="s in strategies" :value="s.value" :selected="s.value == default_strategy">{{s.option}}</option>
						</select>
						<a class="btn cta-btn-green" @click="pageSpeedCheck()">Run Check</a> <span class="last-check">Last Check: {{ last_check_date }}</span>
					</div>
				</div>
				<div v-if="show_coaching" class="row">
					<div class="col-sm-12">
						<div class="notice notice-warning"><p>Click Run Check to see how fast your page loads.</p></div>
					</div>
				</div>
				<div v-if="frontend_benchmark_status" class="notice notice-warning">
					<p v-html="frontend_benchmark_status"></p>
				</div>
				<div class="row padding-bottom"></div>
				<div class="row">
					<div class="col-sm-4"> <!-- using graphs.ttfb -->
						<speed-metric-card metric_name="ttfb" :metric="graphs.ttfb" show_line=false></speed-metric-card>
						<speed-metric-card metric_name="lcp" :metric="graphs.lcp" show_wave=false></speed-metric-card>
						<speed-metric-card metric_name="fid" :metric="graphs.fid" show_wave=false></speed-metric-card>
					</div>
					<div class="col-sm-4" :class="last_check_date == 'None' ? 'bg-empty' : 'bg-green'">
						<flip-panel content_id="graph-overall_score" 
							:status_class="graphs.overall_score.color_class" 
							additional_classes="normal graph-card-centered">
							<template v-slot:content1>
								<div class="row">
									<div class="col-sm-12">
										<h4>{{graphs.overall_score.display_text}}</h4>
										<p><span v-html="graphs.overall_score.metric_text"></span></p>
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
							</template>
							<template v-slot:content2>
								<div class="row">
									<div class="col-sm-12">
										<h4>{{graphs.overall_score.display_text}}</h4>
										<p><span v-html="graphs.overall_score.explanation"></span></p>
									</div>
								</div>
							</template>
						</flip-panel>
						<flip-panel content_id="graph-Recommendations" 
							additional_classes="normal graph-card-centered">
							<template v-slot:content1>
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
							</template>
							<template v-slot:content2>
								<div class="row">
									<div class="col-sm-12">
										<h4>{{graphs.recommendations.display_text}}</h4>
										<p>{{ graphs.recommendations.explanation}}</p>
									</div>
								</div>
							</template>
						</flip-panel>
					</div>
					<div class="col-sm-4">
						<speed-metric-card metric_name="fcp" :metric="graphs.fcp" show_wave=false show_line=true></speed-metric-card>
						<speed-metric-card metric_name="cls" :metric="graphs.cls" show_line=false></speed-metric-card>
						<div class="text-center">
							<a href="admin.php?page=a2-optimized&a2_page=optimizations" class="btn btn-lg cta-btn-green text-right">Improve Score</a>
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

					<div class="modal-top-bar">
						<span v-show="show_close" class="glyphicon glyphicon-remove" style="font-size: 2em;" @click="$emit('close')"></span>
					</div>

					<div class="modal-header">
						<slot name="header">
							<p>{{modalMsg}}</p>
						</slot>
					</div>

					<div class="modal-body">
						<slot name="body">
						</slot>
					</div>

					<div class="modal-footer">
						<slot name="footer"></slot>
					</div>
					<div v-if="show_busy=='true'" class="row">
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
			</div>
		</div>
	</transition>
</script>

	<div class="container-fluid"  id="a2-optimized-wrapper">
		<modal v-if="showModal" @close="showModal = false" show_busy=true>
		</modal>
		<modal v-if="showA2Only" @close="showA2Only = false" show_busy=false show_close=true>
			<template v-slot:header><h3 class="text-center" >This feature is not supported on your current hosting environment</h3></template>
			<template v-slot:body>
				<p>At A2 Hosting, our servers are built around providing unbeatable speed.  As such, A2 Optimized is configured with speed in mind, and works best when installed on our servers where we can make your site lighting fast!</p>
				<p>As an A2 Hosting customer, you get access to the following features for Free:</p>
				<div class="row">
					<div class="col-sm-2"></div>
					<div class="col-sm-8">
						<ul class="bullet-points">
							<li>Object Caching*</li>
							<li>Login URL Change</li>
							<li>Compress Images on upload</li>
							<li>Turbo web hosting*</li>
						</ul>
					</div>
					<div class="col-sm-2"></div>
				</div>
				<p>Join the A2 family and get access to these amazing speed-enhancing features to give your site the ultimate speed boost.  Best of all we offer FREE website migration!</p>
			</template>
			<template v-slot:footer>
				<p><small>* Feature supported on all managed plans except on the shared plans Startup and Drive</small></p>
				<a href="https://www.a2hosting.com/hosting" target="_blank" class="btn cta-btn-green">Get Started</a>
			</template>
		</modal>
		<modal v-if="yesNoDialog.showYesNo" @close="yesNoDialog.showYesNo = false" show_busy=false show_close=true>
			<template v-slot:header><h3>{{ yesNoDialog.header }}</h3></template>
			<template v-slot:body>
				<span class="modal-dialog-text">{{ yesNoDialog.message }}</span>
			</template>
			<template v-slot:footer>
				<a class="btn cta-btn-green" @click="yesNoDialog.doNo" >Cancel</a>
				<a class="btn cta-btn-green" @click="yesNoDialog.doYes">Ok</a></template>
		</modal>
		<div class="row" id="a2-optimized-header">
			<div class="col-sm-10 title">
				<img srcset=" <?php echo esc_url(get_home_url()); ?>/wp-content/plugins/a2-optimized-wp/assets/images/admin/a2opt-logo-2022-2x.png 2x, <?php echo esc_url(get_home_url()); ?>/wp-content/plugins/a2-optimized-wp/assets/images/admin/a2opt-logo-2022.png 1x " src="<?php echo esc_url(get_home_url()); ?>/wp-content/plugins/a2-optimized-wp/assets/images/admin/a2opt-logo-2022.png" >
			</div>
			<div class="col-sm-2 text-right">
				<div class="utility-icon">
					<a id="drop-bell" href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" title="Alerts">
						<span id="drop-bell-wrapper" class="glyphicon glyphicon-bell notification-bell" aria-hidden="true"></span>
					</a>
					<ul id="menu-bell" class="dropdown-menu" aria-labelledby="drop-bell-wrapper">
						<!-- comment until the notifications are ready
						<li v-for="(content, id) in notifications">
							<div class="">
								{{ content }}
							</div>	
						</li>
						-->
					</ul>
				</div>
				<div class="utility-icon">
					<a id="drop-links" href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" title="More Information">
						<span id="drop-links-wrapper" class="glyphicon glyphicon-option-vertical" aria-hidden="true"></span>
					</a>
					<ul id="menu-links" class="dropdown-menu" aria-labelledby="drop-links-wrapper">
						<li><a target="_blank" href="https://my.a2hosting.com/">A2 Client Area</a></li>
						<li><a target="_blank" href="https://my.a2hosting.com/submitticket.php?action=support">A2 Client Support</a></li>
						<li><a target="_blank" href="https://wordpress.org/support/plugin/a2-optimized-wp/">Wordpress Support</a></li>
						<li><a target="_blank" href="https://www.a2hosting.com/kb/collections/wordpress-articles">Wordpress Knowledge Base</a></li>
					</ul>
				</div>
			</div>
			<div id="color-palette" style="display:none;">
				<span class="success"></span>
				<span class="warn"></span>
				<span class="danger"></span>
				<span class="thishost"></span>
				<span class="empty"></span>
			</div>
		</div>
		<?php $reporting_active = get_option('a2_sitedata_allow');
		if(!$reporting_active){ 
			$data_nonce = wp_create_nonce('a2opt_datacollection_nonce');
			?>
		<div class="row">
			<div class="notice notice-warning">
				<p><strong>Help Us Get You the Best Performance Possible</strong></p>
				<p>A2 Hosting would like to collect technical data about your installation. This data will be used to make sure that the A2 Optimized plugin works seamlessly on the widest possible range of WordPress sites. A full list of the data that is collected can be found <a href="https://www.a2hosting.com/kb/installable-applications/optimization-and-configuration/wordpress2/information-collected-by-the-a2-optimized-wp-plugin" target="_blank">here</a>.</p>
				<p><a href="admin.php?page=a2-optimized&data-collection=yes&nonce=<?php echo $data_nonce; ?>" class="btn btn-primary">Yes, I would like to help</a>&nbsp;<a href="admin.php?page=a2-optimized&data-collection=no&nonce=<?php echo $data_nonce; ?>" class="btn btn-default">No, thank you</a></p>
			</div>
		</div>
		<?php }; ?>
		<div class="row" id="a2-optimized-nav">
			<div class="col-md-12 col-lg-11 col-lg-offset-1">
				<div class="row a2-optimized-navigation" id="a2-optimized-navigation">
					<div class="col-md-4 col-lg-3">
						<button type="button" class="navlink-button <?php echo A2_Optimized\App\Models\Settings::get_nav_class($data['nav'], 'pls_class') ?>" 
						@click="loadSubPage('page_speed_score')">Page Load Speed Score</button>
					</div>
					<div class="col-md-4 col-lg-3 col-lg-offset-1">
						<button type="button" class="navlink-button <?php echo A2_Optimized\App\Models\Settings::get_nav_class($data['nav'], 'wsp_class') ?>" 
						v-on:click.prevent="loadSubPage('server_performance')">Website &amp; Server Performance</button>
					</div>
					<div class="col-md-4 col-lg-3 col-lg-offset-1">
						<button type="button" class="navlink-button <?php echo A2_Optimized\App\Models\Settings::get_nav_class($data['nav'], 'opt_class') ?>" 
						@click="loadSubPage('optimizations')">Optimization</button>
					</div>
				</div>
			</div>
		</div>
		<?php echo $data['content-element'] ?>
	</div>

</div> <!-- .wrap -->

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
