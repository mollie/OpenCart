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
			<h2><?php clean_echo($message_title) ?></h2>
			<br/>

			<p><?php clean_echo($message_text) ?></p>

			<?php if (isset($mollie_error)) { ?>
				<p><code><?php clean_echo($mollie_error) ?></code></p>
			<?php } ?>

			<?php if (isset($button_retry)) { ?>
				<p><a href="<?php clean_echo($checkout_url) ?>" class="button btn btn-primary"><?php clean_echo($button_retry) ?></a></p>
			<?php } ?>
			<?php echo $content_bottom ?>
		</div>
		<?php echo $column_right ?>
	</div>
</div>

<?php echo $footer ?>
