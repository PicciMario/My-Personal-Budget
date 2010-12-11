<?php
	class Category extends ActiveRecord\Model{
		
		static $validates_presence_of = array(
			array('description', 'message'=>'campo obbligatorio'),
			array('name', 'message'=>'campo obbligatorio'),
			array('user_id', 'message'=>'campo obbligatorio')
		);		
		
		static $belongs_to = array(
			array('user')
		);
		
	}
?>