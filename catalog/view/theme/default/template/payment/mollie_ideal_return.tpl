<?php echo $header; ?><?php echo $column_left; ?><?php echo $column_right; ?>
<div id="content">
	<?php echo $content_top ?>

	<div class="breadcrumb">
		<?php foreach ($breadcrumbs as $breadcrumb): ?>
			<?php htmlspecialchars($breadcrumb['separator']); ?><a href="<?php htmlspecialchars($breadcrumb['href']); ?>"><?php htmlspecialchars($breadcrumb['text']); ?></a>
		<?php endforeach; ?>
	</div>

	<h1><?php echo htmlspecialchars($message_title); ?></h1>

	<div class="content">
		<p><?php echo htmlspecialchars($message_text); ?></p>
	</div>

</div>

<?php echo $content_bottom; ?>

<?php echo $footer; ?>
