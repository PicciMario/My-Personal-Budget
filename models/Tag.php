<?php
	class Tag extends ActiveRecord\Model{
		
		static $validates_presence_of = array(
			array('name', 'message'=>'campo obbligatorio'),
			array('user_id', 'message'=>'campo obbligatorio')
		);
		
		static $has_one = array(
			array('user')
		);

		static $has_many = array(
			array('transactions')
		);
		
	}
?>