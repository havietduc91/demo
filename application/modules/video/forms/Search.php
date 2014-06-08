<?php 
class Video_Form_Search extends Cl_Form_Search
{

	public function init()
	{
		parent::init();
		$this->method= "GET";
		$this->fieldList = array('name', 'status', 'ac_name',
				'order_ts','iid',
		);
    	$this->setCbHelper('Video_Form_Helper');
    	//$this->setDisplayInline();
	}
	public function setStep($step, $currentRow = null)
	{
	    parent::setStep($step, $currentRow);
	}
	//protected $_fieldListConfig; @see Cl_Dao_Foo
	
	
    //we must have it here as separate from $_fieldListConfig
    //because some configs will be merged with another file
    protected function _formFieldsConfig(){
		return array(    	
	    	'status' => array(
	    		'type' => 'MultiCheckbox', /* 'MultiCheckbox', */
	    		'options' => array(
					'label' => "",
	    			'disableLoadDefaultDecorators' => true,
	//    			'required' => true,
		    		'filters' => array('StringTrim', 'StripTags'),
	    		),
	    		'op' => '$in',
	    		'multiOptionsCallback' => array(array('Video_Form_Helper', 'getStatus')),
	    		'defaultValue' => array()
	    	),
	    	'iid' => array(
	    		'type' => 'Text',
	    		'options' => array(
	    			'label' => "Story Id",
	    			'filters' => array('StringTrim', 'StripTags')
	    		),
	    		'op' => '$eq',
	    	),
	    	'order_ts' => $this->generateFormElement('_cl_order', 'Order by Create time', 1),
	    	'name' => array(
	    		'type' => 'Text',
	    		'options' => array(
	    			'label' => "Video name",
		    		'filters' => array('StringTrim', 'StripTags')
	    		),
	    		'op' => '$like',
	    	),
	    	'ac_name' => array(
	    		'type' => 'Text',
	    		'options' => array(
	    			'label' => "Accent VN name",
	    			'filters' => array('StringTrim', 'StripTags')
	    		),
	    		'op' => '$like',
	    	),
			'items_per_page' => array(
	        		'type' => 'Select', 
	        		'options' => array(
	    				'label' => "Display",
	        			'disableLoadDefaultDecorators' => false,
	        			'required' => true,
	    	    		'filters' => array('StringTrim', 'StripTags')
	        		),
	        		//or you can implement getItemsPerPageList here
	        		//'multiOptions' => array('getItemsPerPageList'),
	        		'multiOptions' => array(
			    	    '-1' => "All",
	            		'10' => "10/page",
	            		'20' => "20/page",
	            		'30' => "30/page",	
	            		'50' => "50/page"
	        		),
	        		'defaultValue' => 10
	    	),    	
	    	'order_by_count' => array(
	    		'type' => 'Select',
	    		'options' => array(
	    			'label' => "order_by_count",
	    			'required' => true,
		    		'filters' => array('StringTrim', 'StripTags'),
	    		),
	    		'op' => '$eq',
	    		'multiOptions' => array(
	    			'ts' => 'created time',
	    			'counter.c' => "comment count",
	    		),
	    	),
		);
    }
}
