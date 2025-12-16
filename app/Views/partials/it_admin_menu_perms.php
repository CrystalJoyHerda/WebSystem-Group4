<?php
$permissions = $permissions ?? [];
$path = service('uri')->getPath();
$canDashboard = in_array('admin.dashboard.view', $permissions, true);
$canUsers = in_array('user.manage', $permissions, true);
$canLogs = in_array('logs.view', $permissions, true);
$canAccess = in_array('access.view', $permissions, true);
$canBackup = in_array('backup.view', $permissions, true);
$canConfig = in_array('config.view', $permissions, true);
