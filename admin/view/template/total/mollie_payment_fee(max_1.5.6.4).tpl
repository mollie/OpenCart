<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <?php if ($error_warning) { ?>
  <div class="warning"><?php echo $error_warning; ?></div>
  <?php } ?>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/total.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons"><a onclick="$('#form').submit();" class="button"><?php echo $button_save; ?></a><a href="<?php echo $cancel; ?>" class="button"><?php echo $button_cancel; ?></a></div>
    </div>
    <div class="content">
      <div id="tabs" class="htabs"><a href="#tab-general"><?php echo $tab_general; ?></a><a href="#tab-charge"><?php echo $tab_charge; ?></a></div>
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
        <div id="tab-general">
          <table class="form">
            <tr>
              <td><?php echo $entry_status; ?></td>
              <td><select name="<?php echo $code; ?>_status">
                  <?php if ($mollie_payment_fee_status) { ?>
                  <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                  <option value="0"><?php echo $text_disabled; ?></option>
                  <?php } else { ?>
                  <option value="1"><?php echo $text_enabled; ?></option>
                  <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                  <?php } ?>
                </select></td>
            </tr>
            <tr>
              <td><?php echo $entry_tax_class; ?></td>
              <td>
                <select name="<?php echo $code; ?>_tax_class_id">
                  <option value="0"><?php echo $text_select; ?></option>
                  <?php foreach ($tax_classes as $tax_class) { ?>		
                    <?php if ($tax_class['tax_class_id'] == $mollie_payment_fee_tax_class_id) { ?>
                    <option value="<?php echo $tax_class['tax_class_id']; ?>" selected="selected"><?php echo $tax_class['title']; ?></option>
                    <?php } else { ?>
                    <option value="<?php echo $tax_class['tax_class_id']; ?>"><?php echo $tax_class['title']; ?></option>
                    <?php } ?>
                  <?php } ?>
                </select>
              </td>
            </tr>
            <tr>
              <td><?php echo $entry_sort_order; ?></td>
              <td><input type="text" name="<?php echo $code; ?>_sort_order" value="<?php echo $mollie_payment_fee_sort_order; ?>" size="1" /></td>
            </tr>
          </table>
        </div>
        <div id="tab-charge">
          <table id="charge" class="list">
            <thead>
              <tr>
                <td class="left"><?php echo $entry_title; ?></td>
                <td class="left"><?php echo $entry_payment_method; ?></td>
                <td class="right"><?php echo $entry_cost; ?></td>
                <td class="left"><?php echo $entry_store; ?></td>
                <td class="left"><?php echo $entry_customer_group; ?></td>
                <td class="left"><?php echo $entry_geo_zone; ?></td>
                <td class="right"><?php echo $entry_priority; ?></td>
                <td></td>
              </tr>
            </thead>
            <tbody>
              <?php $charge_row = 0; ?>
              <?php foreach ($mollie_payment_fee_charge as $charge) { ?>
              <tr id="charge-row<?php echo $charge_row; ?>">
                <td class="left">
                  <?php foreach ($languages as $language) { ?>
                  <div class="input-group">
                    <input type="text" name="<?php echo $code; ?>_charge[<?php echo $charge_row; ?>][description][<?php echo $language['language_id']; ?>][title]" value="<?php echo isset($charge['description'][$language['language_id']]) ? $charge['description'][$language['language_id']]['title'] : ''; ?>" placeholder="<?php echo $entry_title; ?>" class="form-control"/> <span class="input-group-addon"><img src="<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /></span>
                  </div>
                  <?php } ?>
                </td>
                <td class="left">
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
                <td class="right"><input type="text" name="<?php echo $code; ?>_charge[<?php echo $charge_row; ?>][cost]" value="<?php echo $charge['cost']; ?>" placeholder="<?php echo $entry_cost; ?>" class="form-control" /></td>
                <td class="left">
                  <div class="scrollbox">
                    <?php $class = 'even'; ?>
                    <?php foreach ($stores as $store) { ?>
                    <?php $class = ($class == 'even' ? 'odd' : 'even'); ?>
                    <div class="<?php echo $class; ?>">
                        <?php if (in_array($store['store_id'], isset($charge['store']) ? $charge['store'] : array())) { ?>
                        <input type="checkbox" name="<?php echo $code; ?>_charge[<?php echo $charge_row; ?>][store][]" value="<?php echo $store['store_id']; ?>" checked="checked" />
                        <?php echo $store['name']; ?>
                        <?php } else { ?>
                        <input type="checkbox" name="<?php echo $code; ?>_charge[<?php echo $charge_row; ?>][store][]" value="<?php echo $store['store_id']; ?>" />
                        <?php echo $store['name']; ?>
                        <?php } ?>
                    </div>
                    <?php } ?>
                  </div>
                </td>
                <td class="left">
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
                <td class="left">
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
                <td class="right"><input type="text" name="<?php echo $code; ?>_charge[<?php echo $charge_row; ?>][priority]" value="<?php echo $charge['priority']; ?>" placeholder="<?php echo $entry_priority; ?>" class="form-control" /></td>
                <td class="left"><a onclick="$('#charge-row<?php echo $charge_row; ?>').remove();" class="button"><?php echo $button_remove_charge; ?></a></td>
              </tr>
              <?php $charge_row++; ?>
              <?php } ?>
            </tbody>
            <tfoot>
              <tr>
                <td colspan="7"></td>
                <td class="left"><a onclick="addCharge();" class="button"><?php echo $button_add_charge; ?></a></td>
              </tr>
            </tfoot>
          </table>
        </div>
      </form>
    </div>
  </div>
