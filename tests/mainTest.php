<?php
/**
 * test for pickles2/px2agent
 */
class mainTest extends PHPUnit\Framework\TestCase{
	private $fs;
	private $factory;

	public function setUp() : void{
		mb_internal_encoding('UTF-8');
		$this->fs = new tomk79\filesystem();
		require_once(__DIR__.'/helper/factory.php');
		$this->factory = new test_helper_factory();
	}


	/**
	 * バージョン番号を取得するテスト
	 */
	public function testGettingVersionNumber(){
		$px2proj = $this->factory->getProject('htdocs1');
		$version = $px2proj->get_version();
		// var_dump($version);
		$result = preg_match('/^([0-9]+\\.[0-9]+\\.[0-9]+)(\\-(?:alpha|beta|rc)(?:\.[0-9]+)?)?(\\+(?:nb|dev))?$/is', $version, $matched);
		$this->assertEquals( $result, 1 );
	}

	/**
	 * configを取得するテスト
	 */
	public function testGettingConfig(){
		$px2proj = $this->factory->getProject('htdocs1');
		$conf = $px2proj->get_config();
		// var_dump($conf);
		$this->assertEquals($conf->name, 'px2agent test htdocs1');
		$this->assertEquals($conf->allow_pxcommands, 1);
		$this->assertTrue(is_object($conf->funcs));
		$this->assertTrue(is_object($conf->funcs->processor->html));
	}

	/**
	 * phpinfo() を取得する
	 */
	public function testGettingPhpinfo(){
		$px2proj = $this->factory->getProject('htdocs1');
		$html = $px2proj->query(
			'/?PX=phpinfo'
		);
		// var_dump(html);
		$this->assertTrue(is_string($html));

		$match_result = preg_match('/phpinfo\(\)/is', $html, $matched);
		$this->assertNotEquals($matched, null);

		$versionRegExp = '[0-9]+\.[0-9]+\.[0-9]+';
		$match_result = preg_match('/PHP Version \=\> '.$versionRegExp.'/is', $html, $matched);
		$this->assertNotEquals($matched, null);
	}


	/**
	 * サイトマップを取得するテスト
	 */
	public function testGettingSitemap(){
		$px2proj = $this->factory->getProject('htdocs1');
		$sitemap = $px2proj->get_sitemap();
		// var_dump($sitemap);
		$this->assertTrue(is_object($sitemap));
		$this->assertTrue(is_object($sitemap->{'/index.html'}));
		$this->assertEquals($sitemap->{'/index.html'}->path, '/index.html');
		$this->assertEquals($sitemap->{'/index.html'}->id, '');
		$this->assertEquals($sitemap->{'/index.html'}->title, 'HOME');
		$this->assertEquals($sitemap->{'/sample_pages/page1/4/{*}'}->path, '/sample_pages/page1/4/{*}');
		$this->assertEquals($sitemap->{'/sample_pages/page1/4/{*}'}->id, ':auto_page_id.13');
		$this->assertEquals($sitemap->{'/sample_pages/page1/4/{*}'}->title, 'ダイナミックパス');
	}



// describe('Pickles 2 からHTMLページを取得するテスト', function() {
// 	var pj = getProject('htdocs1');

// 	it("Mozilla/5.0 としてトップページを取得する", function(done) {
// 		this.timeout(60*1000);
// 		pj.query(
// 			'/' ,
// 			{
// 				"userAgent": "Mozilla/5.0",
// 				"complete": function(html, code){
// 					// var_dump(html);
// 					$this->assertEquals(typeof(html), typeof(''));
// 					done();
// 				}
// 			}
// 		);
// 	});

// 	it("Mozilla/5.0 としてトップページをJSON形式で取得する", function(done) {
// 		this.timeout(60*1000);
// 		pj.query(
// 			'/' ,
// 			{
// 				"userAgent": "Mozilla/5.0",
// 				"output": "json" ,
// 				"complete": function(data, code){
// 					data = JSON.parse(data);
// 					// var_dump(data);
// 					$this->assertEquals(data.status, 200);
// 					$this->assertEquals(data.message, 'OK');
// 					done();
// 				}
// 			}
// 		);
// 	});

// });



// describe('PXコマンドを発行するテスト', function() {
// 	var pj = getProject('htdocs1');

// 	it("path '/' のページ情報を取得する", function(done) {
// 		this.timeout(60*1000);
// 		pj.px_command(
// 			'api.get.page_info',
// 			'/' ,
// 			{
// 				path: '/index.html'
// 			},
// 			function(page_info){
// 				// var_dump(page_info);
// 				$this->assertEquals( typeof(page_info), typeof({}) );
// 				$this->assertEquals( page_info.id, '' );
// 				$this->assertEquals( page_info.title, 'HOME' );
// 				$this->assertEquals( page_info.path, '/index.html' );
// 				done();
// 			}
// 		);
// 	});

// });




	/**
	 * ページ情報を取得するテスト: path '/' のページ情報を取得する
	 */
	public function testGettingPageInfo_slash(){
		$px2proj = $this->factory->getProject('htdocs1');
		$page_info = $px2proj->get_page_info('/');
		// var_dump($page_info);
		$this->assertTrue( is_object($page_info) );
		$this->assertEquals( $page_info->id, '' );
		$this->assertEquals( $page_info->title, 'HOME' );
		$this->assertEquals( $page_info->path, '/index.html' );
	}

	/**
	 * ページ情報を取得するテスト: id '' のページ情報を取得する
	 */
	public function testGettingPageInfo_0byteString(){
		$px2proj = $this->factory->getProject('htdocs1');
		$page_info = $px2proj->get_page_info('');
		// var_dump($page_info);
		$this->assertTrue( is_object($page_info) );
		$this->assertEquals( $page_info->id, '' );
		$this->assertEquals( $page_info->title, 'HOME' );
		$this->assertEquals( $page_info->path, '/index.html' );
	}

	/**
	 * ページ情報を取得するテスト: path '/actors/role.html' のページ情報を取得する
	 */
	public function testGettingPageInfo_actors_role(){
		$px2proj = $this->factory->getProject('htdocs1');
		$page_info = $px2proj->get_page_info('/actors/role.html');
		// var_dump($page_info);
		$this->assertTrue( is_object($page_info) );
		$this->assertEquals( $page_info->id, 'role-page' );
		$this->assertEquals( $page_info->path, '/actors/role.html' );
	}

