<?php
	class Transactiontag extends ActiveRecord\Model{
		
		static $validates_presence_of = array(
			array('transaction_id', 'message'=>'campo obbligatorio'),
			array('tag_id', 'message'=>'campo obbligatorio')
		);
		
		static $belongs_to = array(
			array('transaction'),
			array('tag')
		);
		
	}
?>