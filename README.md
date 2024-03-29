##IP Address Mask for SMF
* __Version__ : v1.3.3.7
* __Websites__ : [InSecurity.ro](http://insecurity.ro/forum), [WorldIT.info](http://www.worldit.info)
* __Authors__ : Full installer by Andrei Avadanei (andrei [at] worldit [dot] info) & patch by kNight (intre0si1 [at] gmail [dot] com)

##Description

> IP Address Mask for SMF it's an semi-automatic patching tool for Simple Machine Forum which masks all real IPs of users that are belonging to some groups from a SMF drived-by website. You can include or exclude an unlimited number of groups for being protected by IPAM 4 SMF. Even more, you can choose even the mask (it could be a dynamic set) that will patch users IPs. IPAM 4 SMF have options like installation guider, automatic backup for modified files, patch remover. 

##Features
### v1.3.3.7 
  * full support for IPv4 and partial support for IPv6
  * installer, backup patched files and remote patch options
  * check if SMF is already patched
  * support for dynamic IP addresses (full support for IPv4; for example, 133.37.*.* may show (almost) random IPs based on several internal properties)
  * tested on SMF 2.x

##Installation
  * upload all files in the root of your server
  * ensure that you have write permissions for /backup folder
  * run IPAM 4 SMFs index.php in your browser

##Previews
![alt text](https://github.com/AndreiAvadanei/IPAM-4-SMF/raw/master/previews/1.png "")
![alt text](https://github.com/AndreiAvadanei/IPAM-4-SMF/raw/master/previews/2.png "")
![alt text](https://github.com/AndreiAvadanei/IPAM-4-SMF/raw/master/previews/3.png "")
![alt text](https://github.com/AndreiAvadanei/IPAM-4-SMF/raw/master/previews/4.png "")
![alt text](https://github.com/AndreiAvadanei/IPAM-4-SMF/raw/master/previews/5.png "")
##TODO 
  * support for updating an existing patch
  * full support for IPv6
  * remove IP from $_SERVER['REMOTE_ADDR']
  
##License
GNU GENERAL PUBLIC LICENSE, Version 3, 29 June 2007
