set :application, "noodleDig"
set :deploy_to,   "/public_html/noodledig.com"
set :domain,      "artemis.web-hosting.uk.com"
set :user,        "simonker"

set :scm,         :git
set :repository,  "git@github.com:simonpkerr/music-memories.git"
set :deploy_via,  :remote_cache

#set :model_manager, "doctrine"

role :web,        domain                         # Your HTTP server, Apache/etc
role :app,        domain                         # This may be the same as your `Web` server
role :db,         domain, :primary => true       # This is where Symfony2 migrations will run

set :use_sudo, false
set :keep_releases,  3

set :shared_files,      ["app/config/parameters.yml"]
set :shared_children, [app_path + "/logs", web_path + "/uploads", "vendor"]

set :use_composer, true
set :copy_vendors, false
set :dump_assetic_assets, true

# Be more verbose by uncommenting the following line
# logger.level = Logger::MAX_LEVEL