function generateCircle(id, radius, width, graph_data) {
	if (!graph_data) { return; }
	let baseColor = palette[graph_data.color_class];

	var circle_graph = Circles.create({
		id: id,
		radius: radius,
		value: graph_data.score,
		maxValue: graph_data.max,
		width: width,
		text: graph_data.text,
		colors: [baseColor + '25', baseColor + '55'],
		duration: 400,
		wrpClass: 'circles-wrp',
		textClass: 'circles-text',
		valueStrokeClass: 'circles-valueStroke',
		maxValueStrokeClass: 'circles-maxValueStroke',
		styleWrapper: true,
		styleText: true
	});

	return circle_graph;
}

const plugin_draw_a2hosting_box = {
	id: 'custom_canvas_background_color_area',
	beforeDraw: (chart) => {
		const { ctx } = chart;
		ctx.save();
		ctx.globalCompositeOperation = 'destination-over';
		ctx.strokeStyle = palette.success + '50';
		ctx.lineWidth = 5;
		let meta = chart.getDatasetMeta(0);
		let data = meta.data[1];

		let top = chart.chartArea.top - 5;
		let height = chart.chartArea.height + 10;
		let left = data.x - (data.width * .5) - 20;
		ctx.strokeRect(left, top, chart.chartArea.width - left + 20, height);
		ctx.font = '20px Verdana';
		/*
		var gradient = ctx.createLinearGradient(0, 0, chart.chartArea.width - left + 20, 0);
		gradient.addColorStop("0", palette.success);
		gradient.addColorStop("1", palette.success);
		ctx.fillStyle = gradient;
		*/
		ctx.fillStyle = palette.success;
		ctx.fillText('A2 Hosting', left + 60, top + 30);
		ctx.restore();
	}
}

function generateSingleBarGraphData(graph, dataPoint) {
	graph_products = ['host', 'a2hosting-turbo', 'a2hosting-mwp'];

	let set_title = graph.legend_text;

	let graph_labels = [];
	let graph_dataset = [];
	let colors = [];
	let bgColors = [];
	let borderColors = [];
	let entryData = [];

	graph_products.forEach((product, index, array) => {
		let data_entry = page_data.graph_data[product];
		graph_labels[index] = data_entry.display_text;


		let value = parseFloat(data_entry[dataPoint]);
		colors[index] = palette[data_entry.color_class];
		bgColors[index] = palette[data_entry.color_class] + '80';
		borderColors[index] = palette[data_entry.color_class] + '50';
		entryData[index] = value;

	});
	graph_dataset[0] = {
		label: dataPoint,
		color: colors,
		backgroundColor: bgColors,
		hoverBackgroundColor: bgColors,
		borderColor: borderColors,
		data: entryData
	}
	return { title: set_title, labels: graph_labels, dataset: graph_dataset, show_legend: false, stack: false };
}

function generateStackedBarGraphData(graph, dataPoints = []) {
	graph_products = ['host', 'a2hosting-turbo', 'a2hosting-mwp'];

	let set_title = graph.legend_text;

	let graph_labels = [];
	let data_labels = [];
	let graph_dataset = [];

	graph_products.forEach((el, index2, array) => {
		let colors = [];
		let bgColors = [];
		let borderColors = [];
		let entryData = [];
		graph_labels[index2] = page_data.graph_data[el].display_text;
		dataPoints.forEach((dataPointName, index, array) => {

			let data_entry = page_data.graph_data[el];

			let value = parseFloat(data_entry[dataPointName]);
			colors[index] = palette[data_entry.color_class];
			bgColors[index] = palette[data_entry.color_class] + '80';
			borderColors[index] = palette[data_entry.color_class] + '50';
			entryData[index] = value;
			data_labels[index] = dataPointName;
		});

		graph_dataset[index2] =
		{
			label: data_labels[index2],
			color: colors,
			backgroundColor: bgColors,
			hoverBackgroundColor: bgColors,
			borderColor: borderColors,
			data: entryData
		}
	});

	return { title: set_title, labels: graph_labels, dataset: graph_dataset, show_legend: true, stack: true };
}