	/**
	 * ページ情報を取得するテスト: path '/actors/actor-1.html' のページ情報を取得する
	 */
	public function testGettingPageInfo_actors_actor(){
		$px2proj = $this->factory->getProject('htdocs1');
		$page_info = $px2proj->get_page_info('/actors/actor-1.html');
		// var_dump($page_info);
		$this->assertTrue( is_object($page_info) );
		$this->assertEquals( $page_info->id, 'actor-1' );
		$this->assertEquals( $page_info->path, '/actors/actor-1.html' );
		$this->assertEquals( $page_info->role, 'role-page' );
	}



// describe('親ページのページIDを取得する', function() {
// 	var pj = getProject('htdocs1');

// 	it("path '/sample_pages/' のページ情報を取得する", function(done) {
// 		this.timeout(60*1000);
// 		pj.get_parent( '/sample_pages/', function( parent ){
// 			// var_dump(parent);
// 			$this->assertEquals( parent, '' );
// 			done();
// 		} );
// 	});

// 	it("path '/' の親ページを取得する", function(done) {
// 		this.timeout(60*1000);
// 		pj.get_parent( '/', function( parent ){
// 			$this->assertEquals( parent, false );
// 			done();
// 		} );
// 	});

// });




// describe('子ページのページID一覧を取得する', function() {
// 	var pj = getProject('htdocs1');

// 	it("path '/sample_pages/' の子ページ一覧を取得する", function(done) {
// 		this.timeout(60*1000);
// 		pj.get_children( '/sample_pages/', function( children ){
// 			// var_dump(children);
// 			$this->assertEquals( typeof(children), typeof([]) );
// 			$this->assertEquals( children[0], ':auto_page_id.4' );
// 			$this->assertEquals( children[6], 'sitemapExcel_auto_id_1' );
// 			$this->assertEquals( children.length, 7 );
// 			done();
// 		} );
// 	});

// 	it("path '/' の子ページ一覧を取得する", function(done) {
// 		this.timeout(60*1000);
// 		pj.get_children( '/', function( children ){
// 			// var_dump(children);
// 			$this->assertEquals( typeof(children), typeof([]) );
// 			$this->assertEquals( children[0], ':auto_page_id.3' );
// 			$this->assertEquals( children[4], 'help' );
// 			$this->assertEquals( children.length, 6 );
// 			done();
// 		} );
// 	});

// 	it("path '/bros3/' の子ページ一覧を取得する", function(done) {
// 		this.timeout(60*1000);
// 		pj.get_children( '/bros3/', function( children ){
// 			// var_dump(children);
// 			$this->assertEquals( typeof(children), typeof([]) );
// 			$this->assertEquals( children[0], 'Bros3-2' );
// 			$this->assertEquals( children[2], 'Bros3-6' );
// 			$this->assertEquals( children.length, 3 );
// 			done();
// 		} );
// 	});

// 	it("path '/bros3/' の子ページ一覧を、filterを無効にして取得する", function(done) {
// 		this.timeout(60*1000);
// 		pj.get_children( '/bros3/', {"filter": false}, function( children ){
// 			// var_dump(children);
// 			$this->assertEquals( typeof(children), typeof([]) );
// 			$this->assertEquals( children[0], 'Bros3-2' );
// 			$this->assertEquals( children[4], 'Bros3-6' );
// 			$this->assertEquals( children.length, 5 );
// 			done();
// 		} );
// 	});

// });


// describe('兄弟ページのページID一覧を取得する', function() {
// 	var pj = getProject('htdocs1');

// 	it("path '/sample_pages/' の兄弟ページ一覧を取得する", function(done) {
// 		this.timeout(60*1000);
// 		pj.get_bros( '/sample_pages/', function( bros ){
// 			// var_dump(bros);
// 			$this->assertEquals( typeof(bros), typeof([]) );
// 			$this->assertEquals( bros[0], ':auto_page_id.3' );
// 			$this->assertEquals( bros[4], 'help' );
// 			$this->assertEquals( bros.length, 6 );
// 			done();
// 		} );
// 	});

// 	it("path '/' の兄弟ページ一覧を取得する", function(done) {
// 		this.timeout(60*1000);
// 		pj.get_bros( '/', function( bros ){
// 			// var_dump(bros);
// 			$this->assertEquals( typeof(bros), typeof([]) );
// 			$this->assertEquals( bros[0], '' );
// 			$this->assertEquals( bros.length, 1 );
// 			done();
// 		} );
// 	});

// 	it("path '/bros3/3.html' の兄弟ページ一覧を、filterを無効にして取得する", function(done) {
// 		this.timeout(60*1000);
// 		pj.get_bros( '/bros3/3.html', {"filter": false}, function( bros ){
// 			// var_dump(bros);
// 			$this->assertEquals( typeof(bros), typeof([]) );
// 			$this->assertEquals( bros[0], 'Bros3-2' );
// 			$this->assertEquals( bros[4], 'Bros3-6' );
// 			$this->assertEquals( bros.length, 5 );
// 			done();
// 		} );
// 	});

// });

// describe('次の兄弟ページを取得する', function() {
// 	var pj = getProject('htdocs1');

// 	it("path '/sample_pages/page2/1.htm' の次の兄弟ページIDを取得する", function(done) {
// 		this.timeout(60*1000);
// 		pj.get_bros_next( '/sample_pages/page2/1.htm', function( pageId ){
// 			// var_dump(pageId);
// 			$this->assertEquals( pageId, ':auto_page_id.17' );
// 			done();
// 		} );
// 	});

// 	it("path '/sample_pages/page2/2.html' の次の兄弟ページIDを取得する", function(done) {
// 		this.timeout(60*1000);
// 		pj.get_bros_next( '/sample_pages/page2/2.html', function( pageId ){
// 			// var_dump(pageId);
// 			$this->assertEquals( pageId, false );
// 			done();
// 		} );
// 	});

// 	it("path '/bros3/4.html' の次の兄弟ページIDを、filterを無効にして取得する", function(done) {
// 		this.timeout(60*1000);
// 		pj.get_bros_next( '/bros3/4.html', {"filter": false}, function( pageId ){
// 			// var_dump(pageId);
// 			$this->assertEquals( pageId, 'Bros3-5' );
// 			done();
// 		} );
// 	});

// });

// describe('前の兄弟ページを取得する', function() {
// 	var pj = getProject('htdocs1');

// 	it("path '/sample_pages/page2/index.html' の前の兄弟ページIDを取得する", function(done) {
// 		this.timeout(60*1000);
// 		pj.get_bros_prev( '/sample_pages/page2/index.html', function( pageId ){
// 			// var_dump(pageId);
// 			$this->assertEquals( pageId, ':auto_page_id.3' );
// 			done();
// 		} );
// 	});

// 	it("path '/sample_pages/' の前の兄弟ページIDを取得する", function(done) {
// 		this.timeout(60*1000);
// 		pj.get_bros_prev( '/sample_pages/', function( pageId ){
// 			// var_dump(pageId);
// 			assert.strictEqual( pageId, false );
// 			done();
// 		} );
// 	});

// 	it("path '/bros3/4.html' の前の兄弟ページIDを、filterを無効にして取得する", function(done) {
// 		this.timeout(60*1000);
// 		pj.get_bros_prev( '/bros3/4.html', {"filter": false}, function( pageId ){
// 			// var_dump(pageId);
// 			$this->assertEquals( pageId, 'Bros3-3' );
// 			done();
// 		} );
// 	});

// });

// describe('次のページを取得する', function() {
// 	var pj = getProject('htdocs1');

// 	it("path '/sample_pages/page2/1.htm' の次のページIDを取得する", function(done) {
// 		this.timeout(60*1000);
// 		pj.get_next( '/sample_pages/page2/1.htm', function( pageId ){
// 			// var_dump(pageId);
// 			$this->assertEquals( pageId, ':auto_page_id.17' );
// 			done();
// 		} );
// 	});

// 	it("path '/sample_pages/page2/2.html' の次のページIDを取得する", function(done) {
// 		this.timeout(60*1000);
// 		pj.get_next( '/sample_pages/page2/2.html', function( pageId ){
// 			// var_dump(pageId);
// 			$this->assertEquals( pageId, ':auto_page_id.18' );
// 			done();
// 		} );
// 	});

// 	it("path '/bros3/4.html' の次のページIDを、filterを無効にして取得する", function(done) {
// 		this.timeout(60*1000);
// 		pj.get_next( '/bros3/4.html', {"filter": false}, function( pageId ){
// 			// var_dump(pageId);
// 			$this->assertEquals( pageId, 'Bros3-5' );
// 			done();
// 		} );
// 	});

// 	it("path '/actors/role.html' の次のページIDを取得する", function(done) {
// 		this.timeout(60*1000);
// 		pj.get_next( '/actors/role.html', function( pageId ){
// 			// var_dump(pageId);
// 			assert.strictEqual( pageId, false );
// 			done();
// 		} );
// 	});

// });

// describe('前のページを取得する', function() {
// 	var pj = getProject('htdocs1');

// 	it("path '/sample_pages/page2/index.html' の前のページIDを取得する", function(done) {
// 		this.timeout(60*1000);
// 		pj.get_prev( '/sample_pages/page2/index.html', function( pageId ){
// 			// var_dump(pageId);
// 			$this->assertEquals( pageId, ':auto_page_id.13' );
// 			done();
// 		} );
// 	});

// 	it("path '/sample_pages/' の前のページIDを取得する", function(done) {
// 		this.timeout(60*1000);
// 		pj.get_prev( '/sample_pages/', function( pageId ){
// 			// var_dump(pageId);
// 			assert.strictEqual( pageId, '' );
// 			done();
// 		} );
// 	});

// 	it("path '/bros3/4.html' の前のページIDを、filterを無効にして取得する", function(done) {
// 		this.timeout(60*1000);
// 		pj.get_prev( '/bros3/4.html', {"filter": false}, function( pageId ){
// 			// var_dump(pageId);
// 			$this->assertEquals( pageId, 'Bros3-3' );
// 			done();
// 		} );
// 	});

// 	it("path '/' の前のページIDを取得する", function(done) {
// 		this.timeout(60*1000);
// 		pj.get_prev( '/', function( pageId ){
// 			// var_dump(pageId);
// 			assert.strictEqual( pageId, false );
// 			done();
// 		} );
// 	});

// });

// describe('パンくず上のページ一覧を取得する', function() {
// 	var pj = getProject('htdocs1');

// 	it("path '/sample_pages/page1/2.html' の兄弟ページ一覧を取得する", function(done) {
// 		this.timeout(60*1000);
// 		pj.get_breadcrumb_array( '/sample_pages/page1/2.html', function( breadcrumb ){
// 			// ※このAPIが返す値には、自分自身は含まれない。
// 			// var_dump(breadcrumb);
// 			$this->assertEquals( typeof(breadcrumb), typeof([]) );
// 			$this->assertEquals( breadcrumb[0], '' );
// 			$this->assertEquals( breadcrumb[1], ':auto_page_id.3' );
// 			$this->assertEquals( breadcrumb.length, 2 );
// 			done();
// 		} );
// 	});

// });


// describe('ダイナミックパス情報を取得する', function() {
// 	var pj = getProject('htdocs1');

// 	it("path '/sample_pages/page1/2.html' のダイナミックパス情報を取得する", function(done) {
// 		this.timeout(60*1000);
// 		pj.get_dynamic_path_info( '/sample_pages/page1/2.html', function( value ){
// 			// var_dump(value);
// 			$this->assertEquals( value, false );
// 			done();
// 		} );
// 	});

// 	it("path '/sample_pages/page1/4/{*}' のダイナミックパス情報を取得する", function(done) {
// 		this.timeout(60*1000);
// 		pj.get_dynamic_path_info( '/sample_pages/page1/4/{*}', function( value ){
// 			// var_dump(value);
// 			$this->assertEquals( typeof(value), typeof({}) );
// 			$this->assertEquals( value.path, '/sample_pages/page1/4/' );
// 			$this->assertEquals( value.path_original, '/sample_pages/page1/4/{*}' );
// 			$this->assertEquals( value.id, ':auto_page_id.13' );
// 			done();
// 		} );
// 	});

// 	it("path '/sample_pages/page1/4/param/value/index.html' のダイナミックパス情報を取得する", function(done) {
// 		this.timeout(60*1000);
// 		pj.get_dynamic_path_info( '/sample_pages/page1/4/param/value/index.html', function( value ){
// 			// var_dump(value);
// 			$this->assertEquals( typeof(value), typeof({}) );
// 			$this->assertEquals( value.path, '/sample_pages/page1/4/' );
// 			$this->assertEquals( value.path_original, '/sample_pages/page1/4/{*}' );
// 			$this->assertEquals( value.id, ':auto_page_id.13' );
// 			done();
// 		} );
// 	});

// });


// describe('ダイナミックパス情報に値をバインドする', function() {
// 	var pj = getProject('htdocs1');

// 	it("path '/dynamicPath/{*}' に値をバインドする", function(done) {
// 		this.timeout(60*1000);
// 		pj.bind_dynamic_path_param( '/dynamicPath/{*}', {'':'abc.html'}, function( value ){
// 			// var_dump(value);
// 			$this->assertEquals( value, '/dynamicPath/abc.html' );
// 			done();
// 		} );
// 	});

// 	it("path '/dynamicPath/id_{$id}/name_{$name}/{*}' に値をバインドする", function(done) {
// 		this.timeout(60*1000);
// 		pj.bind_dynamic_path_param( '/dynamicPath/id_{$id}/name_{$name}/{*}', {'':'abc.html', 'id':'hoge', 'name':'fuga'}, function( value ){
// 			// var_dump(value);
// 			$this->assertEquals( value, '/dynamicPath/id_hoge/name_fuga/abc.html' );
// 			done();
// 		} );
// 	});

// });


// describe('アクター情報を取得する', function() {
// 	var pj = getProject('htdocs1');

// 	it("path '/actors/actor-1.html' のroleを取得する", function(done) {
// 		this.timeout(60*1000);
// 		pj.get_role( '/actors/actor-1.html', function( value ){
// 			// var_dump(value);
// 			$this->assertEquals( value, 'role-page' );
// 			done();
// 		} );
// 	});

// 	it("path '/actors/role.html' のactorの一覧を取得する", function(done) {
// 		this.timeout(60*1000);
// 		pj.get_actors( '/actors/role.html', function( value ){
// 			// var_dump(value);
// 			assert.deepEqual( value, ['actor-1','actor-2'] );
// 			done();
// 		} );
// 	});

// });


// describe('ホームディレクトリのパスを取得する', function() {
// 	var pj = getProject('htdocs1');

// 	it("ホームディレクトリのパスを取得する", function(done) {
// 		this.timeout(60*1000);
// 		pj.get_realpath_homedir( function( path_home_dir ){
// 			// var_dump(path_home_dir);
// 			$this->assertEquals( typeof(path_home_dir), typeof('') );
// 			$this->assertEquals( fs.realpathSync(path_home_dir), fs.realpathSync(__DIR__.'/testData/htdocs1/px-files/') );
// 			done();
// 		} );
// 	});
// });


// describe('コンテンツルートディレクトリのパスを取得する', function() {
// 	var pj = getProject('htdocs1');

// 	it("コンテンツルートディレクトリのパスを取得する", function(done) {
// 		this.timeout(60*1000);
// 		pj.get_path_controot( function( path_controot ){
// 			// var_dump(path_controot);
// 			$this->assertEquals( typeof(path_controot), typeof('') );
// 			$this->assertEquals( path_controot, '/' );
// 			done();
// 		} );
// 	});
// });

// describe('ドキュメントルートディレクトリのパスを取得する', function() {
// 	var pj = getProject('htdocs1');

// 	it("ドキュメントルートディレクトリのパスを取得する", function(done) {
// 		this.timeout(60*1000);
// 		pj.get_realpath_docroot( function( path_docroot ){
// 			// var_dump(path_docroot);
// 			$this->assertEquals( typeof(path_docroot), typeof('') );
// 			$this->assertEquals( fs.realpathSync(path_docroot), fs.realpathSync(__DIR__.'/testData/htdocs1/') );
// 			done();
// 		} );
// 	});
// });

// describe('コンテンツのパスを取得する', function() {
// 	var pj = getProject('htdocs1');

// 	it("path '/' のコンテンツのパスを取得する", function(done) {
// 		this.timeout(60*1000);
// 		pj.get_path_content( '/', function( path_content ){
// 			// var_dump(path_docroot);
// 			$this->assertEquals( path_content, '/index.html' );
// 			done();
// 		} );
// 	});

// 	it("path '/sample_pages/page1/3.html' のコンテンツのパスを取得する", function(done) {
// 		this.timeout(60*1000);
// 		pj.get_path_content( '/sample_pages/page1/3.html', function( path_content ){
// 			// var_dump(path_content);
// 			$this->assertEquals( path_content, '/sample_pages/page1/3.html.md' );
// 			done();
// 		} );
// 	});
// });

// describe('コンテンツのリソースディレクトリのパスを取得する', function() {
// 	var pj = getProject('htdocs1');

// 	it("path '/' のコンテンツのリソースディレクトリのパスを取得する", function(done) {
// 		this.timeout(60*1000);
// 		pj.path_files( '/', '/images/test.png', function( path_content ){
// 			// var_dump(path_docroot);
// 			$this->assertEquals( path_content, '/index_files/images/test.png' );
// 			done();
// 		} );
// 	});

// 	it("path '/' のコンテンツのリソースディレクトリのパスを取得する(第二引数をnullで指定)", function(done) {
// 		this.timeout(60*1000);
// 		pj.path_files( '/', null, function( path_content ){
// 			// var_dump(path_docroot);
// 			$this->assertEquals( path_content, '/index_files/' );
// 			done();
// 		} );
// 	});

// 	it("path '/sample_pages/page1/3.html' のコンテンツのリソースディレクトリのパスを取得する", function(done) {
// 		this.timeout(60*1000);
// 		pj.path_files( '/sample_pages/page1/3.html', '', function( path_content ){
// 			// var_dump(path_content);
// 			$this->assertEquals( path_content, '/sample_pages/page1/3_files/' );
// 			done();
// 		} );
// 	});
// });

// describe('コンテンツのリソースディレクトリの絶対パスを取得する', function() {
// 	var pj = getProject('htdocs1');

// 	it("path '/' のコンテンツのリソースディレクトリの絶対パスを取得する", function(done) {
// 		this.timeout(60*1000);
// 		pj.realpath_files( '/', '/images/test.png', function( path_content ){
// 			// var_dump(path_docroot);
// 			$this->assertEquals( path.resolve(path_content), path.resolve(__DIR__.'/testData/htdocs1/'+'/index_files/images/test.png') );
// 			done();
// 		} );
// 	});

// 	it("path '/' のコンテンツのリソースディレクトリの絶対パスを取得する(第二引数をnullで指定)", function(done) {
// 		this.timeout(60*1000);
// 		pj.realpath_files( '/', null, function( path_content ){
// 			// var_dump(path_docroot);
// 			$this->assertEquals( path.resolve(path_content), path.resolve(__DIR__.'/testData/htdocs1/'+'/index_files/') );
// 			done();
// 		} );
// 	});

// 	it("path '/sample_pages/page1/3.html' のコンテンツのリソースディレクトリの絶対パスを取得する", function(done) {
// 		this.timeout(60*1000);
// 		pj.realpath_files( '/sample_pages/page1/3.html', '', function( path_content ){
// 			// var_dump(path_content);
// 			$this->assertEquals( path.resolve(path_content), path.resolve(__DIR__.'/testData/htdocs1/'+'/sample_pages/page1/3_files/') );
// 			done();
// 		} );
// 	});
// });



// describe('コンテンツの cache directory のパスを調べる', function() {
// 	var pj = getProject('htdocs1');

// 	it("path '/' の cache directory のパス", function(done) {
// 		this.timeout(60*1000);
// 		pj.path_files_cache( '/', '/sample.png', function( result ){
// 			// var_dump(result);
// 			$this->assertEquals( result, '/caches/c/index_files/sample.png' );
// 			done();
// 		} );
// 	});

// });


// describe('コンテンツの cache directory の絶対パスを調べる', function() {
// 	var pj = getProject('htdocs1');

// 	it("path '/' の cache directory の絶対パス", function(done) {
// 		this.timeout(60*1000);
// 		pj.realpath_files_cache( '/', '/sample.png', function( realpath ){
// 			// var_dump(realpath);
// 			$this->assertEquals( path.resolve( realpath ), path.resolve( __DIR__.'/testData/htdocs1/caches/c/index_files/sample.png' ) );
// 			done();
// 		} );
// 	});

// });


// describe('コンテンツの private cache directory の絶対パスを調べる', function() {
// 	var pj = getProject('htdocs1');

// 	it("path '/' の private cache directory の絶対パス", function(done) {
// 		this.timeout(60*1000);
// 		pj.realpath_files_private_cache( '/', '/sample.png', function( realpath ){
// 			// var_dump(realpath);
// 			$this->assertEquals( path.resolve( realpath ), path.resolve( __DIR__.'/testData/htdocs1/px-files/_sys/ram/caches/c/index_files/sample.png' ) );
// 			done();
// 		} );
// 	});

// });



// describe('ドメイン名を取得する', function() {
// 	var pj = getProject('htdocs1');

// 	it("ドメイン名を取得する", function(done) {
// 		this.timeout(60*1000);
// 		pj.get_domain( function( domain ){
// 			// var_dump(domain);
// 			$this->assertEquals( domain, 'pickles2.pxt.jp' );
// 			done();
// 		} );
// 	});

// });

// describe('ディレクトリインデックスのテスト', function() {
// 	var pj = getProject('htdocs1');

// 	it("ディレクトリインデックスの一覧を取得する", function(done) {
// 		this.timeout(60*1000);
// 		pj.get_directory_index( function( directory_index ){
// 			// var_dump(directory_index);
// 			$this->assertEquals( typeof(directory_index), typeof([]) );
// 			$this->assertEquals( directory_index[0], 'index.html' );
// 			$this->assertEquals( directory_index.length, 1 );
// 			done();
// 		} );
// 	});

// 	it("最も優先されるディレクトリインデックスを取得する", function(done) {
// 		this.timeout(60*1000);
// 		pj.get_directory_index_primary( function( directory_index ){
// 			// var_dump(directory_index);
// 			$this->assertEquals( directory_index, 'index.html' );
// 			done();
// 		} );
// 	});

// });



// describe('proc_typeを取得する', function() {
// 	var pj = getProject('htdocs1');

// 	it("path '/sample_pages/index.html' のproc_typeを取得", function(done) {
// 		this.timeout(60*1000);
// 		pj.get_path_proc_type( '/sample_pages/index.html', function( proc_type ){
// 			// var_dump(proc_type);
// 			$this->assertEquals( proc_type, 'html' );
// 			done();
// 		} );
// 	});

// 	it("path '/common/styles/common.css' のproc_typeを取得", function(done) {
// 		this.timeout(60*1000);
// 		pj.get_path_proc_type( '/common/styles/common.css', function( proc_type ){
// 			// var_dump(proc_type);
// 			$this->assertEquals( proc_type, 'css' );
// 			done();
// 		} );
// 	});

// 	it("path '/common/images/logo.png' のproc_typeを取得", function(done) {
// 		this.timeout(60*1000);
// 		pj.get_path_proc_type( '/common/images/logo.png', function( proc_type ){
// 			// var_dump(proc_type);
// 			$this->assertEquals( proc_type, 'direct' );
// 			done();
// 		} );
// 	});

// 	it("path '/vendor/autoload.php' のproc_typeを取得", function(done) {
// 		this.timeout(60*1000);
// 		pj.get_path_proc_type( '/vendor/autoload.php', function( proc_type ){
// 			// var_dump(proc_type);
// 			$this->assertEquals( proc_type, 'ignore' );
// 			done();
// 		} );
// 	});


// });




// describe('リンク先を解決するテスト', function() {
// 	var pj = getProject('htdocs1');

// 	it("path '/sample_pages/index.html' へのリンク", function(done) {
// 		this.timeout(60*1000);
// 		pj.href( '/sample_pages/index.html', function( href ){
// 			// var_dump(href);
// 			$this->assertEquals( typeof(href), typeof('') );
// 			$this->assertEquals( href, '/sample_pages/' );
// 			done();
// 		} );
// 	});


// });


// describe('ダイナミックパスの一覧に含まれるかどうか調べる', function() {
// 	var pj = getProject('htdocs1');

// 	it("path '/sample_pages/page1/4/{*}' がダイナミックパスかチェック", function(done) {
// 		this.timeout(60*1000);
// 		pj.is_match_dynamic_path( '/sample_pages/page1/4/{*}', function( result ){
// 			// var_dump(result);
// 			$this->assertEquals( result, true );
// 			done();
// 		} );
// 	});

// 	it("path '/sample_pages/page1/4/' がダイナミックパスかチェック", function(done) {
// 		this.timeout(60*1000);
// 		pj.is_match_dynamic_path( '/sample_pages/page1/4/', function( result ){
// 			// var_dump(result);
// 			$this->assertEquals( result, true );
// 			done();
// 		} );
// 	});

// 	it("path '/sample_pages/page1/4/param1/param2.html' がダイナミックパスかチェック", function(done) {
// 		this.timeout(60*1000);
// 		pj.is_match_dynamic_path( '/sample_pages/page1/4/param1/param2.html', function( result ){
// 			// var_dump(result);
// 			$this->assertEquals( result, true );
// 			done();
// 		} );
// 	});

// 	it("path '/sample_pages/' がダイナミックパスかチェック", function(done) {
// 		this.timeout(60*1000);
// 		pj.is_match_dynamic_path( '/sample_pages/', function( result ){
// 			// var_dump(result);
// 			$this->assertEquals( result, false );
// 			done();
// 		} );
// 	});

// });


// describe('パンくずに含まれるかどうか調べる', function() {
// 	var pj = getProject('htdocs1');

// 	it("path '/sample_pages/' が path '/sample_pages/page1/2.html' のパンくずに含まれるかチェック", function(done) {
// 		this.timeout(60*1000);
// 		pj.is_page_in_breadcrumb( '/sample_pages/page1/2.html', '/sample_pages/', function( result ){
// 			// var_dump(result);
// 			$this->assertEquals( result, true );
// 			done();
// 		} );
// 	});


// });


// describe('ignore_pathかどうか調べる', function() {
// 	var pj = getProject('htdocs1');

// 	it("path '/sample_pages/index.html' をチェック", function(done) {
// 		this.timeout(60*1000);
// 		pj.is_ignore_path( '/sample_pages/index.html', function( is_ignore ){
// 			// var_dump(is_ignore);
// 			$this->assertEquals( is_ignore, false );
// 			done();
// 		} );
// 	});

// 	it("path '/common/styles/common.css' をチェック", function(done) {
// 		this.timeout(60*1000);
// 		pj.is_ignore_path( '/common/styles/common.css', function( is_ignore ){
// 			// var_dump(is_ignore);
// 			$this->assertEquals( is_ignore, false );
// 			done();
// 		} );
// 	});

// 	it("path '/common/images/logo.png' をチェック", function(done) {
// 		this.timeout(60*1000);
// 		pj.is_ignore_path( '/common/images/logo.png', function( is_ignore ){
// 			// var_dump(is_ignore);
// 			$this->assertEquals( is_ignore, false );
// 			done();
// 		} );
// 	});

// 	it("path '/vendor/autoload.php' をチェック", function(done) {
// 		this.timeout(60*1000);
// 		pj.is_ignore_path( '/vendor/autoload.php', function( is_ignore ){
// 			// var_dump(is_ignore);
// 			$this->assertEquals( is_ignore, true );
// 			done();
// 		} );
// 	});


// });





