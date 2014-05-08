<?php 
class User_Form_Update extends Cl_Form_User_Update
{
    public function setStep($step, $currentRow = null) {
        $this->fieldList = $this->getFieldList($step);
    	parent::setStep($step, $currentRow);
    }
        
    public function init()
    {
    	parent::init();
    	$this->setCbHelper("User_Form_Helper");
    	if (method_exists($this, "_customFormFieldsConfig"))
    		$this->_formFieldsConfig = array_merge($this->_formFieldsConfig, $this->_customFormFieldsConfig());
    	 
    }
    
    protected function _customFormFieldsConfig()
    { 
        return array(
        /*
	    	'verify' => array(
	    		'type' => 'Text',
	    		'options' => array(
	    			'label' => "Verify",
	    			'class' => 'user-name',
	    			//'required' => true,
		    		'filters' => array('StringTrim', 'StripTags')
	    		),
	    	),
	    	*/
    	);
    }
}
