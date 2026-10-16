<?php
namespace Opencart\Admin\Model\Extension\Mollie\Payment;

class Mollie extends \Opencart\System\Engine\Model {
    
    public function install(): void {
        // Create mollie payments table
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mollie_payments` (
            `order_id` INT(11) NOT NULL,
            `method` VARCHAR(32) NOT NULL,
            `transaction_id` VARCHAR(32),
            `amount` decimal(15,4),
            `bank_account` VARCHAR(15),
            `bank_status` VARCHAR(20),
            `refund_id` VARCHAR(32),
            `mollie_subscription_id` VARCHAR(32),
            `order_subscription_id` INT(11),
            `next_payment` DATETIME,
            `subscription_end` DATETIME,
            `date_modified` DATETIME NOT NULL,
            `payment_attempt` INT(11) NOT NULL,
            PRIMARY KEY (`transaction_id`),
            INDEX `idx_order_id` (`order_id`)
        ) DEFAULT CHARSET=utf8");

        // Create mollie customers table
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mollie_customers` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `mollie_customer_id` VARCHAR(32) NOT NULL,
            `customer_id` INT(11) NOT NULL,
            `email` VARCHAR(96) NOT NULL,
            `date_created` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            INDEX `idx_email` (`email`)
        ) DEFAULT CHARSET=utf8");

