<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-mollie-payment-fee" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
      </div>
      <div class="panel-body">
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-mollie-payment-fee" class="form-horizontal">
          <ul class="nav nav-tabs">
            <li class="active"><a href="#tab-general" data-toggle="tab"><?php echo $tab_general; ?></a></li>
            <li><a href="#tab-charge" data-toggle="tab"><?php echo $tab_charge; ?></a></li>
          </ul>
          <div class="tab-content">
            <div class="tab-pane active" id="tab-general">
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status; ?></label>
                <div class="col-sm-10">
                  <select name="<?php echo $code; ?>_status" id="input-status" class="form-control">
                    <?php if ($mollie_payment_fee_status) { ?>
                    <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                    <option value="0"><?php echo $text_disabled; ?></option>
                    <?php } else { ?>
                    <option value="1"><?php echo $text_enabled; ?></option>
                    <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-tax-class"><?php echo $entry_tax_class; ?></label>
                <div class="col-sm-10">
                  <select name="<?php echo $code; ?>_tax_class_id" id="input-tax-class" class="form-control">
                    <option value="0"><?php echo $text_select; ?></option>
                    <?php foreach ($tax_classes as $tax_class) { ?>		
                      <?php if ($tax_class['tax_class_id'] == $mollie_payment_fee_tax_class_id) { ?>
                      <option value="<?php echo $tax_class['tax_class_id']; ?>" selected="selected"><?php echo $tax_class['title']; ?></option>
                      <?php } else { ?>
                      <option value="<?php echo $tax_class['tax_class_id']; ?>"><?php echo $tax_class['title']; ?></option>
                      <?php } ?>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-sort-order"><?php echo $entry_sort_order; ?></label>
                <div class="col-sm-10">
                  <input type="text" name="<?php echo $code; ?>_sort_order" for="input-sort-order" value="<?php echo $mollie_payment_fee_sort_order; ?>" class="form-control" placeholder="<?php echo $entry_sort_order; ?>">
                </div>
              </div>
            </div>
            <div class="tab-pane" id="tab-charge">
              <div class="table-responsive">
                <table id="charge" class="table table-striped table-bordered table-hover">
                  <thead>
                    <tr>
                      <td class="text-left"><?php echo $entry_title; ?></td>
                      <td class="text-left"><?php echo $entry_payment_method; ?></td>
                      <td class="text-right"><?php echo $entry_cost; ?></td>
                      <td class="text-left"><?php echo $entry_store; ?></td>
                      <td class="text-left"><?php echo $entry_customer_group; ?></td>
                      <td class="text-left"><?php echo $entry_geo_zone; ?></td>
                      <td class="text-right"><?php echo $entry_priority; ?></td>
                      <td></td>
                    </tr>
                  </thead>
                  <tbody>
                    <?php $charge_row = 0; ?>
                    <?php foreach ($mollie_payment_fee_charge as $charge) { ?>
                    <tr id="charge-row<?php echo $charge_row; ?>">
                      <td class="text-left">
                        <?php foreach ($languages as $language) { ?>
                        <div class="input-group"><span class="input-group-addon"><img src="<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /></span>
                          <input type="text" name="<?php echo $code; ?>_charge[<?php echo $charge_row; ?>][description][<?php echo $language['language_id']; ?>][title]" value="<?php echo isset($charge['description'][$language['language_id']]) ? $charge['description'][$language['language_id']]['title'] : ''; ?>" placeholder="<?php echo $entry_title; ?>" class="form-control"/>
                        </div>
                        <?php } ?>
                      </td>
                      <td class="text-left">
                        <select name="<?php echo $code; ?>_charge[<?php echo $charge_row; ?>][payment_method]" class="form-control">
                          <option value=""><?php echo $text_select; ?></option>
                          <?php foreach ($payment_methods as $payment_method) { ?>
                            <?php if (strtolower($payment_method) == strtolower($charge['payment_method'])) { ?>
                            <option value="<?php echo strtolower($payment_method); ?>" selected="selected"><?php echo ucfirst($payment_method); ?></option>
                            <?php } else { ?>
                            <option value="<?php echo strtolower($payment_method); ?>"><?php echo ucfirst($payment_method); ?></option>
                            <?php } ?>
                          <?php } ?>
                        </select>
                      </td>
                      <td class="text-right"><input type="text" name="<?php echo $code; ?>_charge[<?php echo $charge_row; ?>][cost]" value="<?php echo $charge['cost']; ?>" placeholder="<?php echo $entry_cost; ?>" class="form-control" /></td>
                      <td class="text-left">
                        <div class="well well-sm" style="height: 125px; overflow: auto;">
                          <?php foreach ($stores as $store) { ?>
                          <div class="checkbox">
                            <label>
                              <?php if (in_array($store['store_id'], isset($charge['store']) ? $charge['store'] : array())) { ?>
                              <input type="checkbox" name="<?php echo $code; ?>_charge[<?php echo $charge_row; ?>][store][]" value="<?php echo $store['store_id']; ?>" checked="checked" />
                              <?php echo $store['name']; ?>
                              <?php } else { ?>
                              <input type="checkbox" name="<?php echo $code; ?>_charge[<?php echo $charge_row; ?>][store][]" value="<?php echo $store['store_id']; ?>" />
                              <?php echo $store['name']; ?>
                              <?php } ?>
                            </label>
                          </div>
                          <?php } ?>
                        </div>
                      </td>
                      <td class="text-left">
                        <select name="<?php echo $code; ?>_charge[<?php echo $charge_row; ?>][customer_group_id]" class="form-control">
                          <?php foreach ($customer_groups as $customer_group) { ?>
                            <?php if ($customer_group['customer_group_id'] == $charge['customer_group_id']) { ?>
                            <option value="<?php echo $customer_group['customer_group_id']; ?>" selected="selected"><?php echo $customer_group['name']; ?></option>
                            <?php } else { ?>
                            <option value="<?php echo $customer_group['customer_group_id']; ?>"><?php echo $customer_group['name']; ?></option>
                            <?php } ?>
                          <?php } ?>
                        </select>
                      </td>
                      <td class="text-left">
                        <select name="<?php echo $code; ?>_charge[<?php echo $charge_row; ?>][geo_zone_id]" class="form-control">
                          <option value="0"><?php echo $text_all_zones; ?></option>
                          <?php foreach ($geo_zones as $geo_zone) { ?>
                            <?php if ($geo_zone['geo_zone_id'] == $charge['geo_zone_id']) { ?>
                            <option value="<?php echo $geo_zone['geo_zone_id']; ?>" selected="selected"><?php echo $geo_zone['name']; ?></option>
                            <?php } else { ?>
                            <option value="<?php echo $geo_zone['geo_zone_id']; ?>"><?php echo $geo_zone['name']; ?></option>
                            <?php } ?>
                          <?php } ?>
                        </select>
                      </td>
                      <td class="text-right"><input type="text" name="<?php echo $code; ?>_charge[<?php echo $charge_row; ?>][priority]" value="<?php echo $charge['priority']; ?>" placeholder="<?php echo $entry_priority; ?>" class="form-control" /></td>
                      <td class="text-left"><button type="button" onclick="$('#charge-row<?php echo $charge_row; ?>').remove();" data-toggle="tooltip" title="<?php echo $button_remove_charge; ?>" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>
                    </tr>
                    <?php $charge_row++; ?>
                    <?php } ?>
                  </tbody>
                  <tfoot>
                    <tr>
                      <td colspan="7"></td>
                      <td class="text-left"><button type="button" onclick="addCharge();" data-toggle="tooltip" title="<?php echo $button_add_charge; ?>" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button></td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript"><!--
var charge_row = <?php echo $charge_row; ?>;

function addCharge() {
	html  = '<tr id="charge-row' + charge_row + '">';
  html += '<td class="text-left">';
    <?php foreach ($languages as $language) { ?>
      html += '  <div class="input-group"><span class="input-group-addon"><img src="<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /></span>';
      html += '    <input type="text" name="<?php echo $code; ?>_charge[' + charge_row + '][description][<?php echo $language['language_id']; ?>][title]" value="" placeholder="<?php echo $entry_title; ?>" class="form-control"/>';
      html += '  </div>';
    <?php } ?>
    html += '</td>';
    html += '<td class="text-left">';
    html += '  <select name="<?php echo $code; ?>_charge[' + charge_row + '][payment_method]" class="form-control">';
    html += '<option value=""><?php echo $text_select; ?></option>';
      <?php foreach ($payment_methods as $payment_method) { ?>
        html += '      <option value="<?php echo strtolower($payment_method); ?>"><?php echo ucfirst($payment_method); ?></option>';
      <?php } ?>
      html += '  </select>';
      html += '</td>';
      html += '<td class="text-right"><input type="text" name="<?php echo $code; ?>_charge[' + charge_row + '][cost]" value="" placeholder="<?php echo $entry_cost; ?>" class="form-control" /></td>';
      html += '<td class="text-left">';
      html += '<div class="well well-sm" style="height: 125px; overflow: auto;">';
        <?php foreach ($stores as $store) { ?>
          html += '  <div class="checkbox">';
          html += '    <label>';
          html += '      <input type="checkbox" name="<?php echo $code; ?>_charge[' + charge_row + '][store][]" value="<?php echo $store['store_id']; ?>" /> <?php echo $store['name']; ?>';
          html += '    </label>';
          html += '  </div>';
        <?php } ?>
        html += '</div>';
      html += '</td>';
      html += '<td class="text-left">';
      html += '  <select name="<?php echo $code; ?>_charge[' + charge_row + '][customer_group_id]" class="form-control">';
      <?php foreach ($customer_groups as $customer_group) { ?>
        html += '      <option value="<?php echo $customer_group['customer_group_id']; ?>"><?php echo $customer_group['name']; ?></option>';
      <?php } ?>
      html += '  </select>';
      html += '</td>';
      html += '<td class="text-left">';
      html += '  <select name="<?php echo $code; ?>_charge[' + charge_row + '][geo_zone_id]" class="form-control">';
      html += '   <option value="0"><?php echo $text_all_zones; ?></option>';
      <?php foreach ($geo_zones as $geo_zone) { ?>
        html += '      <option value="<?php echo $geo_zone['geo_zone_id']; ?>"><?php echo $geo_zone['name']; ?></option>';
      <?php } ?>
      html += '  </select>';
      html += '</td>';
      html += '<td class="text-right"><input type="text" name="<?php echo $code; ?>_charge[' + charge_row + '][priority]" value="" placeholder="<?php echo $entry_priority; ?>" class="form-control" /></td>';
	html += '  <td class="text-left"><button type="button" onclick="$(\'#charge-row' + charge_row + '\').remove();" data-toggle="tooltip" title="<?php echo $button_remove_charge; ?>" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>';
	html += '</tr>';

	$('#charge tbody').append(html);

	charge_row++;
}
//--></script>
<?php echo $footer; ?> 