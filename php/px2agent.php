<?php
namespace picklesFramework2\px2agent;

/**
 * px2agent.php
 */
class px2agent{
	private $options;

	/**
	 * Constructor
	 */
	public function __construct($options = array()){
		$this->options = (array) $options;
	}

	/**
	 * Create project object
	 */
	public function createProject(){
		$px2project = new px2project($this, $this->options);
		return $px2project;
	}	
}