function createBarGraph(canvasId, graph_metadata) {
	const targetCanvas = document.getElementById(canvasId);

	var chart = new Chart(targetCanvas, {
		type: 'bar',
		data: {
			labels: graph_metadata.labels,
			datasets: graph_metadata.dataset,
		},
		plugins: [plugin_draw_a2hosting_box],
		options: {
			tooltips: {
				callbacks: {
					label: function (tooltipItem, data) {
						return Number(tooltipItem.yLabel).toFixed(2);
					}
				}
			},
			scales: {
				x: {
					stacked: graph_metadata.stack,
					ticks: {
						color: (c) => {
							return c.index > 0 ? 'green' : Chart.defaults.color;
						},
					}
				},
				y: {
					stacked: graph_metadata.stack
				}

			},
			responsive: false,
			plugins: {
				title: {
					display: true,
					text: graph_metadata.title
				},
				legend: {
					display: graph_metadata.show_legend,
				},
			}
		},
	});
	return chart;
}

function createLineGraph(elemId, graphData, circleId) {
	if (!graphData) { return; }
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
	svg.appendChild(getLinePath(5, 5, max_length, baseColor + "25"));
	svg.appendChild(getLinePath(5, 5, progress_length, baseColor));

	lineDiv.innerHTML = '';
	lineDiv.appendChild(svg);


	if (circleId) {
		let circleDiv = document.getElementById(circleId);
		let centerPos = rect.width / 2;
		let maxOffset = centerPos - 35;
		let offset = progress_length - centerPos;
		if (offset < -1 * maxOffset) {
			offset = -1 * maxOffset;
		}
		else if (offset > maxOffset) {
			offset = maxOffset;
		}
		let graphDiv = circleDiv.querySelector('.circles-wrp');
		graphDiv.style.left = offset + "px";
	}
}

function getLinePath(x, y, length, color) {
	var path = document.createElementNS("http://www.w3.org/2000/svg", "path");
	path.setAttribute("fill", "transparent");
	path.setAttribute("stroke", color);
	path.setAttribute("stroke-width", "10");
	path.setAttribute("stroke-linecap", "round");
	path.setAttribute("stroke-linejoin", "round");
	path.setAttribute("class", "line-graph-style");
	path.setAttribute("d", "m " + x + "," + y + " h " + length + " Z");

	return path;

}

// thanks stackoverflow! https://stackoverflow.com/questions/1740700/how-to-get-hex-color-value-rather-than-rgb-value
function rgb2hex(rgb) {
	var match = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/)
	return '#' + match.slice(1).map(n => parseInt(n, 10).toString(16).padStart(2, '0')).join('');
}

let palette = [];
document.addEventListener("DOMContentLoaded", function () {
	var palette_items = document.querySelectorAll("#color-palette span");
	palette_items.forEach(function (el, index, array) {
		var style = getComputedStyle(el);
		var element_color = style.color;
		var element_class = el.className;
		var hex = rgb2hex(element_color);
		palette[element_class] = hex;
	});

});

Vue.component('info-button', {
	props: { metric: { type: String } },
	template: "#info-button-template",
	methods: {
		toggleInfoDiv: function (metric, elem) {
			this.$root.toggleInfoDiv(metric);
		}
	}
});

Vue.component('graph-legend', {
	props: { metric: { type: String } },
	data() {
		return page_data.graphs[this.metric]
	},
	template: "#graph-legend-template"
});

Vue.component('hosting-matchup', {
	data() {
		return page_data
	},
	template: "#hosting-matchup-template",
	mounted() {
		document.addEventListener("DOMContentLoaded", function () {
			let webperf_meta = generateSingleBarGraphData(page_data.graphs['webperformance'], 'wordpress_db');
			let serverperf_meta = generateStackedBarGraphData(page_data.graphs['serverperformance'], ['php', 'mysql', 'filesystem']);

			createBarGraph('overall_wordpress_canvas', webperf_meta);
			createBarGraph('server_perf_canvas', serverperf_meta, true, true);
		});
	}
});

Vue.component("modal", {
	template: "#modal-template"
});

