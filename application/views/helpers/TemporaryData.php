<?php 
class Zend_View_Helper_TemporaryData extends Zend_View_Helper_Abstract
{    


    public function temporaryData($postData, $fallback)
    {
    	$getInstance = Zend_Controller_Front::getInstance();
    	
        if($getInstance->getRequest()->getPost($postData)) {
            return $getInstance->getRequest()->getPost($postData);
        } else {
            return $fallback;
        }
    }

}
 ?>