<?php 
class User_Form_Register extends Cl_Form_User_Register
{
    public function init()
    {
        parent::init();        
        $this->fieldList = array('mail', 'name','pass');
    }
}
