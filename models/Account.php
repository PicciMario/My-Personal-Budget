<?php
	class Account extends ActiveRecord\Model{
		
		static $has_many = array(
			array('transactions')
		);
		
	}
?>