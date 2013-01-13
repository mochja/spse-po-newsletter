# Foo Bar deployment script (Capistrano)

## basic setup stuff ##

# http://help.github.com/deploy-with-capistrano/
set :application, "newsletter"
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

task :production do
  role :web, "spse-po.sk", :primary => true
  set :deploy_to, "/var/www-data/mochnak/newsletter/"
  set :app_environment, "production"
  set :branch, "master"
end

# task :staging do
#   role :web, "staging.davegardner.me.uk", :primary => true
#   set :app_environment, "staging"
#   # this is so we automatically deploy the latest numbered tag
#   # (with staging releases we use incrementing build number tags)
#   set :branch, `git tag | xargs -I@ git log --format=format:"%ci %h @%n" -1 @ | sort | awk '{print  $5}' | egrep '^b[0-9]+$' | tail -n 1`
# end

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
      run "chmod -R 777 #{releases_path}/#{release_name}/temp"
      run "chmod -R 777 #{releases_path}/#{release_name}/log"
      # run our build script
      # run "echo '#{app_environment}' > #{releases_path}/#{release_name}/config/environment.txt"
      # run "cd #{releases_path}/#{release_name} && phing build"
    end
  end

  task :migrate do
    # do nothing
  end

  task :restart, :except => { :no_release => true } do
    # reload nginx config
    # run "sudo service nginx reload"
  end

  # after "deploy", :except => { :no_release => true } do
  #   # run "cd #{releases_path}/#{release_name} && phing spawn-workers > /dev/null 2>&1 &", :pty => false
  # end
end


namespace :composer do
  desc "run composer install and ensure all dependencies are installed"
  task :install do
      run "cd #{release_path} && composer install"
  end
end

# ==============================
# Uploads
# ==============================

# namespace :uploads do

#     # Creates the upload folders unless they exist
#     # and sets the proper upload permissions.
#   task :setup, :except => { :no_release => true } do
#     dirs = uploads_dirs.map { |d| File.join(shared_path, d) }
#     run "#{try_sudo} mkdir -p #{dirs.join(' ')} && #{try_sudo} chmod g+w #{dirs.join(' ')}"
#   end

#   # desc <<-EOD
#   #   [internal] Creates the symlink to uploads shared folder
#   #   for the most recently deployed version.
#   # EOD
#   task :symlink, :except => { :no_release => true } do
#     run "rm -rf #{release_path}/public/uploads"
#     run "ln -nfs #{shared_path}/uploads #{release_path}/public/uploads"
#   end

#   # desc <<-EOD
#   #   [internal] Computes uploads directory paths
#   #   and registers them in Capistrano environment.
#   # EOD
#   task :register_dirs do
#     set :uploads_dirs,    %w(uploads uploads/partners)
#     set :shared_children, fetch(:shared_children) + fetch(:uploads_dirs)
#   end

#   after       "deploy:finalize_update", "uploads:symlink"
#   on :start,  "uploads:register_dirs"

# end


after "deploy:setup", "deploy:create_release_dir"
after "deploy:finalize_update"
after "deploy:update", "deploy:cleanup"