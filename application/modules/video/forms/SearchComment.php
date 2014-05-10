<?php 
class Samx_Form_SearchComment extends Cl_Form_SearchCommentNode
{

	public function init()
	{
        parent::init();
		if (method_exists($this, "_customFormFieldsConfig"))
			$this->_formFieldsConfig = array_merge($this->_formFieldsConfig(), $this->_customFormFieldsConfig());
	
		$this->fieldList = array('status', 'is_spam');
    	$this->setCbHelper('Samx_Form_Helper');
	}
	public function setStep($step, $currentRow = null)
	{
	    if ($step == '')
	    {
    		$this->fieldList = array(
                'status', 'is_spam'
        	);
	        
	    }
	    parent::setStep($step, $currentRow);
	}
	
    protected function _customFormFieldsConfig()
    {
        return array(
        );
    }
    
}