        // Create mollie subscription payments table
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mollie_subscription_payments` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `transaction_id` VARCHAR(32),
            `order_subscription_id` INT(11),
            `mollie_subscription_id` VARCHAR(32) NOT NULL,
            `mollie_customer_id` VARCHAR(32) NOT NULL,
            `amount` decimal(15,4) NOT NULL,
            `method` VARCHAR(32) NOT NULL,
            `status` VARCHAR(32) NOT NULL,
            `date_created` DATETIME NOT NULL,
            PRIMARY KEY (`id`)
        ) DEFAULT CHARSET=utf8");

        // Create mollie refund table
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mollie_refund` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `refund_id` VARCHAR(32),
            `order_id` INT(11) NOT NULL,
            `transaction_id` VARCHAR(32),
            `amount` decimal(15,4),
            `currency_code` VARCHAR(32),
            `status` VARCHAR(20),
            `date_created` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            INDEX `idx_order_id` (`order_id`)
        ) DEFAULT CHARSET=utf8");

        // Create mollie payment link table
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mollie_payment_link` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `payment_link_id` VARCHAR(32),
            `order_id` INT(11) NOT NULL,
            `transaction_id` VARCHAR(32),
            `amount` decimal(15,4),
            `currency_code` VARCHAR(32),
            `date_created` DATETIME NOT NULL,
            `date_payment` DATETIME,
            PRIMARY KEY (`id`),
            INDEX `idx_order_id` (`order_id`)
        ) DEFAULT CHARSET=utf8");

        $this->db->query("ALTER TABLE `" . DB_PREFIX . "order` MODIFY `payment_method` VARCHAR(255) NOT NULL;");

        // Add voucher category field
        if (!$this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "product` LIKE 'voucher_category'")->num_rows) {
            $this->db->query("ALTER TABLE `" . DB_PREFIX . "product` ADD `voucher_category` VARCHAR(20) NULL");
        }

        // Add stock mutation field
        if (!$this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "order_product` LIKE 'stock_mutation'")->num_rows) {
            $this->db->query("ALTER TABLE `" . DB_PREFIX . "order_product` ADD `stock_mutation` BOOLEAN NOT NULL DEFAULT TRUE");
        }

        if ($this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "mollie_payments` LIKE 'order_recurring_id'")->num_rows) {
            $this->db->query("ALTER TABLE `" . DB_PREFIX . "mollie_payments` CHANGE `order_recurring_id` `order_subscription_id` INT(11)");
        }

        if ($this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "mollie_payments` LIKE 'subscription_id'")->num_rows) {
            $this->db->query("ALTER TABLE `" . DB_PREFIX . "mollie_payments` CHANGE `subscription_id` `mollie_subscription_id` VARCHAR(32)");
        }

        // Add lookup indexes for existing installations upgrading from a version without
        // them (fresh installs already get these via CREATE TABLE above).
        if (!$this->db->query("SHOW INDEX FROM `" . DB_PREFIX . "mollie_payments` WHERE Key_name = 'idx_order_id'")->num_rows) {
            $this->db->query("ALTER TABLE `" . DB_PREFIX . "mollie_payments` ADD INDEX `idx_order_id` (`order_id`)");
        }

        if (!$this->db->query("SHOW INDEX FROM `" . DB_PREFIX . "mollie_customers` WHERE Key_name = 'idx_email'")->num_rows) {
            $this->db->query("ALTER TABLE `" . DB_PREFIX . "mollie_customers` ADD INDEX `idx_email` (`email`)");
        }

        if (!$this->db->query("SHOW INDEX FROM `" . DB_PREFIX . "mollie_refund` WHERE Key_name = 'idx_order_id'")->num_rows) {
            $this->db->query("ALTER TABLE `" . DB_PREFIX . "mollie_refund` ADD INDEX `idx_order_id` (`order_id`)");
        }

        if (!$this->db->query("SHOW INDEX FROM `" . DB_PREFIX . "mollie_payment_link` WHERE Key_name = 'idx_order_id'")->num_rows) {
            $this->db->query("ALTER TABLE `" . DB_PREFIX . "mollie_payment_link` ADD INDEX `idx_order_id` (`order_id`)");
        }
    }

    public function getMolliePayment(int $order_id): array {
        $_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "mollie_payments` WHERE order_id = '" . (int)$order_id . "' ORDER BY payment_attempt DESC LIMIT 1");

        if ($_query->num_rows) {
            return $_query->row;
        } else {
            return [];
        }
    }

    public function getMolliePaymentLinks(int $order_id): array {
        $_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "mollie_payment_link` WHERE order_id = '" . (int)$order_id . "'");

        if ($_query->num_rows) {
            return $_query->rows;
        } else {
            return [];
        }
    }

    public function getMolliePayments(int $order_id): array {
        $_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "mollie_payments` WHERE order_id = '" . (int)$order_id . "' ORDER BY payment_attempt DESC");

        if ($_query->num_rows) {
            return $_query->rows;
        } else {
            return [];
        }
    }

    public function updateMolliePaymentForPaymentAPI(string $mollie_payment_id, string $refund_id, string $payment_status): void {
        $this->db->query("UPDATE `" . DB_PREFIX . "mollie_payments` SET refund_id = '" . $this->db->escape($refund_id) . "', bank_status = '" . $this->db->escape($payment_status) . "' WHERE transaction_id = '" . $this->db->escape($mollie_payment_id) . "'");
    }

    public function getMollieRefunds(int $order_id): array {
        $_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "mollie_refund` WHERE order_id = '" . (int)$order_id . "'");

        return $_query->rows;
    }

    public function updateMollieRefundStatus(string $refund_id, string $transaction_id, string $status): void {
        $this->db->query("UPDATE `" . DB_PREFIX . "mollie_refund` SET status = '" . $this->db->escape($status) . "' WHERE refund_id = '" . $this->db->escape($refund_id) . "' AND transaction_id = '" . $this->db->escape($transaction_id) . "'");
    }

    public function addMollieRefund(array $data): void {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "mollie_refund` SET refund_id = '" . $this->db->escape($data['refund_id']) . "', order_id = '" . (int)$data['order_id'] . "', transaction_id = '" . $this->db->escape($data['transaction_id']) . "', amount = '" . (float)$data['amount'] . "', currency_code = '" . $this->db->escape($data['currency_code']) . "', status = '" . $this->db->escape($data['status']) . "', date_created = NOW()");
    }

    /**
     * Stop OpenCart from restocking the lines of this order a second time when the order status changes
     * (the refunded products were restocked already, see addHistoryAfter in the catalog controller).
     */
    public function disableStockMutation(int $order_id): void {
        $this->db->query("UPDATE `" . DB_PREFIX . "order_product` SET `stock_mutation` = '0' WHERE `order_id` = '" . (int)$order_id . "'");
    }

    /**
     * Quantity per order_product_id that was refunded already, read from the refund metadata
     * of a payment fetched with the refunds embedded.
     */
    public function getRefundedQuantities(object $molliePayment): array {
        $refundedQuantities = [];

        foreach (($molliePayment->_embedded->refunds ?? []) as $refund) {
            if (isset($refund->status) && in_array($refund->status, ['failed', 'canceled'])) {
                continue;
            }

            $order_products = [];

            if (isset($refund->metadata) && isset($refund->metadata->order_product_id)) {
                $order_products = json_decode($refund->metadata->order_product_id, true);
            }

            if (is_array($order_products)) {
                foreach ($order_products as $order_product) {
                    $id = $order_product['order_product_id'];
                    $refundedQuantities[$id] = ($refundedQuantities[$id] ?? 0) + (int)$order_product['quantity'];
                }
            }
        }

        return $refundedQuantities;
    }

    public function stockMutation(int $order_id, array $data = []): void {
        $this->load->model('sale/order');

        foreach ($data as $stock_mutation_data) {
            $order_product_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_product` WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$stock_mutation_data['order_product_id'] . "'");

            // Safe PHP 8 Check: Only proceed if the product actually exists in the order
            if ($order_product_query->num_rows) {
                $product_id = (int)$order_product_query->row['product_id'];
                $quantity = (int)$stock_mutation_data['quantity'];

                $this->db->query("UPDATE `" . DB_PREFIX . "product` SET quantity = (quantity + " . $quantity . ") WHERE product_id = '" . $product_id . "' AND subtract = '1'");

                // Restock the master product stock level if product is a variant (Opencart 4 support)
                $master_id = (int)($order_product_query->row['master_id'] ?? 0);
                if ($master_id > 0) {
                    $this->db->query("UPDATE `" . DB_PREFIX . "product` SET `quantity` = (`quantity` + " . $quantity . ") WHERE `product_id` = '" . $master_id . "' AND `subtract` = '1'");
                }

                $order_options = $this->model_sale_order->getOptions($order_id, $stock_mutation_data['order_product_id']);

                foreach ($order_options as $order_option) {
                    $this->db->query("UPDATE `" . DB_PREFIX . "product_option_value` SET quantity = (quantity + " . $quantity . ") WHERE product_option_value_id = '" . (int)$order_option['product_option_value_id'] . "' AND subtract = '1'");
                }
            }
        }
    }

    public function getProductVoucherCategory(int $product_id): ?string {
        $query = $this->db->query("SELECT voucher_category FROM `" . DB_PREFIX . "product` WHERE product_id = '" . (int)$product_id . "'");

        // Safe PHP 8 Check: Return null if product or category isn't found
        if ($query->num_rows) {
            return $query->row['voucher_category'] ?? null;
        }
        
        return null;
    }
}