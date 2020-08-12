<?php
namespace picklesFramework2\px2agent;

/**
 * px2project.php
 */
class px2project{
	private $main;
	private $path_entry_script;
	private $options;

	/**
	 * Constructor
	 */
	public function __construct($main, $path_entry_script, $options){
		$this->main = $main;
		$this->path_entry_script = $path_entry_script;
		$this->options = (array) $options;
	}


	/**
	 * Pickles 2 にクエリを投げて、結果を受け取る (汎用)
	 */
	public function query($request_path, $options = null, &$return_var = null){
		$path_cmd_php = 'php';
		if( array_key_exists('bin', $this->options) && strlen($this->options['bin']) ){
			$path_cmd_php = $this->options['bin'];
		}
		$path_cmd_php_ini = null;
		if( array_key_exists('ini', $this->options) && strlen($this->options['ini']) ){
			$path_cmd_php_ini = $this->options['ini'];
		}
		$path_extension_dir = null;
		if( array_key_exists('extension_dir', $this->options) && strlen($this->options['extension_dir']) ){
			$path_extension_dir = $this->options['extension_dir'];
		}

		$current_dir = realpath('.');
		$project_dir = dirname($this->path_entry_script);


		if(!is_string($request_path)){
			$this->main->error('Invalid argument supplied for 1st option $request_path in $px2project->query(). It required String value.');
			return false;
		}
		if(!strlen($request_path)){ $request_path = '/'; }
		if(is_null($options)){ $options = array(); }
		if(!is_array($options)){ $options = (array) $options; }
		$php_command = array();
		array_push( $php_command, addslashes($path_cmd_php) );
			// ↑ Windows でこれを `escapeshellarg()` でエスケープすると、なぜかエラーに。

		if( strlen($path_cmd_php_ini) ){
			$php_command = array_merge(
				$php_command,
				array(
					'-c', escapeshellarg($path_cmd_php_ini),// ← php.ini のパス
				)
			);
		}

		if( strlen($path_extension_dir) ){
			$php_command = array_merge(
				$php_command,
				array(
					'-d', escapeshellarg($path_extension_dir),// ← php.ini definition
				)
			);
		}

		array_push($php_command, escapeshellarg( realpath($this->path_entry_script) ));
		if( array_key_exists('output', $options) && $options['output'] == 'json' ){
			array_push($php_command, '-o');
			array_push($php_command, 'json');
		}
		if( array_key_exists('user_agent', $options) && strlen($options['user_agent']) ){
			array_push($php_command, '-u');
			array_push($php_command, escapeshellarg($options['user_agent']));
		}
		array_push($php_command, escapeshellarg($request_path));


		$cmd = implode( ' ', $php_command );

		// コマンドを実行
		chdir($project_dir);
		ob_start();
		$proc = proc_open($cmd, array(
			0 => array('pipe','r'),
			1 => array('pipe','w'),
			2 => array('pipe','w'),
		), $pipes);
		$io = array();
		foreach($pipes as $idx=>$pipe){
			$io[$idx] = null;
			if( $idx >= 1 ){
				$io[$idx] = stream_get_contents($pipe);
			}
			fclose($pipe);
		}
		$return_var = proc_close($proc);
		ob_get_clean();

		$bin = $io[1]; // stdout
		if( strlen( $io[2] ) ){
			// $this->error($io[2]); // stderr
		}

		if( array_key_exists('output', $options) && $options['output'] == 'json' ){
			$bin = json_decode($bin);
		}

		chdir($current_dir);
		return $bin;

	}

	/**
	 * PX=api.*を投げる
	 */
	protected function apiGet($cmd, $path = '/', $param = array()){
		if( !strlen($path) ){
			$path = '/';
		}
		if( !$param ){
			$param = array();
		}
		$param = (function($param){
			$aryParam = array();
			foreach( $param as $idx=>$row ){
				array_push($aryParam, urlencode($idx).'='.urlencode($param[$idx]) );
			}
			if(!count($aryParam)){return '';}
			return '&'+implode('&', $aryParam);
		})($param);

		$errorMsg = null;
		$rtn = $this->query(
			$path.'?PX='.$cmd.$param ,
			array(
				"output" => "json",
			)
		);
		return $rtn;
	}

	/**
	 * PXコマンドを実行する
	 */
	public function px_command($cmd, $path, $param){
		return $this->apiGet($cmd, $path, $param);
	}

	/**
	 * バージョン番号を取得する
	 */
	public function get_version(){
		return $this->apiGet('api.get.version', '/', array());
	}


	/**
	 * configデータを取得する
	 */
	public function get_config(){
		return $this->apiGet('api.get.config', '/', array());
	}

	/**
	 * サイトマップデータを取得する
	 */
	public function get_sitemap(){
		return $this->apiGet('api.get.sitemap', '/', array());
	}

	/**
	 * pathまたはidからページ情報を得る
	 */
	public function get_page_info($path){
		return $this->apiGet('api.get.page_info', '/', array(
			"path" => $path
		));
	}

