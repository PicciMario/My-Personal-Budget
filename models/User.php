<?php
	class User extends ActiveRecord\Model{
		
		static $validates_presence_of = array(
			array('username', 'message'=>'campo obbligatorio'),
			array('password', 'message'=>'campo obbligatorio'),
			array('userlevel', 'message'=>'campo obbligatorio'),
			array('email', 'message'=>'campo obbligatorio')
		);

		static $validates_uniqueness_of = array(
			array('username', 'message'=>'duplicato')
		);
		
		static $has_many = array(
			array('accounts')
		);
		
	}
?>