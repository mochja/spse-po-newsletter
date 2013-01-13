# Foo Bar deployment script (Capistrano)

## basic setup stuff ##

# http://help.github.com/deploy-with-capistrano/
set :application, "spse newsletter"
set :repository, "git@github.com:mochja/newsletter.git"
set :scm, "git"
default_run_options[:pty] = true
set :user, "mochnak"


# use our keys, make sure we grab submodules, try to keep a remote cache
ssh_options[:forward_agent] = true
# set :git_enable_submodules, 1
set :deploy_via, :remote_cache
set :use_sudo, false

## multi-stage deploy process ###

# simple version @todo make db settings environment specific
# https://github.com/capistrano/capistrano/wiki/2.x-Multiple-Stages-Without-Multistage-Extension

task :dev do
  role :web, "spse-po.sk", :primary => true
  set :deploy_to, "/var/www-data/mochnak/newsletter"
  set :app_environment, "dev"
  set :branch, "master"
end

# task :staging do
#   role :web, "staging.davegardner.me.uk", :primary => true
#   set :app_environment, "staging"
#   # this is so we automatically deploy the latest numbered tag
#   # (with staging releases we use incrementing build number tags)
#   set :branch, `git tag | xargs -I@ git log --format=format:"%ci %h @%n" -1 @ | sort | awk '{print  $5}' | egrep '^b[0-9]+$' | tail -n 1`
# end

task :production do
  role :web, "spse-po.sk", :primary => true
  set :deploy_to, "/var/www-data/mochnak/newsletter"
  set :app_environment, "production"
  set :branch, "master"
end

## tag selection ##

# we will ask which tag to deploy; default = latest
# http://nathanhoad.net/deploy-from-a-git-tag-with-capistrano
# set :branch do
#   default_tag = `git describe --abbrev=0 --tags`.split("\n").last

#   tag = Capistrano::CLI.ui.ask "Tag to deploy (make sure to push the tag first): [#{default_tag}] "
#   tag = default_tag if tag.empty?
#   tag
# end unless exists?(:branch)

## php cruft ##

# https://github.com/mpasternacki/capistrano-documentation-support-files/raw/master/default-execution-path/Capistrano%20Execution%20Path.jpg
# https://github.com/namics/capistrano-php

set :copy_exclude, ["config/deploy*", "Capfile", "tests", "README.md", ".idea"]

namespace :deploy do
  task :create_release_dir, :except => {:no_release => true} do
    run "mkdir -p #{fetch :releases_path}"
  end

  task :finalize_update do
    transaction do
      run "chmod -R g+w #{releases_path}/#{release_name}"
      # run "chmod -R 777 #{releases_path}/#{release_name}/temp"
      # run "chmod -R 777 #{releases_path}/#{release_name}/log"
    end
  end

  task :migrate do
    # do nothing
  end

  task :restart, :except => { :no_release => true } do
  end

end


namespace :composer do
  desc "run composer install and ensure all dependencies are installed"
  task :install do
      # run "cd #{release_path} && composer install"
  end
end


after "deploy:setup", "deploy:create_release_dir"
after "deploy:finalize_update", "composer:install"
after "deploy:update", "deploy:cleanup"