	/**
	 * 親ページのIDを取得する
	 */
	public function get_parent($path){
		return $this->apiGet('api.get.parent', $path, array());
	}

	/**
	 * 子階層のページの一覧を取得する
	 */
	public function get_children($path, $options = array()){
		if(is_array($options)){
			$options = $this->sitemap_children_params($options);
		}
		return $this->apiGet('api.get.children', $path, $options);
	}

	/**
	 * 兄弟ページの一覧を取得する
	 */
	public function get_bros($path, $options = array()){
		if(is_array($options)){
			$options = $this->sitemap_children_params($options);
		}
		return $this->apiGet('api.get.bros', $path, $options);
	}

	/**
	 * 次の兄弟ページを取得する
	 */
	public function get_bros_next($path, $options = array()){
		if(is_array($options)){
			$options = $this->sitemap_children_params($options);
		}
		return $this->apiGet('api.get.bros_next', $path, $options);
	}

	/**
	 * 前の兄弟ページを取得する
	 */
	public function get_bros_prev($path, $options = array()){
		if(is_array($options)){
			$options = $this->sitemap_children_params($options);
		}
		return $this->apiGet('api.get.bros_prev', $path, $options);
	}

	/**
	 * 次のページを取得する
	 */
	public function get_next($path, $options = array()){
		if(is_array($options)){
			$options = $this->sitemap_children_params($options);
		}
		return $this->apiGet('api.get.next', $path, $options);
	}

	/**
	 * 前のページを取得する
	 */
	public function get_prev($path, $options = array()){
		if(is_array($options)){
			$options = $this->sitemap_children_params($options);
		}
		return $this->apiGet('api.get.prev', $path, $options);
	}

	/**
	 * パンくず配列を取得する
	 */
	public function get_breadcrumb_array($path){
		return $this->apiGet('api.get.breadcrumb_array', $path, array());
	}

	/**
	 * ダイナミックパス情報を得る
	 */
	public function get_dynamic_path_info($path){
		return $this->apiGet('api.get.dynamic_path_info', '/', array(
			"path"=>$path,
		));
	}

	/**
	 * ダイナミックパスに値をバインドする
	 */
	public function bind_dynamic_path_param($path, $param){
		return $this->apiGet('api.get.bind_dynamic_path_param', '/', array(
			"path" => $path,
			"param" => json_encode($param),
		));
	}

	/**
	 * role を取得する
	 */
	public function get_role(F$path){
		return $this->apiGet('api.get.role', $path, array());
	}

	/**
	 * Actor のページID一覧を取得する
	 */
	public function get_actors($path){
		return $this->apiGet('api.get.actors', $path, array());
	}

	/**
	 * get home directory path (deprecated)
	 *
	 * `get_path_homedir()` は 非推奨のメソッドです。
	 * 代わりに、 `get_realpath_homedir()` を使用してください。
	 */
	public function get_path_homedir(){
		return $this->apiGet('api.get.path_homedir', '/', array());
	}

	/**
	 * get home directory path
	 */
	public function get_realpath_homedir(){
		return $this->apiGet('api.get.path_homedir', '/', array());
	}

	/**
	 * コンテンツルートディレクトリのパス(=install path) を取得する
	 */
	public function get_path_controot(){
		return $this->apiGet('api.get.path_controot', '/', array());
	}

	/**
	 * DOCUMENT_ROOT のパスを取得する (deprecated)
	 *
	 * `get_path_docroot()` は 非推奨のメソッドです。
	 * 代わりに、 `get_realpath_docroot()` を使用してください。
	 */
	public function get_path_docroot(){
		return $this->apiGet('api.get.path_docroot', '/', array());
	}

	/**
	 * DOCUMENT_ROOT のパスを取得する
	 */
	public function get_realpath_docroot(){
		return $this->apiGet('api.get.path_docroot', '/', array());
	}

	/**
	 * get content path
	 */
	public function get_path_content($path){
		return $this->apiGet('api.get.path_content', $path, array());
	}

	/**
	 * ローカルリソースディレクトリのパスを得る
	 */
	public function path_files($path, $path_resource = null){
		if (!strlen($path_resource)) { 
			$path_resource = '';
		}
		return $this->apiGet('api.get.path_files', $path, array(
			"path_resource"=>$path_resource,
		));
	}

	/**
	 * ローカルリソースディレクトリのサーバー内部パスを得る
	 */
	public function realpath_files($path, $path_resource = null){
		if (!strlen($path_resource)) { 
			$path_resource = '';
		}
		return $this->apiGet('api.get.realpath_files', $path, array(
			"path_resource"=>$path_resource,
		));
	}

	/**
	 * ローカルリソースのキャッシュディレクトリのパスを得る
	 */
	public function path_files_cache($path, $path_resource = null){
		if (!strlen($path_resource)) { 
			$path_resource = '';
		}
		return $this->apiGet('api.get.path_files_cache', $path, array(
			"path_resource"=>$path_resource,
		));
	}

