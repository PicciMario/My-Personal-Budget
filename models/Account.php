<?php
	class Account extends ActiveRecord\Model{
		
		static $has_many = array(
			array('transactions')
		);
		
		static $validates_presence_of = array(
			array('description', 'message'=>'campo obbligatorio'),
			array('user_id', 'message'=>'campo obbligatorio')
		);	
		
	}
?>