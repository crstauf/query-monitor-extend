# query-monitor-extend
WordPress plugin to add enhancements and extend the already awesome [Query Monitor](https://github.com/johnbillion/query-monitor) plugin by John Blackbourn.

- adds additional panels:
 * Constants
 * Image Sizes
 * Included Files
 * Multisite
 * Paths
 * Var Dumps (use `QM_dump()`)

- adds [WooCommerce](http://www.woothemes.com/woocommerce/) conditional function checks:
 * `is_account_page`
 * `is_cart`
 * `is_checkout`
 * `is_checkout_pay_page`
 * `is_product`
 * `is_product_taxonomy`
 * `is_product_category`
 * `is_product_tag`
 * `is_shop`
 * `is_wc_endpoint_url`
 * `is_woocommerce`
 * `is_view_order_page`
 * `is_edit_account_page`
 * `is_order_received_page`
 * `is_add_payment_method_page`
 * `is_lost_password_page`
 * `is_store_notice_showing`

- adds additional columns/info to:
 * Assets panels
 * Transients panel

- provides a "graph" display of all notices/errors (multiple background colors of admin bar menu item)

!["Graph" display of notices/errors](https://cldup.com/orLoJ0VsTe-3000x3000.png "'Graph' display of notices/errors")
