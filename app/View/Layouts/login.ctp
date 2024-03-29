<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="utf-8">
	<title>
		<?php echo __('もりけん｜実験システム'); ?>
	</title>
	<?php echo $this->Html->meta ('favicon.ico','./../../webroot/favicon.ico', array ('type' => 'icon')); ?>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="">
	<meta name="author" content="">

	<!-- Le styles -->
	<?php echo $this->Html->css('bootstrap'); ?>
	<?php echo $this->Html->css('mycss'); ?>
<style>
	body {
		padding-top: 60px;  /*60px to make the container go all the way to the bottom of the topbar */
	}
	</style>
	<?php
        echo $this->fetch('meta');
        echo $this->fetch('css');
	?>
</head>

<body>
	<div class="container">
			<br />
			<br />
			<?php echo $this->Session->flash(); ?>
			<?php echo $this->fetch('content'); ?>
	</div>
    
	<!-- Le javascript
    ================================================== -->
	<!-- Placed at the end of the document so the pages load faster -->
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
	<?php echo $this->Html->script('bootstrap.min'); ?>
	<?php echo $this->fetch('script'); ?>

</body>
</html>
