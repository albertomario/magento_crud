<?php

class OrdersController extends Zend_Controller_Action
{

    public function init()
    {
        Zend_Layout::startMvc();
    }

    public function listAction()
    {
        //$soapClient = new MagentoCrud_SoapClient('http://docserv.alberto.ro/soap/default?wsdl');
        //$soapClient->customerCustomerRepositoryV1('getList', ['searchCriteria' => ['filterGroups' => [['value' => 'hello', 'field' => 'name', 'test' => 'hello']]]]);
    }


}