Vue.component('server-performance', {
	data() {
		return page_data
	},
	methods: {
		strategyChanged: function (evt) {
			this.$root.pageSpeedCheck('server-performance', false);

		},
		pageSpeedCheck: function () {
			this.$root.pageSpeedCheck('server-performance');
		},
		rec_toggle: function(id) {
			var desc = document.getElementById('rec_item_desc_' + id);
			var toggle = document.getElementById('rec_item_toggle_' + id);
			
			if(desc.style.display === 'none'){
				desc.style.display = 'block';
				toggle.classList.remove('glyphicon-chevron-right');
				toggle.classList.add('glyphicon-chevron-down');
			} else {
				desc.style.display = 'none';
				toggle.classList.add('glyphicon-chevron-right');
				toggle.classList.remove('glyphicon-chevron-down');
			}
			
		},
		drawGraphs: function () {
			let perf = page_data.graphs;
			var circles_ttfb = generateCircle('circles-ttfb', 40, 10, perf.ttfb);
			var circles_cls = generateCircle('circles-cls', 40, 10, perf.cls);
			var circles_overall = generateCircle('circles-overall_score', 60, 20, perf.overall_score);

			//var offset_circles = jQuery('#circles-ttfb, #circles-cls, #circles-overall_score').find('.circles-text');
			//offset_circles.css({ 'left': '-32px', 'top': '-10px', 'font-size': '36px' });
			//var overall_circle = jQuery('#circles-overall_score').find('.circles-text');
			//overall_circle.css({ 'left': '-32px', 'top': '-10px', 'font-size': '60px' });

			var circles_lcp = generateCircle('circles-lcp', 35, 7, perf.lcp);
			var circles_fid = generateCircle('circles-fid', 35, 7, perf.fid);
			var circles_fcp = generateCircle('circles-fcp', 35, 7, perf.fcp);

			var line_graph_lcp = createLineGraph('line-graph-lcp', perf.lcp, 'circles-lcp');
			var line_graph_fid = createLineGraph('line-graph-fid', perf.fid, 'circles-fid');
			var line_graph_fcp = createLineGraph('line-graph-fcp', perf.fcp, 'circles-fcp');
		}
	},
	template: "#server-performance-template",
	props: ['updateChild'],
	watch: {
		updateChild: function () {
			this.drawGraphs();
		}
	},
	mounted() {
		let that = this;
		document.addEventListener("DOMContentLoaded", function () {
			that.drawGraphs();
		});
	}
});

Vue.component('page-speed-score', {
	data() {
		page_data.info_toggle_data = {
			'pagespeed': false,
			'opt': false
		};
		return page_data
	},
	methods: {
		pageSpeedCheck: function () {
			this.$root.pageSpeedCheck('page-speed-score');
		},
		doCircles: function () {
			let graphs = page_data.graphs;
			var plsMobile = generateCircle('circles-pls-mobile', 40, 10, graphs.pagespeed_mobile.overall_score);
			var plsDesktop = generateCircle('circles-pls-desktop', 40, 10, graphs.pagespeed_desktop.overall_score);
			//jQuery('#circles-pls-mobile .circles-text, #circles-pls-desktop .circles-text').css({ 'left': '-32px', 'top': '-10px', 'font-size': '50px' });

			var optPerf = generateCircle('circles-opt-perf', 35, 7, graphs.opt_perf);
			var optSec = generateCircle('circles-opt-sec', 35, 7, graphs.opt_security);
			var optBP = generateCircle('circles-opt-bp', 35, 7, graphs.opt_bp);
		}
	},
	template: "#page-speed-score-template",
	mounted() {
		let that = this;

		document.addEventListener("DOMContentLoaded", function () {
			that.doCircles();
		});
	},
	props: ['updateChild'],
	watch: {
		updateChild: function () {
			this.doCircles();
		}
	}
});

var app = new Vue({
	el: '#a2-optimized-wrapper',
	data: page_data,
	methods: {
		toggleInfoDiv: function (metric) {
			let data_div = document.querySelector('#graph-' + metric + ' .graph_data');
			let explanation_div = document.querySelector('#graph-' + metric + ' .graph_info');

			if (data_div.style.display == 'none') {
				data_div.style.display = '';
				explanation_div.style.display = 'none';
			}
			else {
				data_div.style.display = 'none';
				explanation_div.style.display = '';
			}
		},
		pageSpeedCheck: function (page, run_checks = true) {
			page_data.showModal = true;
			var params = new URLSearchParams();
			params.append('action', 'run_benchmarks');
			params.append('a2_page', page);
			params.append('run_checks', run_checks);
			let strat = document.getElementById('server-perf-strategy-select');
			if (strat) {
				params.append('a2_performance_strategy', strat.value);
			}

			axios
				.post(ajaxurl, params)
				.catch((error) => {
					alert('There was a problem getting benchmark data. See console log.');
					console.log(error.message);
					page_data.showModal = false;
				})
				.then((response) => {
					//console.log('post finished, got data');
					//console.log(response.data);
					if (run_checks) {
						page_data.last_check_date = 'just now';
					}
					else {
						page_data.last_check_date = response.data.overall_score.last_check_date;
					}
					page_data.graphs = response.data;
					page_data.updateView++;
					page_data.showModal = false;
				});
		}
	},
});


