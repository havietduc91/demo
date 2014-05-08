<?php
class Controller_Plugin extends  Cl_Controller_Plugin
{
	public function dispatchLoopShutdown()
	{
		parent::dispatchLoopShutdown();
		//DO something with $content = $this->getResponse()->getBody(); if you like. For example cache it here
	}
}