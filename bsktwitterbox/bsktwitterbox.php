<?php

/*
 * BitSHOK Starter Module
 * 
 * @author BitSHOK <office@bitshok.net>
 * @copyright 2012 BitSHOK
 * @version 1.0
 * @license http://creativecommons.org/licenses/by/3.0/ CC BY 3.0
 */

!defined('_PS_VERSION_') && exit;

class BskTwitterBox extends Module {

    public function __construct() {
        $this->name = 'bsktwitterbox'; // internal identifier, unique and lowercase
        $this->tab = 'other'; // backend module coresponding category
        $this->version = '1.0'; // version number for the module
        $this->author = 'BitSHOK'; // module author
        $this->need_instance = 0; // load the module when displaying the "Modules" page in backend

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('Twitter Box'); // public name
        $this->description = $this->l('Twitter Box for PrestaShop 1.6'); // public description
    }

    /*
     * Install this module
     */
    public function install() {
        return parent::install() &&
           $this->registerHook('displayHeader') &&
           $this->registerHook('displayRightColumn') &&
           $this->initConfig();
    }

    /*
     * Uninstall this module
     */
    public function uninstall() {
        return Configuration::deleteByName($this->name) &&
               parent::uninstall();
    }

    /**
     * Set the default values for Configuration page settings
     */
    protected function initConfig() {
        $config = array();
        
        $config['user'] = 'bitshok';
        $config['widget_id'] = '355763562850942976';
        $config['tweets_limit'] = 3;
        $config['follow_btn'] = 'on';
        
        return Configuration::updateValue($this->name, json_encode($config));
    }

    public function getConfigFieldsValues() {
        return json_decode(Configuration::get($this->name), true);
    }
    
    /*
     * Header of pages hook (Technical name: displayHeader)
     */
    public function hookHeader() {
        $this->context->controller->addCSS($this->_path . 'style.css');
        $this->context->controller->addJqueryPlugin(array('scrollTo', 'serialScroll'));
        $this->context->controller->addJS($this->_path . 'script.js');
    }

    /*
     * Top of pages (Technical name: displayTopColumn)
     */
    public function hookdisplayTopColumn() {
        $config = json_decode(Configuration::get($this->name), true);
        
        $this->context->smarty->assign(array(
            'user'          => $config['user'],
            'widget_id'     => $config['widget_id'],
            'tweets_limit'  => $config['tweets_limit'],
            'follow_btn'    => $config['follow_btn']
        ));

        return $this->display(__FILE__, $this->name . '_scroll.tpl');
    }

    /*
     * Footer (Technical name: displayFooter)
     */
    public function hookFooter() {
        $config = json_decode(Configuration::get($this->name), true);

        $this->context->smarty->assign(array(
            'user'          => $config['user'],
            'widget_id'     => $config['widget_id'],
            'tweets_limit'  => $config['tweets_limit'],
            'follow_btn'    => $config['follow_btn']
        ));

        return $this->display(__FILE__, $this->name . '.tpl');
    }

    /*
     * Left column blocks (Technical name: name:displayLeftColumn)
     */
    public function hookLeftColumn() {
        return $this->hookFooter();
    }

    /*
     * Right column blocks (Technical name: name:displayRightColumn)
     */
    public function hookRightColumn() {
        return $this->hookFooter();
    }

    private function _displayTwitterInfo() {
        return $this->display(__FILE__, 'info.tpl');
    }


    /**
     * Configuration page
     */
    public function getContent() {
        $this->_html = '';
        
        $this->_html .= $this->_displayTwitterInfo();
        $this->_html .= $this->postProcess();
        $this->_html .= $this->renderForm();
        
        return $this->_html;
    }
    
    public function renderForm() {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Twitter Box'),
                    'icon'  => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type'  => 'text',
                        'label' => $this->l('User'),
                        'name'  => 'user',
                        'class' => 'fixed-width-lg'
                    ),
                    array(
                        'type'  => 'text',
                        'label' => $this->l('Widget ID'),
                        'name'  => 'widget_id',
                        'class' => 'fixed-width-lg'
                    ),
                    array(
                        'type'  => 'text',
                        'label' => $this->l('Tweets limit'),
                        'name'  => 'tweets_limit',
                        'class' => 'fixed-width-lg'
                    ),
                    array(
                        'type'      => 'switch',
                        'label'     => $this->l('Show Follow button'),
                        'name'      => 'follow_btn',
                        'values'    => array(
                            array(
                                'id'    => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'class' => 'pull-right'
                )
            )
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'saveBtn';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
                . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }
    
    public function postProcess() {
        if (Tools::isSubmit('saveBtn')) {
            $config = array();
            
            $config['user'] = Tools::getValue('user');
            $config['widget_id'] = Tools::getValue('widget_id');
            $config['tweets_limit']  = Tools::getValue('tweets_limit');
            $config['follow_btn'] = Tools::getValue('follow_btn');
            
            Configuration::updateValue($this->name, json_encode($config));

            return $this->displayConfirmation($this->l('Settings updated'));
        }
    }

}
