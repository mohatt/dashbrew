## This file is to be included in vhosts files in order to use php <?= $build ?> fpm
<FilesMatch "\.php$">
	Require all granted
	SetHandler "proxy:fcgi://127.0.0.1:<?= $port ?>/"
</FilesMatch>
