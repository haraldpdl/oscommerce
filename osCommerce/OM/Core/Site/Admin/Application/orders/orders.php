<?php
/*
  $Id: $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2009 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require('includes/applications/orders/classes/orders.php');
  require('includes/classes/order.php');

  class osC_Application_Orders extends osC_Template_Admin {

/* Protected variables */

    protected $_module = 'orders',
              $_page_title,
              $_page_contents = 'main.php';

/* Class constructor */

    function __construct() {
      global $osC_Database, $osC_Language, $osC_MessageStack, $osC_Currencies, $orders_statuses, $orders_status_array;

      $this->_page_title = $osC_Language->get('heading_title');

      if ( !isset($_GET['action']) ) {
        $_GET['action'] = '';
      }

      if ( !isset($_GET['page']) || ( isset($_GET['page']) && !is_numeric($_GET['page']) ) ) {
        $_GET['page'] = 1;
      }

      include('../includes/classes/currencies.php');
      $osC_Currencies = new osC_Currencies();

      $orders_statuses = array();
      $orders_status_array = array();

      $Qstatuses = $osC_Database->query('select orders_status_id, orders_status_name from :table_orders_status where language_id = :language_id');
      $Qstatuses->bindTable(':table_orders_status', TABLE_ORDERS_STATUS);
      $Qstatuses->bindInt(':language_id', $osC_Language->getID());
      $Qstatuses->execute();

      while ($Qstatuses->next()) {
        $orders_statuses[] = array('id' => $Qstatuses->valueInt('orders_status_id'),
                                   'text' => $Qstatuses->value('orders_status_name'));

        $orders_status_array[$Qstatuses->valueInt('orders_status_id')] = $Qstatuses->value('orders_status_name');
      }

      if ( !empty($_GET['action']) ) {
        switch ( $_GET['action'] ) {
          case 'invoice':
            $this->_page_contents = 'invoice.php';
            $this->_has_header = false;
            $this->_has_footer = false;

            break;

          case 'packaging_slip':
            $this->_page_contents = 'packaging_slip.php';
            $this->_has_header = false;
            $this->_has_footer = false;

            break;

          case 'save':
            $this->_page_contents = 'edit.php';

            break;

          case 'updateTransaction':
            if ( isset($_POST['subaction']) && ($_POST['subaction'] == 'confirm') ) {
              if ( $this->_updateTransaction($_GET['oID'], $_POST['transaction']) ) {
                $osC_MessageStack->add($this->_module, $osC_Language->get('ms_success_action_performed'), 'success');
              } else {
                $osC_MessageStack->add($this->_module, $osC_Language->get('ms_error_action_not_performed'), 'error');
              }

              osc_redirect_admin(osc_href_link_admin(FILENAME_DEFAULT, $this->_module . '&oID=' . $_GET['oID'] . '&' . (isset($_GET['search']) ? 'search=' . $_GET['search'] . '&' : '') . (isset($_GET['status']) ? 'status=' . $_GET['status'] . '&' : '') . (isset($_GET['cID']) ? 'cID=' . $_GET['cID'] . '&' : '') . 'page=' . $_GET['page'] . '&action=save&tabIndex=tabTransactionHistory'));
            }

            break;

          case 'updateStatus':
            if ( isset($_POST['subaction']) && ($_POST['subaction'] == 'confirm') ) {
              $data = array('status_id' => $_POST['status'],
                            'comment' => $_POST['comment'],
                            'notify_customer' => ( isset($_POST['notify_customer']) && ( $_POST['notify_customer'] == 'on' ) ? true : false ),
                            'append_comment' => ( isset($_POST['append_comment']) && ( $_POST['append_comment'] == 'on' ) ? true : false ));

              if ( $this->_updateStatus($_GET['oID'], $data) ) {
                $osC_MessageStack->add($this->_module, $osC_Language->get('ms_success_action_performed'), 'success');
              } else {
                $osC_MessageStack->add($this->_module, $osC_Language->get('ms_error_action_not_performed'), 'error');
              }

              osc_redirect_admin(osc_href_link_admin(FILENAME_DEFAULT, $this->_module . '&oID=' . $_GET['oID'] . '&' . (isset($_GET['search']) ? 'search=' . $_GET['search'] . '&' : '') . (isset($_GET['status']) ? 'status=' . $_GET['status'] . '&' : '') . (isset($_GET['cID']) ? 'cID=' . $_GET['cID'] . '&' : '') . 'page=' . $_GET['page'] . '&action=save&tabIndex=tabStatusHistory'));
            }

            break;

          case 'delete':
            $this->_page_contents = 'delete.php';

            if ( isset($_POST['subaction']) && ($_POST['subaction'] == 'confirm') ) {
              if ( osC_Orders_Admin::delete($_GET['oID'], (isset($_POST['restock']) && ($_POST['restock'] == 'on') ? true : false)) ) {
                $osC_MessageStack->add($this->_module, $osC_Language->get('ms_success_action_performed'), 'success');
              } else {
                $osC_MessageStack->add($this->_module, $osC_Language->get('ms_error_action_not_performed'), 'error');
              }

              osc_redirect_admin(osc_href_link_admin(FILENAME_DEFAULT, $this->_module . '&' . (isset($_GET['search']) ? 'search=' . $_GET['search'] . '&' : '') . (isset($_GET['status']) ? 'status=' . $_GET['status'] . '&' : '') . (isset($_GET['cID']) ? 'cID=' . $_GET['cID'] . '&' : '') . 'page=' . $_GET['page']));
            }

            break;

          case 'batchDelete':
            if ( isset($_POST['batch']) && is_array($_POST['batch']) && !empty($_POST['batch']) ) {
              $this->_page_contents = 'batch_delete.php';

              if ( isset($_POST['subaction']) && ($_POST['subaction'] == 'confirm') ) {
                $error = false;

                foreach ($_POST['batch'] as $id) {
                  if ( !osC_Orders_Admin::delete($id, (isset($_POST['restock']) && ($_POST['restock'] == 'on') ? true : false)) ) {
                    $error = true;
                    break;
                  }
                }

                if ( $error === false ) {
                  $osC_MessageStack->add($this->_module, $osC_Language->get('ms_success_action_performed'), 'success');
                } else {
                  $osC_MessageStack->add($this->_module, $osC_Language->get('ms_error_action_not_performed'), 'error');
                }

                osc_redirect_admin(osc_href_link_admin(FILENAME_DEFAULT, $this->_module . '&' . (isset($_GET['search']) ? 'search=' . $_GET['search'] . '&' : '') . (isset($_GET['status']) ? 'status=' . $_GET['status'] . '&' : '') . (isset($_GET['cID']) ? 'cID=' . $_GET['cID'] . '&' : '') . 'page=' . $_GET['page']));
              }
            }

            break;
        }
      }
    }

