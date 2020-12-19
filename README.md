# php-px2agent
__pickles2/px2agent__ は、[Pickles 2](https://pickles2.pxt.jp/)  と PHP スクリプトを仲介するAPIを提供します。


<table>
  <thead>
	<tr>
	  <th></th>
	  <th>Linux</th>
	  <th>Windows</th>
	</tr>
  </thead>
  <tbody>
	<tr>
	  <th>master</th>
	  <td align="center">
		<a href="https://travis-ci.org/pickles2/php-px2agent"><img src="https://secure.travis-ci.org/pickles2/php-px2agent.svg?branch=master"></a>
	  </td>
	  <td align="center">
		<a href="https://ci.appveyor.com/project/tomk79/php-px2agent"><img src="https://ci.appveyor.com/api/projects/status/7tbg9d7fb8yvb4ij/branch/master?svg=true"></a>
	  </td>
	</tr>
	<tr>
	  <th>develop</th>
	  <td align="center">
		<a href="https://travis-ci.org/pickles2/php-px2agent"><img src="https://secure.travis-ci.org/pickles2/php-px2agent.svg?branch=develop"></a>
	  </td>
	  <td align="center">
		<a href="https://ci.appveyor.com/project/tomk79/php-px2agent"><img src="https://ci.appveyor.com/api/projects/status/7tbg9d7fb8yvb4ij/branch/develop?svg=true"></a>
	  </td>
	</tr>
  </tbody>
</table>




## インストール - Installation

```
$ composer require pickles2/px2agent;
```



## 使い方 - Usage

```php
$px2agent = new picklesFramework2\px2agent\px2agent();
$px2proj = $px2agent->createProject('/path/to/.px_execute.php');


/**
 * Pickles 2 にクエリを投げて、結果を受け取る (汎用)
 */
$data = $px2proj->query('/?PX=phpinfo', array(
	"output": "json",
));

/**
 * PXコマンドを実行する
 */
$result = $px2proj->px_command(
    'publish.run',
    '/index.html',
    array('path_region' => "/region/")
);

/**
 * バージョン番号を取得する
 */
$value = $px2proj->get_version();


/**
 * configデータを取得する
 */
$value = $px2proj->get_config();

/**
 * サイトマップデータを取得する
 */
$value = $px2proj->get_sitemap();

/**
 * pathまたはidからページ情報を得る
 */
$value = $px2proj->get_page_info('/');

/**
 * 親ページのIDを取得する
 */
$value = $px2proj->get_parent('/sample_pages/');

/**
 * 子階層のページの一覧を取得する
 */
$value = $px2proj->get_children('/');

/**
 * 子階層のページの一覧を、filterを無効にして取得する
 */
$value = $px2proj->get_children('/', array('filter' => false));

/**
 * 同じ階層のページの一覧を取得する
 */
$value = $px2proj->get_bros('/sample_pages/');

/**
 * 同じ階層のページの一覧を、filterを無効にして取得する
 */
$value = $px2proj->get_bros('/sample_pages/', array('filter' => false));

/**
 * 同じ階層の次のページのIDを取得する
 */
$value = $px2proj->get_bros_next('/sample_pages/');

/**
 * 同じ階層の次のページのIDを、filterを無効にして取得する
 */
$value = $px2proj->get_bros_next('/sample_pages/', array('filter' => false));

/**
 * 同じ階層の前のページのIDを取得する
 */
$value = $px2proj->get_bros_prev('/sample_pages/');

/**
 * 同じ階層の前のページのIDを、filterを無効にして取得する
 */
$value = $px2proj->get_bros_prev('/sample_pages/', array('filter' => false));

/**
 * 次のページのIDを取得する
 */
$value = $px2proj->get_next('/sample_pages/');

/**
 * 次のページのIDを、filterを無効にして取得する
 */
$value = $px2proj->get_next('/sample_pages/', array('filter' => false));

/**
 * 前のページのIDを取得する
 */
$value = $px2proj->get_prev('/sample_pages/');

/**
 * 前のページのIDを、filterを無効にして取得する
 */
$value = $px2proj->get_prev('/sample_pages/', array('filter' => false));

/**
 * パンくず配列を取得する
 */
$value = $px2proj->get_breadcrumb_array('/sample_pages/');

/**
 * ダイナミックパス情報を得る
 */
$value = $px2proj->get_dynamic_path_info('/sample_pages/');

/**
 * ダイナミックパスに値をバインドする
 */
$value = $px2proj->bind_dynamic_path_param('/dynamicPath/{*}', array('' => 'abc.html'));

/**
 * role を取得する
 */
$role = $px2proj->get_role('/sample_pages/actor1.html');

/**
 * Actor のページID一覧を取得する
 */
$actors = $px2proj->get_actors('/sample_pages/role.html');

/**
 * get home directory path
 */
$value = $px2proj->get_realpath_homedir();

/**
 * コンテンツルートディレクトリのパス(=install path) を取得する
 */
$value = $px2proj->get_path_controot();

/**
 * DOCUMENT_ROOT のパスを取得する
 */
$value = $px2proj->get_realpath_docroot();

/**
 * get content path
 */
$value = $px2proj->get_path_content('/');

/**
 * ローカルリソースディレクトリのパスを得る
 */
$value = $px2proj->path_files('/', '/images/sample.png');

/**
 * ローカルリソースディレクトリのサーバー内部パスを得る
 */
$value = $px2proj->realpath_files('/', '/images/sample.png');

/**
 * ローカルリソースのキャッシュディレクトリのパスを得る
 */
$value = $px2proj->path_files_cache('/', '/images/sample.png');

/**
 * ローカルリソースのキャッシュディレクトリのサーバー内部パスを得る
 */
$value = $px2proj->realpath_files_cache('/', '/images/sample.png');

/**
 * コンテンツ別の非公開キャッシュディレクトリのサーバー内部パスを得る
 */
$value = $px2proj->realpath_files_private_cache('/', '/images/sample.png');

/**
 * domain を取得する
 */
$value = $px2proj->get_domain();

/**
 * directory_index(省略できるファイル名) の一覧を得る
 */
$value = $px2proj->get_directory_index();

/**
 * 最も優先されるインデックスファイル名を得る
 */
$value = $px2proj->get_directory_index_primary();

/**
 * ファイルの処理方法を調べる
 */
$value = $px2proj->get_path_proc_type('/sample_pages/');

/**
 * リンク先のパスを生成する
 */
$value = $px2proj->href('/sample_pages/');

/**
 * パスがダイナミックパスにマッチするか調べる
 */
$value = $px2proj->is_match_dynamic_path('/sample_pages/');

/**
 * ページが、パンくず内に存在しているか調べる
 */
$value = $px2proj->is_page_in_breadcrumb('/sample_pages/', '/');

/**
 * 除外ファイルか調べる
 */
$value = $px2proj->is_ignore_path('/sample_pages/');


/**
 * パブリッシュする
 */
$output = $px2proj->publish( array(
	"path_region" => "/path/region/",
	"paths_region" => array(
		"/path/region1/",
		"/path/region2/"
	),
	"paths_ignore" => array(
		"/path/region/ignored/1/",
		"/path/region/ignored/2/"
	),
	"keep_cache" => 1,
) );

/**
 * キャッシュを削除する
 */
$output = $px2proj->clearcache();
```


### PHPバイナリのパスを指定する場合 - Specifying path to PHP binary

```php
$px2agent = new picklesFramework2\px2agent\px2agent( array(
    'bin' => '/path/to/php',
    'ini' => '/path/to/php.ini',
    'extension_dir' => '/path/to/ext/',
) );
$px2proj = $px2agent->createProject('/path/to/.px_execute.php');
```


## 開発者向け情報 - for developers

### 開発環境セットアップ - Setting up development environment

```bash
$ cd {$project_root}
$ composer install
```

### テスト - Test

```bash
$ composer test
```

### ドキュメント出力 - JSDoc

```bash
$ composer run-script documentation
```



## 更新履歴 - Change log

### pickles2/px2agent v0.0.2 (リリース日未定)

- Windows で、コマンド中に `%` が含まれる場合に失敗する問題を修正。

### pickles2/px2agent v0.0.1 (2020年8月12日)

- Initial Release.


## ライセンス - License

MIT License


## 作者 - Author

- (C)Tomoya Koyanagi <tomk79@gmail.com>
- website: <https://www.pxt.jp/>
- Twitter: @tomk79 <https://twitter.com/tomk79/>
