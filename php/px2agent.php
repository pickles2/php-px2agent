<?php
namespace picklesFramework2\px2agent;

/**
 * px2agent.php
 */
class px2agent{
	private $options;
	private $error_list = array();

	/**
	 * Constructor
	 */
	public function __construct($options = array()){
		$this->options = (array) $options;
	}

	/**
	 * Create project object
	 */
	public function createProject( $php_self ){
		$px2project = new px2project($this, $php_self, $this->options);
		return $px2project;
	}

	/**
	 * エラーを記録する
	 */
	public function error( $error_msg ){
		array_push($this->error_list, $error_msg);
		return;
	}
}
