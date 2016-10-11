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
<?php echo $column_left ?>
<?php echo $column_right ?>

<div class="container" id="content">
	<?php echo $content_top ?>

	<div class="breadcrumb">
		<?php foreach ($breadcrumbs as $breadcrumb): ?>
			<?php clean_echo($breadcrumb['separator']) ?><a href="<?php echo $breadcrumb['href'] ?>"><?php echo $breadcrumb['text'] ?></a>
		<?php endforeach ?>
	</div>

	<div class="content">
		<h2><?php clean_echo($message_title) ?></h2>
		<br/>

		<p><?php clean_echo($message_text) ?></p>

		<?php if (isset($mollie_error)) { ?>
			<p><code><?php clean_echo($mollie_error) ?></code></p>
		<?php } ?>

		<?php if (isset($button_retry)) { ?>
			<p><a href="<?php clean_echo($checkout_url) ?>" class="button btn btn-primary"><?php clean_echo($button_retry) ?></a></p>
		<?php } ?>
	</div>
</div>

<?php echo $content_bottom ?>
<?php echo $footer ?>