</div>
<script type="text/javascript"><!--
$('#tabs a').tabs();
//--></script>
<script type="text/javascript"><!--
var charge_row = <?php echo $charge_row; ?>;

function addCharge() {
	html  = '<tr id="charge-row' + charge_row + '">';
  html += '<td class="left">';
    <?php foreach ($languages as $language) { ?>
      html += '  <div class="input-group">';
      html += '    <input type="text" name="<?php echo $code; ?>_charge[' + charge_row + '][description][<?php echo $language['language_id']; ?>][title]" value="" placeholder="<?php echo $entry_title; ?>" class="form-control"/> <span class="input-group-addon"><img src="<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /></span>';
      html += '  </div>';
    <?php } ?>
    html += '</td>';
    html += '<td class="left">';
    html += '  <select name="<?php echo $code; ?>_charge[' + charge_row + '][payment_method]" class="form-control">';
    html += '<option value=""><?php echo $text_select; ?></option>';
      <?php foreach ($payment_methods as $payment_method) { ?>
        html += '      <option value="<?php echo strtolower($payment_method); ?>"><?php echo ucfirst($payment_method); ?></option>';
      <?php } ?>
      html += '  </select>';
      html += '</td>';
      html += '<td class="right"><input type="text" name="<?php echo $code; ?>_charge[' + charge_row + '][cost]" value="" placeholder="<?php echo $entry_cost; ?>" class="form-control" /></td>';
      html += '<td class="left">';
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
      html += '<td class="left">';
      html += '  <select name="<?php echo $code; ?>_charge[' + charge_row + '][customer_group_id]" class="form-control">';
      <?php foreach ($customer_groups as $customer_group) { ?>
        html += '      <option value="<?php echo $customer_group['customer_group_id']; ?>"><?php echo $customer_group['name']; ?></option>';
      <?php } ?>
      html += '  </select>';
      html += '</td>';
      html += '<td class="left">';
      html += '  <select name="<?php echo $code; ?>_charge[' + charge_row + '][geo_zone_id]" class="form-control">';
      html += '   <option value="0"><?php echo $text_all_zones; ?></option>';
      <?php foreach ($geo_zones as $geo_zone) { ?>
        html += '      <option value="<?php echo $geo_zone['geo_zone_id']; ?>"><?php echo $geo_zone['name']; ?></option>';
      <?php } ?>
      html += '  </select>';
      html += '</td>';
      html += '<td class="right"><input type="text" name="<?php echo $code; ?>_charge[' + charge_row + '][priority]" value="" placeholder="<?php echo $entry_priority; ?>" class="form-control" /></td>';
	html += '  <td class="left"><a onclick="$(\'#charge-row' + charge_row + '\').remove();" class="button"><?php echo $button_remove_charge; ?></a></td>';
	html += '</tr>';

	$('#charge tbody').append(html);

	charge_row++;
}
//--></script>
<?php echo $footer; ?> 