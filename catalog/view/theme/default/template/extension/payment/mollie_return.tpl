<?php echo $header; ?>

<div id="container" class="container">
  <ul class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
      <li><a href="<?php $breadcrumb['href']; ?>"><?php $breadcrumb['text']; ?></a></li>
    <?php } ?>
  </ul>

  <div class="row">
    <?php echo isset($column_left) ? $column_left : ''; ?>
    <?php $subtract_first = !empty($column_left) ? 3 : 0; ?>
    <?php $subtract_second = $column_right ? 3 : 0; ?>
    <?php $content_width = 12 - $subtract_first - $subtract_second; ?>
    <div id="content" class="col-sm-<?php echo $content_width; ?>">
      <?php echo $content_top; ?>
      <h2><?php echo $message_title; ?></h2>
      <br/>
      <p><?php echo $message_text; ?></p>
      <?php if (isset($mollie_error)) { ?>
        <p><code><?php echo $mollie_error; ?></code></p>
      <?php } ?>

      <?php if (isset($button_retry)) { ?>
        <p><a href="<?php echo $checkout_url; ?>" class="button btn btn-primary"><?php echo $button_retry; ?></a></p>
      <?php } ?>
      <?php echo $content_bottom; ?>
    </div>
    <?php echo $column_right; ?>
  </div>
</div>

<?php echo $footer; ?>