<?php
/*
*
* Author: Jeff Simons Decena @2013
*
*/

if (!defined('_PS_VERSION_'))
	exit;

class Acommerce extends PaymentModule
{

	public function __construct()
	{
	$this->name = 'acommerce';
	$this->tab = 'front_office_features';
	$this->version = '0.1';
	$this->author = 'Jeff Simons Decena';
	$this->need_instance = 0;
	$this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.6');	

	parent::__construct();

	$this->displayName = $this->l('Acommerce Module');
	$this->description = $this->l('Acommerce configuration module');

	$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

	if (!Configuration::get('ACOMMERCE'))
	  $this->warning = $this->l('No name provided');
	}

	public function install()
	{
	  return parent::install() &&
	  	Configuration::updateValue('ACOMMERCE', 'ACOMMERCE MODULE') &&
	  	Configuration::updateValue('REMOTEDIR', '/home') &&
	  	$this->registerHook('payment');
	}	

	public function uninstall()
	{
	  return parent::uninstall() && 
	  	Configuration::deleteByName('ACOMMERCE');
	}

	public function getContent()
	{	 
		$output = null;

	    if (Tools::isSubmit('submit'.$this->name))
	    {
	    	Configuration::updateValue('ACOMMERCE', 'ACOMMERCE MODULE');
            Configuration::updateValue('SECUREHOST', Tools::getValue('SECUREHOST'));
            Configuration::updateValue('SECUREPORT', Tools::getValue('SECUREPORT'));
            Configuration::updateValue('USERNAME', Tools::getValue('USERNAME'));
            Configuration::updateValue('PASSWORD', Tools::getValue('PASSWORD'));
            Configuration::updateValue('REMOTEDIR', Tools::getValue('REMOTEDIR'));
            $output .= $this->displayConfirmation($this->l('Settings updated'));
	    }
	    return $this->displayForm();
	}

	public function displayForm()
	{
	    // Get default Language
	    $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
	     
	    // Init Fields form array
	    $fields_form[0]['form'] = array(
	        'legend' => array(
	            'title' => $this->l('Settings'),
	        ),
	        'input' => array(
	            array(
	                'type' => 'text',
	                'label' => $this->l('Host'),
	                'name' => 'SECUREHOST',
	                'size' => 20,
	                'required' => true
	            ),
	            array(
	                'type' => 'text',
	                'label' => $this->l('Port'),
	                'name' => 'SECUREPORT',
	                'size' => 20,
	                'required' => true
	            ),	            
	            array(
	                'type' => 'text',
	                'label' => $this->l('Username'),
	                'name' => 'USERNAME',
	                'size' => 20,
	                'required' => true
	            ),
	            array(
	                'type' => 'text',
	                'label' => $this->l('Password'),
	                'name' => 'PASSWORD',
	                'size' => 20,
	                'required' => true
	            ),
	            array(
	                'type' => 'text',
	                'label' => $this->l('Remote Directory Path'),
	                'name' => 'REMOTEDIR',
	                'size' => 20,
	                'required' => true
	            )	            
	        ),
	        'submit' => array(
	            'title' => $this->l('Save'),
	            'class' => 'button'
	        )
	    );
	     
	    $helper = new HelperForm();
	     
	    // Module, token and currentIndex
	    $helper->module = $this;
	    $helper->name_controller = $this->name;
	    $helper->token = Tools::getAdminTokenLite('AdminModules');
	    $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
	     
	    // Language
	    $helper->default_form_language = $default_lang;
	    $helper->allow_employee_form_lang = $default_lang;
	     
	    // Title and toolbar
	    $helper->title = $this->displayName;
	    $helper->show_toolbar = true;        // false -> remove toolbar
	    $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
	    $helper->submit_action = 'submit'.$this->name;
	    $helper->toolbar_btn = array(
	        'save' =>
	        array(
	            'desc' => $this->l('Save'),
	            'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
	            '&token='.Tools::getAdminTokenLite('AdminModules'),
	        ),
	        'back' => array(
	            'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
	            'desc' => $this->l('Back to list')
	        )
	    );
	     
	    // Load current value
	    $helper->fields_value['SECUREHOST'] = Configuration::get('SECUREHOST');
	    $helper->fields_value['SECUREPORT'] = Configuration::get('SECUREPORT');
	    $helper->fields_value['USERNAME'] 	= Configuration::get('USERNAME');
	    $helper->fields_value['PASSWORD'] 	= Configuration::get('PASSWORD');
	    $helper->fields_value['REMOTEDIR'] 	= Configuration::get('REMOTEDIR');
	     
	    return $helper->generateForm($fields_form);
	}
}