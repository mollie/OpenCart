<?php echo $header; ?>

<div id="container" class="container">
  <ul class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
      <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
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

      <?php if (isset($button_retry)) { ?>
        <p><a href="<?php echo $checkout_url; ?>" class="button btn btn-primary"><?php echo $button_retry; ?></a></p>
      <?php } ?>

      <?php if ($show_report_button) { ?>
        <p><button id="button-report-error" class="button btn btn-danger"><?php echo $button_report; ?></button></p>
        <form id="error-form" class="form-horizontal" style="display: none;">
          <hr>
          <div class="form-group">
            <div class="col-sm-12">
              <textarea name="enquiry" id="mollie-error" class="form-control" rows="10"><?php echo  isset($mollie_error) ? $mollie_error : ''; ?></textarea>
            </div>
          </div>
          <button type="button" id="report-error" class="btn btn-primary pull-right"><?php echo $button_submit; ?></button>
        </form>
      <?php } ?>          

      <?php echo $content_bottom; ?>
    </div>
    <?php echo $column_right; ?>
  </div>
</div>
<?php if ($show_report_button) { ?>
<script> 
$(document).ready(function(){
  $("#button-report-error").click(function(){
    $("#error-form").slideDown("slow");
  });

  $("#report-error").click(function(){
    var error = $('#mollie-error').val();
    if (error == '') {
      return;
    }

    $.ajax({
      type: "POST",
      url: 'index.php?route=payment/mollie/base/reportError',
      data: "mollie_error=" + error,
      beforeSend: function() {
        $('#report-error').button('loading');
      },
      complete: function() {
        $('#report-error').button('reset');
      },
      success: function(json) {   
        if (json['success']) {
          $('.alert-success').remove();
          $('#content').prepend('<div class="alert alert-success alert-dismissible"><i class="fa fa-check-circle"></i> ' + json['success'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
          $('#mollie-error').val('');
          window.scrollTo({ top: 0, behavior: 'smooth' });
        }  
      }
    });
  });
});
</script>
<?php } ?> 

<?php echo $footer; ?>