<?php 
class Samx_Form_Comment extends Cl_Form_CommentNode
{
    public $nodeType = "samx";

    /*
    public $attributes = array(
    		'id' => "comment_form",
    		'method' => "POST",
    		'action' => "/samx/comment",
    		'class' => "cl_ajax",
            'new_url' => "/samx/comment",
            'update_url' => "/samx/update-comment"
    );
    */
    
	public function init()
	{
		parent::init();
		if (method_exists($this, "_customFormFieldsConfig"))
			$this->_formFieldsConfig = array_merge($this->_formFieldsConfig(), $this->_customFormFieldsConfig());
	
		$this->fieldList = array('content', 'nid', 'pid', 'id'/*, 'attachments'*/);
	}
	
	public function setStep($step, $currentRow = null)
	{
	    if ($step == 'update')
	        $this->fieldList = array('content', 'nid', 'id');
        elseif($step == 'status')
            $this->fieldList = array('status', 'nid', 'id');
        elseif($step == 'is_spam')
            $this->fieldList = array('nid', 'id');
	    parent::setStep($step, $currentRow);
	}
	
    protected function _customFormFieldsConfig()
    {
        return array(
        	'content' => array(
        		'type' => 'Textarea',
        		'options' => array(
                    'required' => true,
        		    'label' => "(Standard shortcuts are similar to Microsoft Office: Ctrl + B for bold text,etc...)",
    	    		'filters' => array('StringTrim', 'NodePost'), //TODO: filter user input here. use Purifier
    	    		'class' => 'isEditor', 
        			'prefixPath' => array(
        				"filter" => array (
        					"Filter" => "Filter/"
        				)
        			)
        		),
        	),
            'attachments' =>  array(
        		'type' => 'Hidden',
        		'options' => array(
                    'transformers' => array("tokensJSONStringToArray")
                ) 
        	),
            'status' => array(
                'type' => 'Hidden'
            )
        );
    }
    
     public function customPermissionFilter($data, $currentRow)
     {
         if ($this->step == 'update')
         {
             //check permission for update
             $lu = Zend_Registry::get('user');
             if ($lu['id'] != $currentRow['u']['id'])
                 return array('success' => false, 'err' => "You are not owner of the comment");
         }
         return array('success' => true);
     }
}