include_recipe "build-essential"
include_recipe "mysql"
include_recipe "mysql::server"
include_recipe "apache2"
include_recipe "apache2::mod_ssl"
include_recipe "apache2::mod_rewrite"

# Install php 5.4
execute "rpm --force -Uvh http://repo.webtatic.com/yum/el6/latest.rpm"
execute "yum --enablerepo=webtatic -y install svn git php54w-devel php54w-gd php54w-imap php54w-mbstring php54w-mysql php54w-pdo php54w-pecl-apc php54w-pecl-memcache php54w-snmp php54w-soap php54w-xml php54w-xmlrpc"

# Disable iptables for development box
execute "service iptables stop"  
execute "chkconfig iptables off"  

# Vagrant has a speical kernel, which cannot be updated with yum
execute "echo 'exclude=kernel*' >> /etc/yum.conf"

execute "cd /vagrant && COMPOSER_PROCESS_TIMEOUT=4000 php composer.phar install"

 web_app "web_app" do
   docroot "/var/webapp/www/public"
   template "project.conf.erb"
   server_name "webapp.dev"
   server_aliases [node[:hostname], "webapp.dev"]
   notifies :reload, resources(:service => "apache2"), :delayed
 end
