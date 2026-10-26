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

// PostgreSQL credentials are provided by the VPS shared/.env.
set('shared_files', ['.env']);
set('shared_dirs', []);
set('writable_dirs', []);

host('production')
    ->setHostname('202.61.241.19')
    ->set('remote_user', 'deploy');

task('database:migrate', function () {
    run('{{bin/php}} {{release_path}}/scripts/migrate.php');
});

task('deploy', [
    'deploy:prepare',
    'deploy:vendors',
    'database:migrate',
    'deploy:publish',
]);

after('deploy:failed', 'deploy:unlock');
