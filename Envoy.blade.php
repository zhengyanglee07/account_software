@servers(['staging' => ['gitlab-deployer@165.22.104.154 -p 8321 -o StrictHostKeyChecking=no']])


@story('deploy-staging')
  update-code
  install-composer-dependencies
  re-cache-view-and-config
  update-database
  restart-queue
  install-npm-dependencies
  run-prod-build
@endstory


@task('update-code')
	cd /var/www/rapportstar
  git checkout {{ $branch ?? 'main' }}
  git pull origin {{ $branch ?? 'main' }}
@endtask

@task('install-composer-dependencies')
  composer install --prefer-dist --no-ansi --no-interaction --no-progress --no-dev --optimize-autoloader --no-scripts --quiet
@endtask

@task('re-cache-view-and-config')
  php artisan cache:clear;
  php artisan view:cache;
  php artisan config:cache;
@endtask

@task('update-database')
  composer dump-autoload
  php artisan migrate
@endtask

@task('restart-queue')
  php artisan queue:restart
@endtask

@task('install-npm-dependencies')
  npm ci --ignore-scripts
@endtask

@task('run-prod-build')
  npm run prod
@endtask
