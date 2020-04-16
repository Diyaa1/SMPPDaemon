# SMPPDaemon

This daemon keep a pressistant connection with the smpp server, and respond to any request on port 49155 

# Installation

Before running the service be sure that's port 49155 is not used.

also check smpp connection settings in  "app\Helpers"

if its clear run this command in the base directory "nohup php launcher.php &"

this will create a daemon process in the background that listen for any incoming request on port 49155.