	/**
	 * ローカルリソースのキャッシュディレクトリのサーバー内部パスを得る
	 */
	public function realpath_files_cache($path, $path_resource = null){
		if (!strlen($path_resource)) { 
			$path_resource = '';
		}
		return $this->apiGet('api.get.realpath_files_cache', $path, array(
			"path_resource"=>$path_resource,
		));
	}

	/**
	 * コンテンツ別の非公開キャッシュディレクトリのサーバー内部パスを得る
	 */
	public function realpath_files_private_cache($path, $path_resource = null){
		if (!strlen($path_resource)) { 
			$path_resource = '';
		}
		return $this->apiGet('api.get.realpath_files_private_cache', $path, array(
			"path_resource"=>$path_resource,
		));
	}

	/**
	 * domain を取得する
	 */
	public function get_domain(){
		return $this->apiGet('api.get.domain', '/', array());
	}

	/**
	 * directory_index(省略できるファイル名) の一覧を得る
	 */
	public function get_directory_index(){
		return $this->apiGet('api.get.directory_index', '/', array());
	}

	/**
	 * 最も優先されるインデックスファイル名を得る
	 */
	public function get_directory_index_primary(){
		return $this->apiGet('api.get.directory_index_primary', '/', array());
	}

	/**
	 * ファイルの処理方法を調べる
	 */
	public function get_path_proc_type($path){
		return $this->apiGet('api.get.path_proc_type', $path, array());
	}

	/**
	 * リンク先のパスを生成する
	 */
	public function href($path_linkto){
		return $this->apiGet('api.get.href', '/', array(
			"linkto"=>$path_linkto,
		));
	}

	/**
	 * パスがダイナミックパスにマッチするか調べる
	 */
	public function is_match_dynamic_path($path){
		return $this->apiGet('api.is.match_dynamic_path', '/', array(
			"path"=>$path,
		));
	}

	/**
	 * ページが、パンくず内に存在しているか調べる
	 */
	public function is_page_in_breadcrumb($path, $path_in){
		return $this->apiGet('api.is.page_in_breadcrumb', $path, array(
			"path"=>$path_in,
		));
	}

	/**
	 * 除外ファイルか調べる
	 */
	public function is_ignore_path($path){
		return $this->apiGet('api.is.ignore_path', '/', array(
			"path"=>$path,
		));
	}


	/**
	 * パブリッシュする
	 */
	public function publish($opt = array()){
		if( !is_array($opt) ){
			$opt = array();
		}

		// path_region
		if( !$opt['path_region'] ){
			$opt['path_region'] = '';
		}

		// paths_region
		$str_paths_region = '';
		if( array_key_exists('paths_region', $opt) && is_string($opt['paths_region']) ){
			$opt['paths_region'] = array($opt['paths_region']);
		}
		if( array_key_exists('paths_region', $opt) && is_array($opt['paths_region']) ){
			foreach($opt['paths_region'] as $i=>$row){
				$str_paths_region .= '&paths_region[]='.urlencode($row);
			}
		}

		// paths_ignore
		$str_paths_ignore = '';
		if( array_key_exists('paths_ignore', $opt) && is_string($opt['paths_ignore']) ){
			$opt['paths_ignore'] = array($opt['paths_ignore']);
		}
		if( array_key_exists('paths_ignore', $opt) && is_array($opt['paths_ignore']) ){
			foreach($opt['paths_ignore'] as $i=>$row){
				$str_paths_ignore .= '&paths_ignore[]='.urlencode($row);
			}
		}

		// keep_cache
		$str_keep_cache = '';
		if( $opt['keep_cache'] ){
			$str_keep_cache = '&keep_cache=1';
		}

		return $this->query(
			'/?PX=publish.run&path_region=' . urlencode($opt['path_region']) . $str_paths_ignore . $str_paths_region . $str_keep_cache,
			$opt
		);
	}

	/**
	 * キャッシュを削除する
	 */
	public function clearcache($opt = array()){
		if( !is_array($opt) ){
			$opt = array();
		}

		return $this->query(
			'/?PX=clearcache' ,
			$opt
		);
	}


	/**
	 * get_children() へ渡されるオプションを調整する
	 * この形式のオプションは、get_bros(), get_bros_next(), get_bros_prev(), get_next(), get_prev() でも共通です。
	 */
	private function sitemap_children_params($options = array()){
		$options = (array) $options;
		$filter = null;
		if( array_key_exists('filter', $options) ){
			$filter = $options['filter'];
		}

		if(is_null($filter)){
			$filter = null;
		}elseif(is_string($filter)){
			switch( $filter ){
				case 'true':
				case '1':
					$filter = 'true'; break;
				case 'false':
				case '0':
					$filter = 'false'; break;
			}
		}else{
			$filter = ($filter?'true':'false');
		}

		$rtn = array();
		$rtn['filter'] = $filter;
		return $rtn;
	}

}
