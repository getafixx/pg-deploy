<?php
namespace Deployer;

require 'recipe/laravel-deployer.php';

require __DIR__ .'/decode.php';

// Project name
set('application', 'deploy.paint.garden');

// Project repository
define('REPO', 'git@github.com:getafixx/pg-deploy.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', false); 


set('keep_releases', 5);

set('branch', 'master');

// If we don't need sudo, keep it false
set('writable_use_sudo', true);

set('repository', REPO);

set('shared_files', [
    '.env',
    'storage/oauth-private.key',
    'storage/oauth-public.key',
]);

set('shared_dirs', [
    'storage/app',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
    
    // app shared
    'public/uploads',
    'public/rawuploads',
]);


set('writable_dirs', ['bootstrap/cache', 'storage']);

task('artisan:clear-compiled', function () {
    $output = run('{{bin/php}} {{deploy_path}}/current/artisan clear-compiled');
    writeln('<info>'.$output.'</info>');
})->desc('Clear compiled done');

task('artisan:optimize', function () {
    $output = run('{{bin/php}} {{deploy_path}}/current/artisan optimize');
    writeln('<info>'.$output.'</info>');
})->desc('Optimize done');

task('artisan:cache:clear', function () {
    $output = run('{{bin/php}} {{deploy_path}}/current/artisan cache:clear');
    writeln('<info>'.$output.'</info>');
})->desc('Clear cache');

task('after-deploy', [
    'artisan:clear-compiled',
    'artisan:optimize',
    'artisan:cache:clear',
])->desc('Deploying done.');

// migrate
task('database:migrate', function () {
    run('php {{release_path}}/' . 'artisan migrate');
})->desc('Migrate database');

task('reload:php-fpm', function () {
    run('sudo service php7.2-fpm reload');
});

task('write-file', function () {
    
    //get the git commit hash id
    $rev = run('git ls-remote {{repository}} refs/heads/{{branch}}');
    preg_match('/[a-zA-Z0-9]{40}/', $rev, $output_array);
    $commit = $output_array[0];
    $date = date('Y-m-d H:i:s');
    $release = get('releases_list')[0];
    writeln('<info>release_name = '.$release.'</info>');
    writeln('<info>date = '.$date.'</info>');
    writeln('<info>branch = '.get('branch').'</info>');
    writeln('<info>commit = '.$commit.'</info>');
    
    $data = [   'date'=>$date,
                'release_id'=> $release,
                'branch'=> get('branch'),
                'commit'=>$commit,
            ];

    $json = json_encode($data, JSON_PRETTY_PRINT);
    $coded = encode_data($json);
    set('coded', $coded);
    run('printf "{{coded}}" > {{release_path}}/storage/deployRef.enc');
});

// optimize
task('deploy:optimize', function () {
    run('php {{release_path}}/' . 'artisan optimize');
    run('php {{release_path}}/' . 'artisan route:cache');
    run('php {{release_path}}/' . 'artisan config:cache');
})->desc('Optimize Application');

/**
 * Main task
 */
task('deploy', ['deploy:prepare',
    'deploy:release',
    'deploy:update_code',
    'deploy:vendors',
    'deploy:shared',
    //'database:migrate',
    'deploy:symlink',
])->desc('Deploy your project');

after('deploy', 'success');
after('deploy', 'write-file');
after('deploy', 'cleanup');

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Migrate database before symlink new release.
//before('deploy:symlink', 'artisan:migrate');


set('deploy_path', '/home/justin/sites/paint.garden/test');
set('user', 'getafixx');
set('http_user', 'justin');

//set('configFile', '/home/.ssh/config');
set('identityFile', '/home/www-data/.ssh/pg-deploy');
set('http_user', 'root');
set('forwardAgent', true);
set('multiplexing', true);

$sshOptions =  [ 
            'UserKnownHostsFile' => '/dev/null',
            'StrictHostKeyChecking' => 'no',
            // ...
        ];
set('sshOptions', $sshOptions);

task('paint.garden.deploytest', function() {


    invoke('deploy:prepare');
    invoke('deploy:release');
    invoke('deploy:update_code');
    //copyShared('{{previous_release}}', '{{release_path}}');
    invoke('deploy:shared');
    invoke('deploy:vendors');
    //invoke('hook:build');       // Any tasks hooked to `build` will be called locally
    invoke('deploy:symlink');
    invoke('cleanup');
})->local();
    

