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

<div id="content">
	<?php echo $content_top ?>

	<div class="breadcrumb">
		<?php foreach ($breadcrumbs as $breadcrumb): ?>
			<?php clean_echo($breadcrumb['separator']) ?><a href="<?php clean_echo($breadcrumb['href']) ?>"><?php clean_echo($breadcrumb['text']) ?></a>
		<?php endforeach ?>
	</div>

	<h1><?php clean_echo($message->get("heading_error")) ?></h1>

	<div class="content">

		<p><?php clean_echo($message->get("text_error")) ?></p>

		<p><code><?php clean_echo($mollie_error) ?></code></p>
	</div>
</div>

<?php echo $content_bottom ?>
<?php echo $footer ?>
