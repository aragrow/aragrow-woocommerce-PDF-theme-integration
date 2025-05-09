<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<?php 

$order_id = $_GET['order_ids'];
$manual_payment_data = get_manual_payment_data($order_id);
function get_manual_payment_data($order_id) {
    // Get the WooCommerce order object
    $order = wc_get_order($order_id);

    // Retrieve stored payment meta values
    return [
        'payment_date'   => $order->get_meta('_payment_date'),
        'payment_amount' => $order->get_meta('_payment_amount'),
        'payment_type'   => $order->get_meta('_payment_type')
    ];
}

?>

<?php do_action( 'wpo_wcpdf_before_document', $this->get_type(), $this->order ); ?>

<table class="head container">
	<tr>
		<td class="header">
		<?php
			if ( $this->has_header_logo() ) {
				do_action( 'wpo_wcpdf_before_shop_logo', $this->get_type(), $this->order );
				$this->header_logo();
				do_action( 'wpo_wcpdf_after_shop_logo', $this->get_type(), $this->order );
			} else {
				$this->title();
			}
		?>
		</td>
		<td class="shop-info">
			<?php do_action( 'wpo_wcpdf_before_shop_name', $this->get_type(), $this->order ); ?>
			<div class="shop-name"><h3><?php $this->shop_name(); ?></h3></div>
			<?php do_action( 'wpo_wcpdf_after_shop_name', $this->get_type(), $this->order ); ?>
			<?php do_action( 'wpo_wcpdf_before_shop_address', $this->get_type(), $this->order ); ?>
			<div class="shop-address"><?php $this->shop_address(); ?></div>
			<?php do_action( 'wpo_wcpdf_after_shop_address', $this->get_type(), $this->order ); ?>
		</td>
	</tr>
</table>

<?php do_action( 'wpo_wcpdf_before_document_label', $this->get_type(), $this->order ); ?>

<?php if ( $this->has_header_logo() ) : ?>
	<h1 class="document-type-label"><?php $this->title(); ?></h1>
<?php endif; ?>

<?php do_action( 'wpo_wcpdf_after_document_label', $this->get_type(), $this->order ); ?>

<table class="order-data-addresses">
	<tr>
		<td class="address billing-address">
			<?php do_action( 'wpo_wcpdf_before_billing_address', $this->get_type(), $this->order ); ?>
			<p><?php $this->billing_address(); ?></p>
			<?php do_action( 'wpo_wcpdf_after_billing_address', $this->get_type(), $this->order ); ?>
			<?php if ( isset( $this->settings['display_email'] ) ) : ?>
				<div class="billing-email"><?php $this->billing_email(); ?></div>
			<?php endif; ?>
			<?php if ( isset( $this->settings['display_phone'] ) ) : ?>
				<div class="billing-phone"><?php $this->billing_phone(); ?></div>
			<?php endif; ?>
		</td>
		<td class="address shipping-address">
			<?php if ( $this->show_shipping_address() ) : ?>
				<h3><?php $this->shipping_address_title(); ?></h3>
				<?php do_action( 'wpo_wcpdf_before_shipping_address', $this->get_type(), $this->order ); ?>
				<p><?php $this->shipping_address(); ?></p>
				<?php do_action( 'wpo_wcpdf_after_shipping_address', $this->get_type(), $this->order ); ?>
				<?php if ( isset( $this->settings['display_phone'] ) ) : ?>
					<div class="shipping-phone"><?php $this->shipping_phone(); ?></div>
				<?php endif; ?>
			<?php endif; ?>
		</td>
		<td class="order-data">
			<table>
				<?php do_action( 'wpo_wcpdf_before_order_data', $this->get_type(), $this->order ); ?>
				<tr>
					<td><strong><?php _e('Invoice Status:', 'woocommerce-pdf-invoices'); ?></strong></td>
					<td><?php echo esc_html($this->order->status); ?></td>
				</tr>
				<?php if ( isset( $this->settings['display_number'] ) ) : ?>
					<tr class="invoice-number">
						<th><?php $this->number_title(); ?></th>
						<td><?php $this->number( $this->get_type() ); ?></td>
					</tr>
				<?php endif; ?>
				<?php if ( isset( $this->settings['display_date'] ) ) : ?>
					<tr class="invoice-date">
						<th><?php $this->date_title(); ?></th>
						<td><?php $this->date( $this->get_type() ); ?></td>
					</tr>
				<?php endif; ?>
				<?php if ( $this->get_payment_method() ) : ?>
					<tr class="payment-method">
						<th><?php $this->payment_method_title(); ?></th>
						<td><?php $this->payment_method(); ?></td>
					</tr>
				<?php endif; ?>
				<?php do_action( 'wpo_wcpdf_after_order_data', $this->get_type(), $this->order ); ?>
			</table>
		</td>
	</tr>
</table>

<?php do_action( 'wpo_wcpdf_before_order_details', $this->get_type(), $this->order ); ?>

