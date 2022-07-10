<?php

/**
 * Class AdminDealtModuleMissionsController
 */
class AdminDealtModuleMissionsController extends ModuleAdminController
{
    /**
     * AdminDealtModuleDealsController constructor
     */
    public function __construct()
    {
        $this->table = 'dealt_mission';
        $this->className = 'DealtMission';
        $this->identifier = 'id_mission';
        $this->bootstrap = true;

        parent::__construct();

        require_once(_DEALT_MODULE_MODELS_DIR_ . 'DealtMission.php');

        $this->initList();
    }
    /**
     * Collects bid range list data
     */
    private function initList()
    {

        $this->fields_list = [
            'id_order' => array(
                'title' => $this->l('Order Id'),
            ),
            'dealt_status_mission' => [
                'title' => $this->l('Mission status'),
            ],
            'date_add' => [
                'title' => $this->l('Date add'),
                'filter_key' => 'a!date_add',
                'type' => 'datetime'
            ]
        ];
        $this->_defaultOrderBy = 'date_add';
        $this->_defaultOrderWay = 'DESC';
        $this->actions = ['show', 'resubmit', 'cancel'];
    }
}
