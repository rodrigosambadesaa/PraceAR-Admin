<?php

declare(strict_types=1);

$serverIndex = 1;

$cfg['Servers'][$serverIndex]['auth_type'] = 'cookie';
$cfg['Servers'][$serverIndex]['AllowRoot'] = false;
$cfg['Servers'][$serverIndex]['AllowNoPassword'] = false;
$cfg['Servers'][$serverIndex]['only_db'] = ['dbs13217995'];

$cfg['AllowArbitraryServer'] = false;
$cfg['AllowThirdPartyFraming'] = false;
$cfg['AllowUserDropDatabase'] = false;
$cfg['Confirm'] = true;
$cfg['LoginCookieValidity'] = 1800;
$cfg['LoginCookieStore'] = 0;
$cfg['LoginCookieDeleteAll'] = true;
$cfg['SendErrorReports'] = 'never';
$cfg['ShowPhpInfo'] = false;
$cfg['ShowServerInfo'] = false;
$cfg['VersionCheck'] = false;
