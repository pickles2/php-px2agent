<?php
class test_helper_factory{

	public function getProject( $testDataName ){
		$options = array(
			"bin" => 'php' ,
			"ini" => null ,
			"extension_dir" => null
		);
		// var_dump($options);
		$px2agent = new picklesFramework2\px2agent\px2agent();
		$px2proj = $px2agent->createProject(__DIR__.'/../testData/'.urlencode($testDataName).'/.px_execute.php');
		return $px2proj;
	}

}
