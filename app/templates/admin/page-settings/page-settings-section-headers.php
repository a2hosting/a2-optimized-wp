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