<table class="order-details">
	<?php 
		$is_time_invoice = get_post_meta($order_id, '_timekeeping_invoice');
		if($is_time_invoice) 
			$headers = apply_filters('wpo_wcpdf_get_aragrow_time_invoice_template_table_headers', [], $this); 
		else
			$headers = apply_filters('wpo_wcpdf_get_aragrow_invoice_template_table_headers', [], $this); 
	?>
	<thead>
		<tr>
			<?php
				
				foreach ( $headers as $column_class => $column_title ) {
					printf( '<th class="%s">%s</th>', esc_attr( $column_class ), esc_html( $column_title ) );
				}
			?>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $this->get_order_items() as $item_id => $item ) : ?>
			<?php
				// Remove all non-numeric characters except for a single decimal point
				$order_price = preg_replace('/[^0-9.]/', '', $item['order_price']);
			
				// Ensure only one decimal point is allowed
				$order_price = preg_replace('/\.(?=.*\.)/', '', $order_price);
		
				// Convert to a float
				$order_price = floatval($order_price);
		
				// Sanitize quantity and calculate total
				$quantity = intval($item['quantity']);
				$total_line = $quantity * $order_price;
			?>
			<tr class="<?php echo esc_html( $item['row_class'] ); ?>">
				<?php error_log(print_r($item,true)); ?> 
				<td class="product">
					<p class="item-name"><?php echo esc_html( $item['name'] ); ?></p>
					<?php do_action( 'wpo_wcpdf_before_item_meta', $this->get_type(), $item, $this->order ); ?>
					<div class="item-meta">
						<?php if ( ! empty( $item['sku'] ) ) : ?>
							<p class="sku"><span class="label"><?php $this->sku_title(); ?></span> <?php echo esc_attr( $item['sku'] ); ?></p>
						<?php endif; ?>
						<?php if ( ! empty( $item['weight'] ) ) : ?>
							<p class="weight"><span class="label"><?php $this->weight_title(); ?></span> <?php echo esc_attr( $item['weight'] ); ?><?php echo esc_attr( get_option( 'woocommerce_weight_unit' ) ); ?></p>
						<?php endif; ?>
						<!-- ul.wc-item-meta -->
						<?php if ( ! empty( $item['meta'] ) ) : ?>
							<?php echo wp_kses_post( $item['meta'] ); ?>
						<?php endif; ?>
						<!-- / ul.wc-item-meta -->
					</div>
					<?php do_action( 'wpo_wcpdf_after_item_meta', $this->get_type(), $item, $this->order ); ?>
				</td>
				<td class="quantity"><?php echo esc_html( $item['quantity'] ); ?></td>
				<td class="price"><?php echo esc_html( $item['single_price'] ); ?></td>
				<td class="price"><?php echo esc_html( $item['order_price'] ); ?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<table class="notes-totals">
	<tbody>
		<tr class="no-borders">
			<td class="no-borders notes-cell">
				<?php do_action( 'wpo_wcpdf_before_document_notes', $this->get_type(), $this->order ); ?>
				<?php if ( $this->get_document_notes() ) : ?>
					<div class="document-notes">
						<h3><?php $this->notes_title(); ?></h3>
						<?php $this->document_notes(); ?>
					</div>
				<?php endif; ?>
				<?php do_action( 'wpo_wcpdf_after_document_notes', $this->get_type(), $this->order ); ?>
				<?php do_action( 'wpo_wcpdf_before_customer_notes', $this->get_type(), $this->order ); ?>
				<?php if ( $this->get_shipping_notes() ) : ?>
					<div class="customer-notes">
						<h3><?php $this->customer_notes_title(); ?></h3>
						<?php $this->shipping_notes(); ?>
					</div>
				<?php endif; ?>
				<?php do_action( 'wpo_wcpdf_after_customer_notes', $this->get_type(), $this->order ); ?>
			</td>
			<td class="no-borders totals-cell">
				<table class="totals">
					<tfoot>
						<?php foreach ( $this->get_woocommerce_totals() as $key => $total ) : ?>
							<tr class="<?php echo esc_attr( $key ); ?>">
								<th class="description"><?php echo esc_html( $total['label'] ); ?></th>
								<td class="price"><span class="totals-price"><?php echo esc_html( $total['value'] ); ?></span></td>
							</tr>
						<?php endforeach; ?>
					</tfoot>
				</table>
			</td>
		</tr>
	</tbody>
</table>
<?php if( $this->order->status == "completed" ) { ?>
<table class="payment-detail">
	<tbody>
		<tr>
			<td>
				<div class="invoice-paid">
					<div class="paid-stamp">
						<h2>Invoice Payment Details</h2>
						<div class="payment-details">
							<p><strong>Payment Date:</strong> <span><?php echo esc_html($manual_payment_data['payment_date']) ?> </span></p>
							<p><strong>Payment Amount:</strong> <span>$ <?php echo esc_html($manual_payment_data['payment_amount']) ?> </span></p>
							<p><strong>Payment Type:</strong> <span><?php echo esc_html($manual_payment_data['payment_type']) ?> </span></p>
						</div>
					</div>
				</div>
			</td>
		</tr>
	</tbody>
</table>
<?php } ?>
<?php do_action( 'wpo_wcpdf_after_order_details', $this->get_type(), $this->order ); ?>

<div class="bottom-spacer"></div>

<?php if ( $this->get_footer() ) : ?>
	<htmlpagefooter name="docFooter"><!-- required for mPDF engine -->
		<div id="footer">
			<!-- hook available: wpo_wcpdf_before_footer -->
			<?php $this->footer(); ?>
			<!-- hook available: wpo_wcpdf_after_footer -->
		</div>
	</htmlpagefooter><!-- required for mPDF engine -->
<?php endif; ?>

<?php do_action( 'wpo_wcpdf_after_document', $this->get_type(), $this->order ); ?>