	/**
	 * パブリッシュするテスト: パブリッシュする
	 */
	public function testPublish(){
		$px2proj = $this->factory->getProject('htdocs1');
		$output = $px2proj->publish(array());
		// var_dump($output);
		clearstatcache();
		$this->assertEquals( file_exists(__DIR__.'/testData/htdocs1/px-files/_sys/ram/publish/applock.txt'), false );
		$this->assertEquals( file_exists(__DIR__.'/testData/htdocs1/px-files/_sys/ram/publish/publish_log.csv'), true );
		$this->assertEquals( file_exists(__DIR__.'/testData/htdocs1/px-files/_sys/ram/publish/alert_log.csv'), true );
		$this->assertEquals( file_exists(__DIR__.'/testData/htdocs1/px-files/_sys/ram/publish/htdocs/'), true );
		$this->assertEquals( file_exists(__DIR__.'/testData/htdocs1/px-files/_sys/ram/publish/htdocs/index.html'), true );
		$this->assertEquals( file_exists(__DIR__.'/testData/htdocs1/px-files/_sys/ram/publish/htdocs/caches/'), true );

		$html = file_get_contents(__DIR__.'/testData/htdocs1/px-files/_sys/ram/publish/htdocs/index.html');
		// var_dump($html);
		$versionRegExp = '[0-9]+\.[0-9]+\.[0-9]+';
		$match_result = preg_match('/PHP Version \=\> '.$versionRegExp.'/is', $html, $matched);
		$this->assertNotEquals($matched, null);
	}

