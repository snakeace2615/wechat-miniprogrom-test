<?php
declare(strict_types=1);

namespace Deployer;

require 'recipe/common.php';

set('application', 'wechat-miniprogrom-test');
set('deploy_path', '/var/www/miniapp-api');
set('update_code_strategy', 'local_archive');
set('keep_releases', 5);

set('bin/php', '/usr/bin/php8.5');
set('bin/composer', '/usr/bin/php8.5 /usr/local/bin/composer');

// The starter app uses no production secrets. When the real API
// is introduced, provide the production .env via shared/.env.
set('shared_files', ['.env']);
set('shared_dirs', []);
set('writable_dirs', []);

host('production')
    ->setHostname('202.61.241.19')
    ->set('remote_user', 'deploy');

task('deploy', [
    'deploy:prepare',
    'deploy:vendors',
    'deploy:publish',
]);

after('deploy:failed', 'deploy:unlock');
