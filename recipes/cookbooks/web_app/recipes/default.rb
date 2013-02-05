include_recipe "build-essential"
include_recipe "mysql"
include_recipe "mysql::server"
include_recipe "apache2"
include_recipe "apache2::mod_ssl"
include_recipe "apache2::mod_rewrite"

execute "rpm --force -Uvh http://repo.webtatic.com/yum/el6/latest.rpm"
execute "yum --enablerepo=webtatic -y install php54w-devel php54w-gd php54w-imap php54w-mbstring php54w-mysql php54w-pdo php54w-pecl-apc php54w-pecl-memcache php54w-snmp php54w-soap php54w-xml php54w-xmlrpc"

command 'service iptables stop'  
command 'chkconfig iptables off'  


 web_app "web_app" do
   docroot "/var/webapp/www/public"
   template "project.conf.erb"
   server_name "webapp.dev"
   server_aliases [node[:hostname], "webapp.dev"]
   notifies :reload, resources(:service => "apache2"), :delayed
 end
