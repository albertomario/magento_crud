<?php

class CustomersController extends Zend_Controller_Action
{

    public function init()
    {
        Zend_Layout::startMvc();
        $this->soapUri = 'http://docserv.alberto.ro/soap/default?wsdl';
        $this->soapClient = new MagentoCrud_SoapClient($this->soapUri);
        $this->view->hasError = [];
        $this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
        $this->view->flashMessenger = $this->_flashMessenger;

        $this->db = Zend_Registry::getInstance()['DB']->getDbAdapter();
    }

    public function listAction()
    {

        $clientsList =  $this->soapClient->customerCustomerRepositoryV1('getList', ['searchCriteria' => ['filterGroups' => [['value' => 'hello', 'field' => 'name', 'test' => 'hello']]]]);
    
    	if($clientsList->result->totalCount > 0) {
			$this->view->customersList = $clientsList->result->items;
    	}
    }

    public function editAction()
    {

        try {
            $clientDetails =  $this->soapClient->customerCustomerRepositoryV1('GetById', ['customerId' => $this->_getParam('customerId')]);
            $this->view->clientDetails = $clientDetails->result;
        } catch (SoapFault $e) {
            die(var_dump($e->getMessage()));
        }



        $this->view->resultHistory = $this->db->query("SELECT * FROM `change_history` WHERE `customer_id` = '".md5($this->soapUri)."_".$this->_getParam('customerId')."'")->fetchAll();


        if ($this->getRequest()->isPost()) {

            foreach($this->getRequest()->getPost() as $postElement => $postValue)
            {
                
                switch ($postElement) {

                    case 'upd_firstname':
                        if(strlen($postValue) < 3) {
                            $this->view->hasError[$postElement] = '"Prenumele" trebuie sa contina cel putin 3 caractere.';
                        } else {
                            $clientDetails->result->firstname = $postValue;
                        }
                        break;
                    case 'upd_lastname':
                        if(strlen($postValue) < 3) {
                            $this->view->hasError[$postElement] = '"Numele" trebuie sa contina cel putin 3 caractere.';
                        } else {
                            $clientDetails->result->lastname = $postValue;
                        }
                        break;

                    case 'upd_email':
                        if(!filter_var($postValue, FILTER_VALIDATE_EMAIL)) {
                            $this->view->hasError[$postElement] = '"Email" trebuie sa fie o adresa valida.';
                        } else {
                            $clientDetails->result->email = $postValue;
                        }
                        break; 

                    case 'upd_dob':
                        if(!$this->validateDate($postValue)) {
                            $this->view->hasError[$postElement] = '"Data nasterii" trebuie sa fie o data valida de forma AAAA-LL-ZZ.';
                        } else {
                            $clientDetails->result->dob = $postValue;
                        }
                        break;


                    case 'upd_gender':
                        if(!in_array($postValue, [1,2,3])) {
                            $this->view->hasError[$postElement] = '"Genul" trebuie sa aibe o valoare prestabilita.';
                        } else {
                            $clientDetails->result->gender = $postValue;
                        }
                        break; 


                    default:
                        // just stop.
                        break;
                }                
            }

            if(count($this->view->hasError) < 1) {

                try {
                    //$clientDetails->result->email = 'ha24@\'323@g.com.'; // Testeaza o eventuala eroare SOAP
                    $saveCustomerData = $this->soapClient->customerCustomerRepositoryV1('Save', ['customer' => $clientDetails->result]);
                    $this->_flashMessenger->addMessage('Datele au fost modificate cu success', 'SUCCESS');

                    $historyData = ['customer_id' => md5($this->soapUri).'_'.$this->_getParam('customerId'), 'payload' => serialize($this->getRequest()->getPost()), 'createdAt' => date('Y-m-d H:i:s')];
                    $this->db->insert('change_history', $historyData);


                    $this->getResponse()->setRedirect('/customers/list');

                } catch (SoapFault $e) {
                    $this->view->soapFault = $e->getMessage();
                }

                
            }
            
            //die(var_dump($this->soapClient->getList('customerCustomerRepositoryV1')));

        }
        
    }


    public function historyAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);

        if($this->getRequest()->isXmlHttpRequest()) {
            $historyData = $this->db->query("SELECT * FROM `change_history` WHERE `id` = '".(int)$this->_getParam('historyId')."'")->fetch();

            if(count($historyData) > 0) {
                echo Zend_Json::encode(unserialize($historyData['payload']));
            } else {
                die('hahaha');
                echo Zend_Json::encode(['false']);
            }
            exit();
        } else {
            $this->getResponse()->setHttpResponseCode(404);
            die('invalid request...');
        }
    }


    private function validateDate($date, $format = 'Y-m-d')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }



}

