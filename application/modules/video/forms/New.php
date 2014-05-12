<?php 
class Video_Form_New extends Cl_Form
{
	public function init()
	{
		parent::init();
		$this->fieldList = array(/*'avatar',*/'name', 'content', 'status', 'url', 'tags', 'is_original');
		$this->setCbHelper('Video_Form_Helper');
		
	}
	public function setStep($step, $currentRow = null)
	{
		parent::setStep($step, $currentRow);
	}
	
    protected function _formFieldsConfigCallback()
    {
        $ret = array(
        	'name' => array(
        		'type' => 'Text',
        		'options' => array(
        			'label' => "Tên Video",
        			'required' => true,
    	    		'filters' => array('StringTrim', 'StripTags'),
                    'validators' => array('NotEmpty'),
        		),
                //'permission' => 'update_task'
        	),
        	'tags' => $this->generateFormElement('tags', 'Tags','', array(
        		'data-url' => '/suggest.php?node=tag&addnew=1',
        		'permission' => 'new_tag'
        	)),
        	'content' => array(
        		'type' => 'Textarea',
        		'options' => array(
        	        'label' => "Nội dung video",
        	        'class' => 'isEditor',
    	    		'filters' => array('StringTrim', 'NodePost'),
        			'prefixPath' => array(
        				"filter" => array (
        					"Filter" => "Filter/"
        				)
        			)
        		),
        	),
            'status' => array(
            		'type' => 'Select',
            		'options' => array(
            				'label' => 'Trạng thái',
            				'required' => true,
            		),
            		'multiOptionsCallback' => array('getStatus')
            ),
        	'is_original' => array(
        		'type' => 'Select',
        		'options' => array(
        			'label' => 'Bản gốc?',
        			'required' => true,
        		),
        		'multiOptionsCallback' => array('getOriginal')
        	),
        	'avatar' => array(
        			'type' => 'Hidden',
        			'options' => array(
        					'class' => 'cl_upload',
        					'filters' => array('StringTrim', 'StripTags')
        			),
        	),
        	'url' => array(
        		'type' => 'Text',
        		'options' => array(
        			'label' => 'Url',
        			'placeholder' => "E.g http://www.youtube.com/watch?v=fkm7eMqiuSw",
        			'required' => true,
        			'filters' => array('StringTrim', 'StripTags'),
        			'validators' => array('NotEmpty'),
        		),
        	),
        );
        return $ret;
    }
    /**TODO: hook here if needed
    public function customIsValid($data)
    {
        return array('success' => false, 'err' => "If customIsValid exist. You must implement it");
    }
    */
}
