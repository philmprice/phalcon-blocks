<?php

class IndexController extends BaseController
{
    public function indexAction()
    {
		//	send debug vars to view
		$this->debugToView();

		$this->view->foo = Module\Pages\Foo::bar();
    }
}