	/**
	 * パブリッシュするテスト: /common/ ディレクトリのみパブリッシュする
	 */
	public function testPublish_common_only(){
		$px2proj = $this->factory->getProject('htdocs1');
		$output = $px2proj->publish(array(
			"path_region" => "/common/",
		));
		// var_dump($output);
		clearstatcache();
		$this->assertEquals( file_exists(__DIR__.'/testData/htdocs1/px-files/_sys/ram/publish/applock.txt'), false );
		$this->assertEquals( file_exists(__DIR__.'/testData/htdocs1/px-files/_sys/ram/publish/htdocs/'), true );
		$this->assertEquals( file_exists(__DIR__.'/testData/htdocs1/px-files/_sys/ram/publish/htdocs/index.html'), false );
		$this->assertEquals( file_exists(__DIR__.'/testData/htdocs1/px-files/_sys/ram/publish/htdocs/caches/'), false );
		$this->assertEquals( file_exists(__DIR__.'/testData/htdocs1/px-files/_sys/ram/publish/htdocs/common/styles/contents.css'), true );
		$this->assertEquals( file_exists(__DIR__.'/testData/htdocs1/px-files/_sys/ram/publish/publish_log.csv'), true );
	}

	/**
	 * パブリッシュするテスト: /common/ ディレクトリのみパブリッシュしない
	 */
	public function testPublish_common_only_ignored(){
		$px2proj = $this->factory->getProject('htdocs1');
		$output = $px2proj->publish(array(
			"path_region" => "/",
			"paths_ignore" => array("/common/"),
		));
		// var_dump($output);
		clearstatcache();
		$this->assertEquals( file_exists(__DIR__.'/testData/htdocs1/px-files/_sys/ram/publish/applock.txt'), false );
		$this->assertEquals( file_exists(__DIR__.'/testData/htdocs1/px-files/_sys/ram/publish/htdocs/'), true );
		$this->assertEquals( file_exists(__DIR__.'/testData/htdocs1/px-files/_sys/ram/publish/htdocs/index.html'), true );
		$this->assertEquals( file_exists(__DIR__.'/testData/htdocs1/px-files/_sys/ram/publish/htdocs/caches/'), true );
		$this->assertEquals( file_exists(__DIR__.'/testData/htdocs1/px-files/_sys/ram/publish/htdocs/common/styles/contents.css'), false );
		$this->assertEquals( file_exists(__DIR__.'/testData/htdocs1/px-files/_sys/ram/publish/publish_log.csv'), true );
	}






// describe('PHPを異常終了させるテスト', function() {
// 	var pj = getProject('htdocs2');
// 	var childProcRtnCode = 255;

// 	it("APIをコールするとPHPが異常終了するテスト", function(done) {
// 		this.timeout(5*1000);

// 		new Promise(function(rlv, rjc){
// 				pj.query('/?PX=phpinfo', {
// 					"output": "json",
// 					"userAgent": "Mozilla/5.0",
// 					"complete": function(data, code){
// 						// var_dump(data);
// 						$this->assertEquals( childProcRtnCode, code );
// 						rlv();
// 					}
// 				});
// 			})
// 			.then(function(){ return new Promise(function(rlv, rjc){
// 				pj.get_version(function(value, code, err){
// 					assert.strictEqual( value, false );
// 					$this->assertEquals( childProcRtnCode, code );
// 					$this->assertEquals( typeof(''), typeof(err) );
// 					// var_dump(code);
// 					// var_dump(err);
// 					rlv();
// 				});
// 			}); })
// 			.then(function(){ return new Promise(function(rlv, rjc){
// 				pj.get_config(function(value, code, err){
// 					assert.strictEqual( value, false );
// 					$this->assertEquals( childProcRtnCode, code );
// 					$this->assertEquals( typeof(''), typeof(err) );
// 					rlv();
// 				});
// 			}); })
// 			.then(function(){ return new Promise(function(rlv, rjc){
// 				pj.get_sitemap(function(value, code, err){
// 					assert.strictEqual( value, false );
// 					$this->assertEquals( childProcRtnCode, code );
// 					$this->assertEquals( typeof(''), typeof(err) );
// 					rlv();
// 				});
// 			}); })
// 			.then(function(){ return new Promise(function(rlv, rjc){
// 				pj.get_page_info('/', function(value, code, err){
// 					assert.strictEqual( value, false );
// 					$this->assertEquals( childProcRtnCode, code );
// 					$this->assertEquals( typeof(''), typeof(err) );
// 					rlv();
// 				});
// 			}); })
// 			.then(function(){ return new Promise(function(rlv, rjc){
// 				pj.get_parent('/sample_pages/', function(value, code, err){
// 					assert.strictEqual( value, false );
// 					$this->assertEquals( childProcRtnCode, code );
// 					$this->assertEquals( typeof(''), typeof(err) );
// 					rlv();
// 				});
// 			}); })
// 			.then(function(){ return new Promise(function(rlv, rjc){
// 				pj.get_children('/', function(value, code, err){
// 					assert.strictEqual( value, false );
// 					$this->assertEquals( childProcRtnCode, code );
// 					$this->assertEquals( typeof(''), typeof(err) );
// 					rlv();
// 				});
// 			}); })
// 			.then(function(){ return new Promise(function(rlv, rjc){
// 				pj.get_children('/', {filter: false}, function(value, code, err){
// 					assert.strictEqual( value, false );
// 					$this->assertEquals( childProcRtnCode, code );
// 					$this->assertEquals( typeof(''), typeof(err) );
// 					rlv();
// 				});
// 			}); })
// 			.then(function(){ return new Promise(function(rlv, rjc){
// 				pj.get_bros('/sample_pages/', function(value, code, err){
// 					assert.strictEqual( value, false );
// 					$this->assertEquals( childProcRtnCode, code );
// 					$this->assertEquals( typeof(''), typeof(err) );
// 					rlv();
// 				});
// 			}); })
// 			.then(function(){ return new Promise(function(rlv, rjc){
// 				pj.get_bros('/sample_pages/', {filter: false}, function(value, code, err){
// 					assert.strictEqual( value, false );
// 					$this->assertEquals( childProcRtnCode, code );
// 					$this->assertEquals( typeof(''), typeof(err) );
// 					rlv();
// 				});
// 			}); })
// 			.then(function(){ return new Promise(function(rlv, rjc){
// 				pj.get_bros_next('/sample_pages/', function(value, code, err){
// 					assert.strictEqual( value, false );
// 					$this->assertEquals( childProcRtnCode, code );
// 					$this->assertEquals( typeof(''), typeof(err) );
// 					rlv();
// 				});
// 			}); })
// 			.then(function(){ return new Promise(function(rlv, rjc){
// 				pj.get_bros_next('/sample_pages/', {filter: false}, function(value, code, err){
// 					assert.strictEqual( value, false );
// 					$this->assertEquals( childProcRtnCode, code );
// 					$this->assertEquals( typeof(''), typeof(err) );
// 					rlv();
// 				});
// 			}); })
// 			.then(function(){ return new Promise(function(rlv, rjc){
// 				pj.get_bros_prev('/sample_pages/', function(value, code, err){
// 					assert.strictEqual( value, false );
// 					$this->assertEquals( childProcRtnCode, code );
// 					$this->assertEquals( typeof(''), typeof(err) );
// 					rlv();
// 				});
// 			}); })
// 			.then(function(){ return new Promise(function(rlv, rjc){
// 				pj.get_bros_prev('/sample_pages/', {filter: false}, function(value, code, err){
// 					assert.strictEqual( value, false );
// 					$this->assertEquals( childProcRtnCode, code );
// 					$this->assertEquals( typeof(''), typeof(err) );
// 					rlv();
// 				});
// 			}); })
// 			.then(function(){ return new Promise(function(rlv, rjc){
// 				pj.get_next('/sample_pages/', function(value, code, err){
// 					assert.strictEqual( value, false );
// 					$this->assertEquals( childProcRtnCode, code );
// 					$this->assertEquals( typeof(''), typeof(err) );
// 					rlv();
// 				});
// 			}); })
// 			.then(function(){ return new Promise(function(rlv, rjc){
// 				pj.get_next('/sample_pages/', {filter: false}, function(value, code, err){
// 					assert.strictEqual( value, false );
// 					$this->assertEquals( childProcRtnCode, code );
// 					$this->assertEquals( typeof(''), typeof(err) );
// 					rlv();
// 				});
// 			}); })
// 			.then(function(){ return new Promise(function(rlv, rjc){
// 				pj.get_prev('/sample_pages/', function(value, code, err){
// 					assert.strictEqual( value, false );
// 					$this->assertEquals( childProcRtnCode, code );
// 					$this->assertEquals( typeof(''), typeof(err) );
// 					rlv();
// 				});
// 			}); })
// 			.then(function(){ return new Promise(function(rlv, rjc){
// 				pj.get_prev('/sample_pages/', {filter: false}, function(value, code, err){
// 					assert.strictEqual( value, false );
// 					$this->assertEquals( childProcRtnCode, code );
// 					$this->assertEquals( typeof(''), typeof(err) );
// 					rlv();
// 				});
// 			}); })
// 			.then(function(){ return new Promise(function(rlv, rjc){
// 				pj.get_breadcrumb_array('/sample_pages/', function(value, code, err){
// 					assert.strictEqual( value, false );
// 					$this->assertEquals( childProcRtnCode, code );
// 					$this->assertEquals( typeof(''), typeof(err) );
// 					rlv();
// 				});
// 			}); })
// 			.then(function(){ return new Promise(function(rlv, rjc){
// 				pj.get_dynamic_path_info('/sample_pages/', function(value, code, err){
// 					assert.strictEqual( value, false );
// 					$this->assertEquals( childProcRtnCode, code );
// 					$this->assertEquals( typeof(''), typeof(err) );
// 					rlv();
// 				});
// 			}); })
// 			.then(function(){ return new Promise(function(rlv, rjc){
// 				pj.bind_dynamic_path_param('/dynamicPath/{*}', {'':'abc.html'}, function(value, code, err){
// 					assert.strictEqual( value, false );
// 					$this->assertEquals( childProcRtnCode, code );
// 					$this->assertEquals( typeof(''), typeof(err) );
// 					rlv();
// 				});
// 			}); })
// 			.then(function(){ return new Promise(function(rlv, rjc){
// 				pj.get_realpath_homedir(function(value, code, err){
// 					assert.strictEqual( value, false );
// 					$this->assertEquals( childProcRtnCode, code );
// 					$this->assertEquals( typeof(''), typeof(err) );
// 					rlv();
// 				})
// 			}); })
// 			.then(function(){ return new Promise(function(rlv, rjc){
// 				pj.get_path_controot(function(value, code, err){
// 					assert.strictEqual( value, false );
// 					$this->assertEquals( childProcRtnCode, code );
// 					$this->assertEquals( typeof(''), typeof(err) );
// 					rlv();
// 				});
// 			}); })
// 			.then(function(){ return new Promise(function(rlv, rjc){
// 				pj.get_realpath_docroot(function(value, code, err){
// 					assert.strictEqual( value, false );
// 					$this->assertEquals( childProcRtnCode, code );
// 					$this->assertEquals( typeof(''), typeof(err) );
// 					rlv();
// 				});
// 			}); })
// 			.then(function(){ return new Promise(function(rlv, rjc){
// 				pj.get_path_content('/', function(value, code, err){
// 					assert.strictEqual( value, false );
// 					$this->assertEquals( childProcRtnCode, code );
// 					$this->assertEquals( typeof(''), typeof(err) );
// 					rlv();
// 				});
// 			}); })
// 			.then(function(){ return new Promise(function(rlv, rjc){
// 				pj.path_files('/', '/images/sample.png', function(value, code, err){
// 					assert.strictEqual( value, false );
// 					$this->assertEquals( childProcRtnCode, code );
// 					$this->assertEquals( typeof(''), typeof(err) );
// 					rlv();
// 				});
// 			}); })
// 			.then(function(){ return new Promise(function(rlv, rjc){
// 				pj.realpath_files('/', '/images/sample.png', function(value, code, err){
// 					assert.strictEqual( value, false );
// 					$this->assertEquals( childProcRtnCode, code );
// 					$this->assertEquals( typeof(''), typeof(err) );
// 					rlv();
// 				});
// 			}); })
// 			.then(function(){ return new Promise(function(rlv, rjc){
// 				pj.path_files_cache('/', '/images/sample.png', function(value, code, err){
// 					assert.strictEqual( value, false );
// 					$this->assertEquals( childProcRtnCode, code );
// 					$this->assertEquals( typeof(''), typeof(err) );
// 					rlv();
// 				});
// 			}); })
// 			.then(function(){ return new Promise(function(rlv, rjc){
// 				pj.realpath_files_cache('/', '/images/sample.png', function(value, code, err){
// 					assert.strictEqual( value, false );
// 					$this->assertEquals( childProcRtnCode, code );
// 					$this->assertEquals( typeof(''), typeof(err) );
// 					rlv();
// 				});
// 			}); })
// 			.then(function(){ return new Promise(function(rlv, rjc){
// 				pj.realpath_files_private_cache('/', '/images/sample.png', function(value, code, err){
// 					assert.strictEqual( value, false );
// 					$this->assertEquals( childProcRtnCode, code );
// 					$this->assertEquals( typeof(''), typeof(err) );
// 					rlv();
// 				});
// 			}); })
// 			.then(function(){ return new Promise(function(rlv, rjc){
// 				pj.get_domain(function(value, code, err){
// 					assert.strictEqual( value, false );
// 					$this->assertEquals( childProcRtnCode, code );
// 					$this->assertEquals( typeof(''), typeof(err) );
// 					rlv();
// 				});
// 			}); })
// 			.then(function(){ return new Promise(function(rlv, rjc){
// 				pj.get_directory_index(function(value, code, err){
// 					assert.strictEqual( value, false );
// 					$this->assertEquals( childProcRtnCode, code );
// 					$this->assertEquals( typeof(''), typeof(err) );
// 					rlv();
// 				});
// 			}); })
// 			.then(function(){ return new Promise(function(rlv, rjc){
// 				pj.get_directory_index_primary(function(value, code, err){
// 					assert.strictEqual( value, false );
// 					$this->assertEquals( childProcRtnCode, code );
// 					$this->assertEquals( typeof(''), typeof(err) );
// 					rlv();
// 				});
// 			}); })
// 			.then(function(){ return new Promise(function(rlv, rjc){
// 				pj.get_path_proc_type('/sample_pages/', function(value, code, err){
// 					assert.strictEqual( value, false );
// 					$this->assertEquals( childProcRtnCode, code );
// 					$this->assertEquals( typeof(''), typeof(err) );
// 					rlv();
// 				});
// 			}); })
// 			.then(function(){ return new Promise(function(rlv, rjc){
// 				pj.href('/sample_pages/', function(value, code, err){
// 					assert.strictEqual( value, false );
// 					$this->assertEquals( childProcRtnCode, code );
// 					$this->assertEquals( typeof(''), typeof(err) );
// 					rlv();
// 				});
// 			}); })
// 			.then(function(){ return new Promise(function(rlv, rjc){
// 				pj.is_match_dynamic_path('/sample_pages/', function(value, code, err){
// 					assert.strictEqual( value, false );
// 					$this->assertEquals( childProcRtnCode, code );
// 					$this->assertEquals( typeof(''), typeof(err) );
// 					rlv();
// 				});
// 			}); })
// 			.then(function(){ return new Promise(function(rlv, rjc){
// 				pj.is_page_in_breadcrumb('/sample_pages/', '/', function(value, code, err){
// 					assert.strictEqual( value, false );
// 					$this->assertEquals( childProcRtnCode, code );
// 					$this->assertEquals( typeof(''), typeof(err) );
// 					rlv();
// 				});
// 			}); })
// 			.then(function(){ return new Promise(function(rlv, rjc){
// 				pj.is_ignore_path('/sample_pages/', function(value, code, err){
// 					assert.strictEqual( value, false );
// 					$this->assertEquals( childProcRtnCode, code );
// 					$this->assertEquals( typeof(''), typeof(err) );
// 					rlv();
// 				});
// 			}); })
// 			.then(function(){ return new Promise(function(rlv, rjc){
// 				pj.publish({
// 					"success": function(output){
// 					},
// 					"complete":function(output, code){
// 						$this->assertEquals( childProcRtnCode, code );
// 						rlv();
// 					}
// 				});
// 			}); })
// 			.then(function(){ return new Promise(function(rlv, rjc){
// 				pj.clearcache({
// 					"success": function(output){
// 					},
// 					"complete":function(output, code){
// 						$this->assertEquals( childProcRtnCode, code );
// 						rlv();
// 					}
// 				});
// 			}); })
// 			.then(function(){
// 				done();
// 			})
// 		;

// 	});

// });


	/**
	 * キャッシュを削除するテスト
	 */
	public function testClearcache(){
		$px2proj = $this->factory->getProject('htdocs1');
		$output = $px2proj->clearcache();
		// var_dump($output);
		clearstatcache();
		$this->assertEquals( file_exists(__DIR__.'/testData/htdocs1/px-files/_sys/ram/publish/applock.txt'), false );
		$this->assertEquals( file_exists(__DIR__.'/testData/htdocs1/px-files/_sys/ram/publish/publish_log.csv'), false );
		$this->assertEquals( file_exists(__DIR__.'/testData/htdocs1/px-files/_sys/ram/publish/alert_log.csv'), false );
		$this->assertEquals( file_exists(__DIR__.'/testData/htdocs1/px-files/_sys/ram/publish/htdocs/'), false );
		$this->assertEquals( file_exists(__DIR__.'/testData/htdocs1/px-files/_sys/ram/publish/.gitkeep'), true );
	}

}
