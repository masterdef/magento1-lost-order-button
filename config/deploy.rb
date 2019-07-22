set :application, "magento"
set :domain,      "lost-order-admin.xpscommerce.com"
set :deploy_to,   "/var/www/#{domain}"

set :repository,  "git@github.com:masterdef/magento1-lost-order-button.git"
set :scm,         :git
# Or: `accurev`, `bzr`, `cvs`, `darcs`, `subversion`, `mercurial`, `perforce`, `subversion` or `none`

role :web, domain
role :app, domain   # This may be the same as your `Web` server or a separate administration server
#server "50.116.8.93", user: "deploy", roles: %w{web app db}, port: 41545

set :port, 41545

set  :keep_releases,  3

set :app_symlinks, ["/media", "/var", "/sitemaps", "/staging"]
set :app_shared_dirs, ["/app/etc", "/sitemaps", "/media", "/var", "/staging"]
set :app_shared_files, ["/app/etc/local.xml"]
