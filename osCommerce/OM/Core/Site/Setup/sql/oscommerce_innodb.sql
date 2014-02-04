# osCommerce Online Merchant
#
# @copyright Copyright (c) 2011 osCommerce; http://www.oscommerce.com
# @license BSD License; http://www.oscommerce.com/bsdlicense.txt

ALTER TABLE osc_address_book engine = InnoDB;
ALTER TABLE osc_administrator_shortcuts engine = InnoDB;
ALTER TABLE osc_administrators engine = InnoDB;
ALTER TABLE osc_administrators_access engine = InnoDB;
ALTER TABLE osc_administrators_log engine = InnoDB;
ALTER TABLE osc_banners engine = InnoDB;
ALTER TABLE osc_banners_history engine = InnoDB;
ALTER TABLE osc_categories engine = InnoDB;
ALTER TABLE osc_categories_description engine = InnoDB;
ALTER TABLE osc_configuration engine = InnoDB;
ALTER TABLE osc_configuration_group engine = InnoDB;
ALTER TABLE osc_counter engine = InnoDB;
ALTER TABLE osc_countries engine = InnoDB;
ALTER TABLE osc_credit_cards engine = InnoDB;
ALTER TABLE osc_currencies engine = InnoDB;
ALTER TABLE osc_customers engine = InnoDB;
ALTER TABLE osc_fk_relationships engine = InnoDB;
ALTER TABLE osc_geo_zones engine = InnoDB;
ALTER TABLE osc_languages engine = InnoDB;
ALTER TABLE osc_languages_definitions engine = InnoDB;
ALTER TABLE osc_manufacturers engine = InnoDB;
ALTER TABLE osc_manufacturers_info engine = InnoDB;
ALTER TABLE osc_modules engine = InnoDB;
ALTER TABLE osc_newsletters engine = InnoDB;
ALTER TABLE osc_newsletters_log engine = InnoDB;
ALTER TABLE osc_orders engine = InnoDB;
ALTER TABLE osc_orders_products engine = InnoDB;
ALTER TABLE osc_orders_products_download engine = InnoDB;
ALTER TABLE osc_orders_products_variants engine = InnoDB;
ALTER TABLE osc_orders_status engine = InnoDB;
ALTER TABLE osc_orders_status_history engine = InnoDB;
ALTER TABLE osc_orders_total engine = InnoDB;
ALTER TABLE osc_orders_transactions_history engine = InnoDB;
ALTER TABLE osc_orders_transactions_status engine = InnoDB;
ALTER TABLE osc_product_attributes engine = InnoDB;
ALTER TABLE osc_product_types engine = InnoDB;
ALTER TABLE osc_product_types_assignments engine = InnoDB;
ALTER TABLE osc_products engine = InnoDB;
ALTER TABLE osc_products_description engine = InnoDB;
ALTER TABLE osc_products_images engine = InnoDB;
ALTER TABLE osc_products_images_groups engine = InnoDB;
ALTER TABLE osc_products_notifications engine = InnoDB;
ALTER TABLE osc_products_to_categories engine = InnoDB;
ALTER TABLE osc_products_variants engine = InnoDB;
ALTER TABLE osc_products_variants_groups engine = InnoDB;
ALTER TABLE osc_products_variants_values engine = InnoDB;
ALTER TABLE osc_reviews engine = InnoDB;
ALTER TABLE osc_sessions engine = InnoDB;
ALTER TABLE osc_shipping_availability engine = InnoDB;
ALTER TABLE osc_shopping_carts engine = InnoDB;
ALTER TABLE osc_shopping_carts_custom_variants_values engine = InnoDB;
ALTER TABLE osc_specials engine = InnoDB;
ALTER TABLE osc_tax_class engine = InnoDB;
ALTER TABLE osc_tax_rates engine = InnoDB;
ALTER TABLE osc_templates engine = InnoDB;
ALTER TABLE osc_templates_boxes engine = InnoDB;
ALTER TABLE osc_templates_boxes_to_pages engine = InnoDB;
ALTER TABLE osc_weight_classes engine = InnoDB;
ALTER TABLE osc_weight_classes_rules engine = InnoDB;
ALTER TABLE osc_whos_online engine = InnoDB;
ALTER TABLE osc_zones engine = InnoDB;
ALTER TABLE osc_zones_to_geo_zones engine = InnoDB;

SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE osc_address_book add CONSTRAINT idx_address_book_customers_id FOREIGN KEY (customers_id) REFERENCES osc_customers (customers_id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE osc_address_book add CONSTRAINT idx_address_book_country_id FOREIGN KEY (entry_country_id) REFERENCES osc_countries (countries_id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE osc_address_book add CONSTRAINT idx_address_book_zone_id FOREIGN KEY (entry_zone_id) REFERENCES osc_zones (zone_id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE osc_administrator_shortcuts add CONSTRAINT idx_admin_shortcuts_admin_id FOREIGN KEY (administrators_id) REFERENCES osc_administrators (id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE osc_administrators_access add CONSTRAINT idx_admin_access_admin_id FOREIGN KEY (administrators_id) REFERENCES osc_administrators (id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE osc_administrators_log add CONSTRAINT idx_administrators_log_admin_id FOREIGN KEY (administrators_id) REFERENCES osc_administrators (id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE osc_banners_history add CONSTRAINT idx_banners_history_banners_id FOREIGN KEY (banners_id) REFERENCES osc_banners (banners_id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE osc_categories add CONSTRAINT idx_categories_parent_id FOREIGN KEY (parent_id) REFERENCES osc_categories (categories_id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE osc_categories_description add CONSTRAINT idx_categories_desc_categories_id FOREIGN KEY (categories_id) REFERENCES osc_categories (categories_id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE osc_categories_description add CONSTRAINT idx_categories_desc_language_id FOREIGN KEY (language_id) REFERENCES osc_languages (languages_id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE osc_configuration add CONSTRAINT idx_configuration_group_id FOREIGN KEY (configuration_group_id) REFERENCES osc_configuration_group (configuration_group_id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE osc_customers add CONSTRAINT idx_customers_default_address_id FOREIGN KEY (customers_default_address_id) REFERENCES osc_address_book (address_book_id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE osc_languages add CONSTRAINT idx_languages_currencies_id FOREIGN KEY (currencies_id) REFERENCES osc_currencies (currencies_id) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE osc_languages_definitions add CONSTRAINT idx_languages_definitions_languages_id FOREIGN KEY (languages_id) REFERENCES osc_languages (languages_id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE osc_manufacturers_info add CONSTRAINT idx_manufacturers_info_id FOREIGN KEY (manufacturers_id) REFERENCES osc_manufacturers (manufacturers_id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE osc_manufacturers_info add CONSTRAINT idx_manufacturers_info_languages_id FOREIGN KEY (languages_id) REFERENCES osc_languages (languages_id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE osc_newsletters_log add CONSTRAINT idx_newsletters_log_newsletters_id FOREIGN KEY (newsletters_id) REFERENCES osc_newsletters (newsletters_id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE osc_orders add CONSTRAINT idx_orders_status FOREIGN KEY (orders_status) REFERENCES osc_orders_status (orders_status_id) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE osc_orders_products add CONSTRAINT idx_orders_products_orders_id FOREIGN KEY (orders_id) REFERENCES osc_orders (orders_id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE osc_orders_products_download add CONSTRAINT idx_orders_products_download_orders_id FOREIGN KEY (orders_id) REFERENCES osc_orders (orders_id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE osc_orders_products_variants add CONSTRAINT idx_orders_products_variants_orders_id FOREIGN KEY (orders_id) REFERENCES osc_orders (orders_id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE osc_orders_status add CONSTRAINT idx_orders_status_language_id FOREIGN KEY (language_id) REFERENCES osc_languages (languages_id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE osc_orders_status_history add CONSTRAINT idx_orders_status_history_orders_id FOREIGN KEY (orders_id) REFERENCES osc_orders (orders_id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE osc_orders_status_history add CONSTRAINT idx_orders_status_history_orders_status_id FOREIGN KEY (orders_status_id) REFERENCES osc_orders_status (orders_status_id) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE osc_orders_total add CONSTRAINT idx_orders_total_orders_id FOREIGN KEY (orders_id) REFERENCES osc_orders (orders_id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE osc_orders_transactions_history add CONSTRAINT idx_orders_transactions_history_orders_id FOREIGN KEY (orders_id) REFERENCES osc_orders (orders_id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE osc_orders_transactions_status add CONSTRAINT idx_orders_transactions_status_language_id FOREIGN KEY (language_id) REFERENCES osc_languages (languages_id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE osc_product_attributes add CONSTRAINT idx_pa_products_id FOREIGN KEY (products_id) REFERENCES osc_products (products_id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE osc_product_attributes add CONSTRAINT idx_pa_languages_id FOREIGN KEY (languages_id) REFERENCES osc_languages (languages_id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE osc_product_types_assignments add CONSTRAINT idx_product_types_assignments_types_id FOREIGN KEY (types_id) REFERENCES osc_product_types (id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE osc_products add CONSTRAINT idx_products_parent_id FOREIGN KEY (parent_id) REFERENCES osc_products (products_id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE osc_products add CONSTRAINT idx_products_weight_class FOREIGN KEY (products_weight_class) REFERENCES osc_weight_classes (weight_class_id) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE osc_products add CONSTRAINT idx_products_tax_class_id FOREIGN KEY (products_tax_class_id) REFERENCES osc_tax_class (tax_class_id) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE osc_products add CONSTRAINT idx_products_types_id FOREIGN KEY (products_types_id) REFERENCES osc_product_types (id) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE osc_products add CONSTRAINT idx_products_manufacturers_id FOREIGN KEY (manufacturers_id) REFERENCES osc_manufacturers (manufacturers_id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE osc_products_description add CONSTRAINT idx_products_id FOREIGN KEY (products_id) REFERENCES osc_products (products_id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE osc_products_description add CONSTRAINT idx_products_language_id FOREIGN KEY (language_id) REFERENCES osc_languages (languages_id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE osc_products_images add CONSTRAINT idx_products_images_products_id FOREIGN KEY (products_id) REFERENCES osc_products (products_id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE osc_products_images_groups add CONSTRAINT idx_products_images_groups_language_id FOREIGN KEY (language_id) REFERENCES osc_languages (languages_id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE osc_products_notifications add CONSTRAINT idx_products_notifications_products_id FOREIGN KEY (products_id) REFERENCES osc_products (products_id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE osc_products_notifications add CONSTRAINT idx_products_notifications_customers_id FOREIGN KEY (customers_id) REFERENCES osc_customers (customers_id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE osc_products_to_categories add CONSTRAINT idx_p2c_products_id FOREIGN KEY (products_id) REFERENCES osc_products (products_id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE osc_products_to_categories add CONSTRAINT idx_p2c_categories_id FOREIGN KEY (categories_id) REFERENCES osc_categories (categories_id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE osc_products_variants add CONSTRAINT idx_products_variants_products_id FOREIGN KEY (products_id) REFERENCES osc_products (products_id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE osc_products_variants add CONSTRAINT idx_products_variants_values_id FOREIGN KEY (products_variants_values_id) REFERENCES osc_products_variants_values (id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE osc_products_variants_groups add CONSTRAINT idx_products_variants_groups_languages_id FOREIGN KEY (languages_id) REFERENCES osc_languages (languages_id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE osc_products_variants_values add CONSTRAINT idx_products_variants_values_languages_id FOREIGN KEY (languages_id) REFERENCES osc_languages (languages_id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE osc_products_variants_values add CONSTRAINT idx_products_variants_values_groups_id FOREIGN KEY (products_variants_groups_id) REFERENCES osc_products_variants_groups (id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE osc_reviews add CONSTRAINT idx_reviews_products_id FOREIGN KEY (products_id) REFERENCES osc_products (products_id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE osc_reviews add CONSTRAINT idx_reviews_customers_id FOREIGN KEY (customers_id) REFERENCES osc_customers (customers_id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE osc_reviews add CONSTRAINT idx_reviews_languages_id FOREIGN KEY (languages_id) REFERENCES osc_languages (languages_id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE osc_shipping_availability add CONSTRAINT idx_shipping_availability_languages_id FOREIGN KEY (languages_id) REFERENCES osc_languages (languages_id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE osc_shopping_carts add CONSTRAINT idx_sc_customers_id FOREIGN KEY (customers_id) REFERENCES osc_customers (customers_id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE osc_shopping_carts add CONSTRAINT idx_sc_products_id FOREIGN KEY (products_id) REFERENCES osc_products (products_id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE osc_shopping_carts_custom_variants_values add CONSTRAINT idx_sccvv_customers_id FOREIGN KEY (customers_id) REFERENCES osc_customers (customers_id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE osc_shopping_carts_custom_variants_values add CONSTRAINT idx_sccvv_products_id FOREIGN KEY (products_id) REFERENCES osc_products (products_id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE osc_shopping_carts_custom_variants_values add CONSTRAINT idx_sccvv_products_variants_values_id FOREIGN KEY (products_variants_values_id) REFERENCES osc_products_variants_values (id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE osc_specials add CONSTRAINT idx_specials_products_id FOREIGN KEY (products_id) REFERENCES osc_products (products_id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE osc_tax_rates add CONSTRAINT idx_tax_rates_zone_id FOREIGN KEY (tax_zone_id) REFERENCES osc_geo_zones (geo_zone_id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE osc_tax_rates add CONSTRAINT idx_tax_rates_class_id FOREIGN KEY (tax_class_id) REFERENCES osc_tax_class (tax_class_id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE osc_templates_boxes_to_pages add CONSTRAINT idx_tb2p_templates_boxes_id FOREIGN KEY (templates_boxes_id) REFERENCES osc_templates_boxes (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE osc_templates_boxes_to_pages add CONSTRAINT idx_tb2p_templates_id FOREIGN KEY (templates_id) REFERENCES osc_templates (id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE osc_weight_classes add CONSTRAINT idx_weight_classes_language_id FOREIGN KEY (language_id) REFERENCES osc_languages (languages_id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE osc_weight_classes_rules add CONSTRAINT idx_weight_class_from_id FOREIGN KEY (weight_class_from_id) REFERENCES osc_weight_classes (weight_class_id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE osc_weight_classes_rules add CONSTRAINT idx_weight_class_to_id FOREIGN KEY (weight_class_to_id) REFERENCES osc_weight_classes (weight_class_id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE osc_whos_online add CONSTRAINT idx_whos_online_customer_id FOREIGN KEY (customer_id) REFERENCES osc_customers (customers_id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE osc_zones add CONSTRAINT idx_zones_country_id FOREIGN KEY (zone_country_id) REFERENCES osc_countries (countries_id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE osc_zones_to_geo_zones add CONSTRAINT idx_z2gz_zone_country_id FOREIGN KEY (zone_country_id) REFERENCES osc_countries (countries_id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE osc_zones_to_geo_zones add CONSTRAINT idx_z2gz_zone_id FOREIGN KEY (zone_id) REFERENCES osc_zones (zone_id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE osc_zones_to_geo_zones add CONSTRAINT idx_z2gz_geo_zone_id FOREIGN KEY (geo_zone_id) REFERENCES osc_geo_zones (geo_zone_id) ON DELETE CASCADE ON UPDATE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;
