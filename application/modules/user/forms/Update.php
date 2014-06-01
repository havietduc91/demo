<?php 
class User_Form_Update extends Cl_Form_User_Update
{
    public function setStep($step, $currentRow = null) {
    	if($step == 'account'){
    		$this->fieldList = array('name', 'mail', 'lname', 'ustatus');
    	}
    	else if($step == 'background')
    		$this->fieldList = array('background');
    	else 
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
        	'name' => array(
        		'type' => 'Text',
        		'options' => array(
        			'label' => 'Tên hiển thị',
        			'class' => 'user-name',
        		    'placeholder' => 'John Smith',
        			//'required' => true,
    	    		'filters' => array('StringTrim', 'StripTags'),
            		'validators' => array (
            				'NotEmpty' ,
            				array('StringLength', false, array(0, 30))
            		)
        		),
        	),
        	'lname' => array(
        		'type' => 'Text',
        		'options' => array(
        			'label' => 'Tên đăng nhập',
        			'class' => 'user-name',
        		    'placeholder' => 'john.doe@example.com',
        			//'required' => true,
    	    		'filters' => array('StringTrim', 'StripTags')
        		),
        	),
        	'ustatus' => array(
        		'type' => 'Textarea',
        		'options' => array(
        			'label' => 'Bạn đang nghĩ gì?',
        			'class' => 'isEditor',
        			'placeholder' => 'Vui quá :)',
        			//'required' => true,
        			'filters' => array('StringTrim', 'StripTags')
        		),
        	),
        	'mail' => array(
        		'type' => 'Text',
        		'options' => array(
        			'label' => 'Email',
        			//'order' => 2,
        			'required' => true,
        			'filters' => array('StringTrim', 'StripTags'),
        			'validators' => array(
        				array(
        					'validator'   => 'Regex',
        					'breakChainOnFailure' => false,
        					'options'     => array(
        						'pattern' => '/^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)+$/',
        						'messages' => array(
        							Zend_Validate_Regex::NOT_MATCH =>t('email_is_not_valid',1)
        						)
        					)
        				),
        				array(
	        				'validator' => 'StringLength',
	        				'options' => array(
	        					'min' => 6
	        				)
        				)
        			),
        		),
        	),
        	'background' => array(
        		'type' => 'Hidden',
        		'options' => array(
        			'class' => 'cl_upload',
        			//TODO:
        			'type' => 'avatar_image',
        			'attribs' => array('cl_upload_text' => 'Avatar')
        		)
        	),
    	);
    }
}
