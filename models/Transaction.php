<?php
	class Transaction extends ActiveRecord\Model{
		
		static $validates_presence_of = array(
			array('description', 'message'=>'campo obbligatorio'),
			array('import', 'message'=>'campo obbligatorio')
		);
		
		static $validates_numericality_of = array(
			array('import')
		);
		
	}
?>