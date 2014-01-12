set :application, 'spse-newsletter'
set :repo_url, 'git@github.com:mochja/newsletter.git'

set :branch, 'master'

set :deploy_to, '/var/www-data/mochnak/newsletter'
set :scm, :git

set :format, :pretty
# set :log_level, :debug
# set :pty, true

# set :linked_files, %w{config/database.yml}
set :linked_dirs, %w{log temp vendor}

# set :default_env, { path: "/opt/ruby/bin:$PATH" }
set :keep_releases, 1
set :tmp_dir, "#{shared_path.join("../tmp")}"

SSHKit.config.command_map[:php] = "php -d suhosin.executor.include.whitelist=phar"
SSHKit.config.command_map[:composer] = "php -d suhosin.executor.include.whitelist=phar #{shared_path.join("composer.phar")}"

namespace :deploy do

  before :starting, 'composer:install_executable'

  desc 'Restart application'
  task :restart do
    on roles(:app), in: :sequence, wait: 5 do
      within release_path do
        execute :cp, '-rf public/* /var/www/newsletter'
      end
    end
  end

  after :restart, :clear_cache do
    on roles(:web), in: :groups, limit: 3, wait: 10 do
      # Here we can do anything such as:
      # within release_path do
      #   execute :rake, 'cache:clear'
      # end
    end
  end

  after :finishing, 'deploy:cleanup'

end
