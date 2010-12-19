<?php
	class Transaction extends ActiveRecord\Model{
		
		static $validates_presence_of = array(
			array('description', 'message'=>'campo obbligatorio'),
			array('import', 'message'=>'campo obbligatorio'),
			array('category_id', 'message'=>'campo obbligatorio'),
			array('account_id', 'message'=>'campo obbligatorio'),
			array('date', 'message'=>'campo obbligatorio')
		);
		
		static $validates_numericality_of = array(
			array('import')
		);
		
		static $belongs_to = array(
			array('category'),
			array('account')
		);
		
		static $has_many = array(
			array('transactiontags'),
			array('tags', 'through' => 'transactiontags')
		);
		
	}
?>