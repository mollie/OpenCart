<?php

return function () {
    $query = $this->db->query("SHOW INDEX FROM `" .DB_PREFIX. "mollie_payments` where Key_name = 'PRIMARY'");
	if($query->num_rows > 0 && $query->row['Column_name'] != 'mollie_order_id') {
		$this->db->query("DELETE FROM `" .DB_PREFIX. "mollie_payments` where mollie_order_id IS NULL OR mollie_order_id = ''");
		$this->db->query("ALTER TABLE `" .DB_PREFIX. "mollie_payments` DROP PRIMARY KEY, ADD PRIMARY KEY (mollie_order_id)");
	}
};