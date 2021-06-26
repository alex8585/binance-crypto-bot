@servers(['local' => ['127.0.0.1'], 'production'=>['root@109.94.208.179']])

@setup
    $now = now();
@endsetup


@task('local', ['on' =>['local'],],)
    cd /home/alex/projects/statistics
    php artisan migrate
    echo {{$now}}
@endtask

@task('prod', ['on' =>['production'],],)
    cd /var/www/www-root/data/www/bot/
    php artisan migrate
    echo {{$now}}
@endtask

@after
    if ($task === 'rc') {
        //echo $now;
    }
@endafter