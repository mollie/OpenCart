<?php echo $header; ?><?php echo $column_left; ?><?php echo $column_right; ?>
<?php
function clean_echo ($string)
{
	echo htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?>
<div id="content"><?php echo $content_top; ?>

	<div class="breadcrumb">
		<?php foreach ($breadcrumbs as $breadcrumb): ?>
			<?php clean_echo($breadcrumb['separator']); ?><a href="<?php clean_echo($breadcrumb['href']); ?>"><?php clean_echo($breadcrumb['text']); ?></a>
		<?php endforeach; ?>
	</div>

	<h1><?php clean_echo($heading_title); ?></h1>
	<div class="checkout">
		<div class="checkout-heading"><?php clean_echo($msg_failed); ?></div>
	</div>

	<?php echo $content_bottom; ?>
</div>

<?php echo $footer; ?>