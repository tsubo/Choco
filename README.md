Choco
=====

Choco is a simple, flexible, fast, flat file CMS.

必要事項
------------
1. PHP 5.4+ が使えること
2. Apacheで mod_rewriteが使えること

インストール
------------
### 手動インストール
Choco の[最新版をダウンロード](https://github.com/tsubo/Choco/zipball/master)して解凍

### git clone でインストール
```
git clone https://github.com/tsubo/Choco.git
```

### composer でライブラリをインストール
```
cd your_choco_dir
curl -s https://getcomposer.org/installer | php
php composer.phar install
```

PHPビルトインサーバで動作確認
-----------------------------
```
cd your_choco_dir/webroot
php -S localhost:8000
```

wwwブラウザで http://localhost:8000 を表示

ドキュメント
-----------------------------
- [公式サイト](http://tsubo.github.io/Choco/)
- [ドキュメント](https://github.com/tsubo/Choco/wiki)

License
-----------------------------
The Choco CMS is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
