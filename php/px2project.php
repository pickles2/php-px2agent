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


	// /**
	//  * configデータを取得する
	//  */
	// this.get_config = function(cb){
	// 	return apiGet('api.get.config', '/', {}, cb);
	// }

	// /**
	//  * サイトマップデータを取得する
	//  */
	// this.get_sitemap = function(cb){
	// 	return apiGet('api.get.sitemap', '/', {}, cb);
	// }

	// /**
	//  * pathまたはidからページ情報を得る
	//  */
	// this.get_page_info = function(path, cb){
	// 	return apiGet('api.get.page_info', '/', {
	// 		"path":path
	// 	}, cb);
	// }

	// /**
	//  * 親ページのIDを取得する
	//  */
	// this.get_parent = function(path, cb){
	// 	return apiGet('api.get.parent', path, {}, cb);
	// }

	// /**
	//  * 子階層のページの一覧を取得する
	//  */
	// this.get_children = function(path, cb){
	// 	var options = {};
	// 	if(arguments.length >= 3){
	// 		options = sitemap_children_params(arguments[1]);
	// 		cb = arguments[2];
	// 	}
	// 	return apiGet('api.get.children', path, options, cb);
	// }

	// /**
	//  * 兄弟ページの一覧を取得する
	//  */
	// this.get_bros = function(path, cb){
	// 	var options = {};
	// 	if(arguments.length >= 3){
	// 		options = sitemap_children_params(arguments[1]);
	// 		cb = arguments[2];
	// 	}
	// 	return apiGet('api.get.bros', path, options, cb);
	// }

	// /**
	//  * 次の兄弟ページを取得する
	//  */
	// this.get_bros_next = function(path, cb){
	// 	var options = {};
	// 	if(arguments.length >= 3){
	// 		options = sitemap_children_params(arguments[1]);
	// 		cb = arguments[2];
	// 	}
	// 	return apiGet('api.get.bros_next', path, options, cb);
	// }

	// /**
	//  * 前の兄弟ページを取得する
	//  */
	// this.get_bros_prev = function(path, cb){
	// 	var options = {};
	// 	if(arguments.length >= 3){
	// 		options = sitemap_children_params(arguments[1]);
	// 		cb = arguments[2];
	// 	}
	// 	return apiGet('api.get.bros_prev', path, options, cb);
	// }

	// /**
	//  * 次のページを取得する
	//  */
	// this.get_next = function(path, cb){
	// 	var options = {};
	// 	if(arguments.length >= 3){
	// 		options = sitemap_children_params(arguments[1]);
	// 		cb = arguments[2];
	// 	}
	// 	return apiGet('api.get.next', path, options, cb);
	// }

	// /**
	//  * 前のページを取得する
	//  */
	// this.get_prev = function(path, cb){
	// 	var options = {};
	// 	if(arguments.length >= 3){
	// 		options = sitemap_children_params(arguments[1]);
	// 		cb = arguments[2];
	// 	}
	// 	return apiGet('api.get.prev', path, options, cb);
	// }

	// /**
	//  * パンくず配列を取得する
	//  */
	// this.get_breadcrumb_array = function(path, cb){
	// 	return apiGet('api.get.breadcrumb_array', path, {}, cb);
	// }

	// /**
	//  * ダイナミックパス情報を得る
	//  */
	// this.get_dynamic_path_info = function(path, cb){
	// 	return apiGet('api.get.dynamic_path_info', '/', {
	// 		"path":path
	// 	}, cb);
	// }

	// /**
	//  * ダイナミックパスに値をバインドする
	//  */
	// this.bind_dynamic_path_param = function(path, param, cb){
	// 	return apiGet('api.get.bind_dynamic_path_param', '/', {
	// 		"path":path,
	// 		"param":JSON.stringify(param)
	// 	}, cb);
	// }

	// /**
	//  * role を取得する
	//  */
	// this.get_role = function(path, cb){
	// 	return apiGet('api.get.role', path, {}, cb);
	// }

	// /**
	//  * Actor のページID一覧を取得する
	//  */
	// this.get_actors = function(path, cb){
	// 	return apiGet('api.get.actors', path, {}, cb);
	// }

	// /**
	//  * get home directory path (deprecated)
	//  *
	//  * `get_path_homedir()` は 非推奨のメソッドです。
	//  * 代わりに、 `get_realpath_homedir()` を使用してください。
	//  */
	// this.get_path_homedir = function(cb){
	// 	return apiGet('api.get.path_homedir', '/', {}, cb);
	// }
	// /**
	//  * get home directory path
	//  */
	// this.get_realpath_homedir = function(cb){
	// 	return apiGet('api.get.path_homedir', '/', {}, cb);
	// }

	// /**
	//  * コンテンツルートディレクトリのパス(=install path) を取得する
	//  */
	// this.get_path_controot = function(cb){
	// 	return apiGet('api.get.path_controot', '/', {}, cb);
	// }

	// /**
	//  * DOCUMENT_ROOT のパスを取得する (deprecated)
	//  *
	//  * `get_path_docroot()` は 非推奨のメソッドです。
	//  * 代わりに、 `get_realpath_docroot()` を使用してください。
	//  */
	// this.get_path_docroot = function(cb){
	// 	return apiGet('api.get.path_docroot', '/', {}, cb);
	// }

	// /**
	//  * DOCUMENT_ROOT のパスを取得する
	//  */
	// this.get_realpath_docroot = function(cb){
	// 	return apiGet('api.get.path_docroot', '/', {}, cb);
	// }

	// /**
	//  * get content path
	//  */
	// this.get_path_content = function(path, cb){
	// 	return apiGet('api.get.path_content', path, {}, cb);
	// }

	// /**
	//  * ローカルリソースディレクトリのパスを得る
	//  */
	// this.path_files = function(path, path_resource, cb){
	// 	path_resource = path_resource||'';
	// 	return apiGet('api.get.path_files', path, {
	// 		"path_resource":path_resource
	// 	}, cb);
	// }

	// /**
	//  * ローカルリソースディレクトリのサーバー内部パスを得る
	//  */
	// this.realpath_files = function(path, path_resource, cb){
	// 	path_resource = path_resource||'';
	// 	return apiGet('api.get.realpath_files', path, {
	// 		"path_resource":path_resource
	// 	}, cb);
	// }

	// /**
	//  * ローカルリソースのキャッシュディレクトリのパスを得る
	//  */
	// this.path_files_cache = function(path, path_resource, cb){
	// 	path_resource = path_resource||'';
	// 	return apiGet('api.get.path_files_cache', path, {
	// 		"path_resource":path_resource
	// 	}, cb);
	// }

	// /**
	//  * ローカルリソースのキャッシュディレクトリのサーバー内部パスを得る
	//  */
	// this.realpath_files_cache = function(path, path_resource, cb){
	// 	path_resource = path_resource||'';
	// 	return apiGet('api.get.realpath_files_cache', path, {
	// 		"path_resource":path_resource
	// 	}, cb);
	// }

	// /**
	//  * コンテンツ別の非公開キャッシュディレクトリのサーバー内部パスを得る
	//  */
	// this.realpath_files_private_cache = function(path, path_resource, cb){
	// 	path_resource = path_resource||'';
	// 	return apiGet('api.get.realpath_files_private_cache', path, {
	// 		"path_resource":path_resource
	// 	}, cb);
	// }

	// /**
	//  * domain を取得する
	//  */
	// this.get_domain = function(cb){
	// 	return apiGet('api.get.domain', '/', {}, cb);
	// }

	// /**
	//  * directory_index(省略できるファイル名) の一覧を得る
	//  */
	// this.get_directory_index = function(cb){
	// 	return apiGet('api.get.directory_index', '/', {}, cb);
	// }

	// /**
	//  * 最も優先されるインデックスファイル名を得る
	//  */
	// this.get_directory_index_primary = function(cb){
	// 	return apiGet('api.get.directory_index_primary', '/', {}, cb);
	// }

	// /**
	//  * ファイルの処理方法を調べる
	//  */
	// this.get_path_proc_type = function(path, cb){
	// 	return apiGet('api.get.path_proc_type', path, {}, cb);
	// }

	// /**
	//  * リンク先のパスを生成する
	//  */
	// this.href = function(path_linkto, cb){
	// 	return apiGet('api.get.href', '/', {
	// 		"linkto":path_linkto
	// 	}, cb);
	// }

	// /**
	//  * パスがダイナミックパスにマッチするか調べる
	//  */
	// this.is_match_dynamic_path = function(path, cb){
	// 	return apiGet('api.is.match_dynamic_path', '/', {
	// 		"path":path
	// 	}, cb);
	// }

	// /**
	//  * ページが、パンくず内に存在しているか調べる
	//  */
	// this.is_page_in_breadcrumb = function(path, path_in, cb){
	// 	return apiGet('api.is.page_in_breadcrumb', path, {
	// 		"path":path_in
	// 	}, cb);
	// }

	// /**
	//  * 除外ファイルか調べる
	//  */
	// this.is_ignore_path = function(path, cb){
	// 	return apiGet('api.is.ignore_path', '/', {
	// 		"path":path
	// 	}, cb);
	// }


	// /**
	//  * パブリッシュする
	//  */
	// this.publish = function(opt){
	// 	opt = opt||{};

	// 	// path_region
	// 	if( !opt.path_region ){
	// 		opt.path_region = '';
	// 	}

	// 	// paths_region
	// 	var str_paths_region = '';
	// 	if( typeof(opt.paths_region) == typeof('') ){
	// 		opt.paths_region = [opt.paths_region];
	// 	}
	// 	if( typeof(opt.paths_region) == typeof([]) ){
	// 		for( var i in opt.paths_region ){
	// 			str_paths_region += '&paths_region[]='+encodeURIComponent(opt.paths_region[i]);
	// 		}
	// 	}

	// 	// paths_ignore
	// 	var str_paths_ignore = '';
	// 	if( typeof(opt.paths_ignore) == typeof('') ){
	// 		opt.paths_ignore = [opt.paths_ignore];
	// 	}
	// 	if( typeof(opt.paths_ignore) == typeof([]) ){
	// 		for( var i in opt.paths_ignore ){
	// 			str_paths_ignore += '&paths_ignore[]='+encodeURIComponent(opt.paths_ignore[i]);
	// 		}
	// 	}

	// 	// keep_cache
	// 	var str_keep_cache = '';
	// 	if( opt.keep_cache ){
	// 		str_keep_cache = '&keep_cache=1';
	// 	}

	// 	return this.query(
	// 		'/?PX=publish.run&path_region=' + encodeURIComponent(opt.path_region) + str_paths_ignore + str_paths_region + str_keep_cache,
	// 		opt
	// 	);
	// }

	// /**
	//  * キャッシュを削除する
	//  */
	// this.clearcache = function(opt){
	// 	opt = opt||{};

	// 	return this.query(
	// 		'/?PX=clearcache' ,
	// 		opt
	// 	);
	// }


	// /**
	//  * get_children() へ渡されるオプションを調整する
	//  * この形式のオプションは、get_bros(), get_bros_next(), get_bros_prev(), get_next(), get_prev() でも共通です。
	//  */
	// function sitemap_children_params(options){
	// 	function boolize(val){
	// 		if(typeof(val) === typeof(null) || val === undefined){
	// 			val = null;
	// 		}else if(typeof(val) === typeof('string')){
	// 			switch( val ){
	// 				case 'true':
	// 				case '1':
	// 					val = 'true'; break;
	// 				case 'false':
	// 				case '0':
	// 					val = 'false'; break;
	// 			}
	// 		}else{
	// 			val = (val?'true':'false')
	// 		}
	// 		return val;
	// 	}
	// 	options = options||{};
	// 	var rtn = {};
	// 	rtn['filter'] = boolize(options['filter']);
	// 	return rtn;
	// }

}
