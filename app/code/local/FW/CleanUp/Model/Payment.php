<?php
//This class is a place holder for old orders that used the 'sterling' as a payment method, this class is required to load those orders - the STERLING module got removed on 1/5/2016
class FW_CleanUp_Model_Payment extends Mage_Payment_Model_Method_Cc{
	protected $_code 					= 'sterling';
	protected $_isGateway 				= true;
	protected $_canAuthorize            = true;
	protected $_canCapture              = false;
	protected $_canCapturePartial       = false;
	protected $_canRefund               = false;
	protected $_canRefundInvoicePartial = false;
	protected $_canVoid                 = false;
	protected $_canUseInternal          = true;
	protected $_canUseCheckout          = true;
	protected $_canUseForMultishipping  = true;
	protected $_canSaveCc				= false;

}