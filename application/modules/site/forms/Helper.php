<?php
class Site_Form_Helper extends Cl_Form_NodeHelper
{
    //other site's modules can extend this helper
	
	public function getItemsPerPageList($params = NULL)
	{
		$ret = array(
		        '-1' => "All",
				'10' => 10,
				'20' => 20,
				'30' => 30,
				'50' => 50,
		);
		return array('success' => true, 'result' => $ret);
	}
	
}