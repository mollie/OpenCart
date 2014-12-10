<?php
	if (!function_exists('clean_echo'))
	{
		function clean_echo ($string)
		{
			echo htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
		}
	}
?>

<?php echo $header ?>

<div class="container">

	<ul class="breadcrumb">
		<?php foreach ($breadcrumbs as $breadcrumb): ?>
		<li><a href="<?php echo $breadcrumb['href'] ?>"><?php echo $breadcrumb['text'] ?></a></li>
		<?php endforeach ?>
	</ul>

	<div class="row">
		<?php echo $column_left ?>
		<div id="content" class="col-sm-<?php echo (12 - ($column_left ? 3 : 0) - ($column_right ? 3 : 0)) ?>">
			<?php echo $content_top ?>
			<h2><?php clean_echo($message->get("heading_error")) ?></h2>
			<br/>

			<p><?php clean_echo($message->get("text_error")) ?></p>

			<p><code><?php clean_echo($mollie_error) ?></code></p>
			<?php echo $content_bottom ?>
		</div>
		<?php echo $column_right ?>
	</div>
</div>

<?php echo $footer ?>