/* Private methods */

    function _updateTransaction($id, $call_function) {
      global $osC_Database;

      $Qorder = $osC_Database->query('select payment_module from :table_orders where orders_id = :orders_id limit 1');
      $Qorder->bindTable(':table_orders', TABLE_ORDERS);
      $Qorder->bindInt(':orders_id', $id);
      $Qorder->execute();

      if ( ( $Qorder->numberOfRows() === 1) && !osc_empty($Qorder->value('payment_module')) ) {
        if ( file_exists('includes/modules/payment/' . $Qorder->value('payment_module') . '.php') ) {
          include('includes/classes/payment.php');
          include('includes/modules/payment/' . $Qorder->value('payment_module') . '.php');

          if ( is_callable(array('osC_Payment_' . $Qorder->value('payment_module'), $call_function)) ) {
            $payment_module = 'osC_Payment_' . $Qorder->value('payment_module');
            $payment_module = new $payment_module();
            $payment_module->$call_function($id);
// HPDL - the following static call won't work due to using $this->_gateway_url in the class method
//            call_user_func(array('osC_Payment_' . $Qorder->value('payment_module'), $call_function), $id);

            return true;
          }
        }
      }

      return false;
    }

    function _updateStatus($id, $data) {
      global $osC_Database, $osC_Language, $orders_status_array;

      $error = false;

      $osC_Database->startTransaction();

      $Qorder = $osC_Database->query('select customers_name, customers_email_address, orders_status, date_purchased from :table_orders where orders_id = :orders_id');
      $Qorder->bindTable(':table_orders', TABLE_ORDERS);
      $Qorder->bindInt(':orders_id', $id);
      $Qorder->execute();

      $Qupdate = $osC_Database->query('update :table_orders set orders_status = :orders_status, last_modified = now() where orders_id = :orders_id');
      $Qupdate->bindTable(':table_orders', TABLE_ORDERS);
      $Qupdate->bindInt(':orders_status', $data['status_id']);
      $Qupdate->bindInt(':orders_id', $id);
      $Qupdate->setLogging($_SESSION['module'], $id);
      $Qupdate->execute();

      if ( !$osC_Database->isError() ) {
        if ( $data['notify_customer'] === true ) {
          $email_body = sprintf($osC_Language->get('email_body'), STORE_NAME, $id, osc_href_link(FILENAME_ACCOUNT, 'orders=' . $id, 'SSL', false, false, true), osC_DateTime::getLong($Qorder->value('date_purchased'))) . "\n\n";

          if ( $data['append_comment'] === true ) {
            $email_body .= sprintf($osC_Language->get('email_body_comment'), $data['comment']) . "\n\n";
          }

          $email_body .= sprintf($osC_Language->get('email_body_status'), $orders_status_array[$data['status_id']]) . "\n\n" .
                         $osC_Language->get('email_body_contact');

          osc_email($Qorder->value('customers_name'), $Qorder->value('customers_email_address'), sprintf($osC_Language->get('email_subject'), STORE_NAME), $email_body, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
        }

        $Qupdate = $osC_Database->query('insert into :table_orders_status_history (orders_id, orders_status_id, date_added, customer_notified, comments) values (:orders_id, :orders_status_id, now(), :customer_notified, :comments)');
        $Qupdate->bindTable(':table_orders_status_history', TABLE_ORDERS_STATUS_HISTORY);
        $Qupdate->bindInt(':orders_id', $id);
        $Qupdate->bindInt(':orders_status_id', $data['status_id']);
        $Qupdate->bindInt(':customer_notified', ( $data['notify_customer'] === true ? '1' : '0'));
        $Qupdate->bindValue(':comments', $data['comment']);
        $Qupdate->setLogging($_SESSION['module'], $id);
        $Qupdate->execute();

        if ( $osC_Database->isError() ) {
          $error = true;
        }
      } else {
        $error = true;
      }

      if ( $error === false ) {
        $osC_Database->commitTransaction();

        return true;
      }

      $osC_Database->rollbackTransaction();

      return false;
    }
  }
?>
