<?php
class Filter_StoryPost implements Zend_Filter_Interface
{
	public function filter($value)
	{
	    //TODO: white list tags here. Remove all link .cl_ajax links
	    // whitelisted: a,span,b,i
	    //$whitelist = '<p><a><span><b><i>';
        require_once (SAND_LIBRARY_PATH . '/htmlpurifier/library/HTMLPurifier.auto.php');
        
        $options = array(
            //array('Cache.SerializerPath', $dir),
            //array('HTML.Doctype', 'XHTML 1.0 Strict'),
            //array('HTML.Allowed','p,em,strong,b,i,a[href],img[src|alt|height|width|style],br,span[class],div[class],div[style]')
            array('HTML.Allowed','p,em,strong,b,i,br,span[class],div[class],div[style]')
            //array('HTML.Allowed','em,strong,b,i,span[class],div[class],div[style]')
        );
        $config = HTMLPurifier_Config::createDefault();
	    
        foreach ($options as $option) {
            $config->set($option[0], $option[1]);
        }
        
        $purifier = new HTMLPurifier($config);
        
        $cleaned = $purifier->purify($value);
        
		return $cleaned;
	}
}