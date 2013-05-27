<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Mensa Scolastica</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="">
	<meta name="author" content="">

	<!-- Le styles -->
	<link href="<?php echo base_url(); ?>assets/bootstrap/css/bootstrap.css" rel="stylesheet">
	<style type="text/css">
		body {
			padding-top: 60px;
			padding-bottom: 40px;
		}

		.admin-container {
			position: relative;
			margin: 15px 0;
			padding: 15px 15px 10px;
			background-color: #fff;
			border: 1px solid #ddd;
			-webkit-border-radius: 4px;
			-moz-border-radius: 4px;
			border-radius: 4px;
		}

		.admin-container-header {
			position: relative;
			top: -16px;
			left: -16px;
			padding: 3px 7px;
			font-size: 12px;
			font-weight: bold;
			background-color: #f5f5f5;
			border: 1px solid #ddd;
			color: #9da0a4;
			-webkit-border-radius: 4px 0 4px 0;
			-moz-border-radius: 4px 0 4px 0;
			border-radius: 4px 0 4px 0;
		}
	</style>
	<link href="<?php echo base_url(); ?>assets/bootstrap/css/bootstrap-responsive.css" rel="stylesheet">

	<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]>
	<script src="<?php echo base_url() ?>assets/html5shiv.js"></script>
	<![endif]-->

</head>

<body>
	<div class="navbar navbar-fixed-top">
		<div class="navbar-inner">
			<div class="container">
				<a class="brand" href="<?php echo base_url() ?>">Mensa Scolastica</a>
				<a class="brand pull-right" href="<?php echo base_url() ?>">2013-04-22</a>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="span3">
			<div class="container-fluid">
				<div class="well">
					<ul class="nav nav-list">
						<li class="nav-header">Menu</li>
						<?php
							$nav = array(
								'home' => 'Home',
								'generale' => 'Generale',
								'menu_del_giorno' => 'Menu del giorno',
								'clienti_in_negativo' => 'Clienti in negativo',
								'clienti_per_fornitore' => 'Clienti per fornitore'
							);

							foreach ($nav as $k => $n)
							{
								echo $current == $k ? '<li class="active">' : '<li>';
								echo '<a href="'.($k == 'home' ? base_url() : site_url($k)).'">'.$n.'</a>';
								echo '</li>';
							}
						?>
					</ul>
				</div>
			</div>
		</div>

		<div class="span10">
			<ul class="breadcrumb">
				<?php echo $controller_title ?>
				<span class="divider">/</span>
				<?php echo $method_title ?>
			</ul>

			<?php if (isset($alert)) : ?>
				<div class="alert"><?php echo htmlentities($alert) ?></div>
			<?php endif; ?>

			<div class="admin-container">
				<?php echo $body; ?>
			</div>
		</div>
	</div>

	<script src="http://code.jquery.com/jquery.js"></script>
	<script src="<?php echo base_url(); ?>assets/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>