<?php
declare(strict_types=1);

namespace Deployer;

require 'recipe/common.php';

set('application', 'wechat-miniprogrom-test');
set('deploy_path', '/var/www/miniapp-api');
set('update_code_strategy', 'local_archive');
set('keep_releases', 5);
set('shared_files', ['.env']);
// Configure framework-specific shared/writable directories when introduced.
set('shared_dirs', []);
set('writable_dirs', []);
set('bin/php', 'php8.5');

host('production')
    ->setHostname('202.61.241.19')
    ->setRemoteUser('deploy');

task('deploy', [
    'deploy:prepare',
    'deploy:vendors',
    'deploy:publish',
]);

after('deploy:failed', 'deploy:unlock');
