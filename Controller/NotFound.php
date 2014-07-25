<?php
class NotFoundController extends Controller{
	public function __construct(){
		array_push($this->messages, array(
				'type' => 'danger',
				'notice' => 'Controller not found!'
			)
		);
		$this->render('404');
	}
}