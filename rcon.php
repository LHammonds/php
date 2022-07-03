<?php
#############################################################
## Name          : rcon.php
## Version       : 1.0
## Date          : 2022-07-01
## Author        : LHammonds
## Source        : https://github.com/LHammonds/php/blob/master/rcon.php
## Purpose       : Linux command line utility to execute rcon commands
## Compatibility : Verified on Ubuntu Server 22.04 LTS
## Requirements  : apt install php7.4-cli
## Run Frequency : As needed.
## Parameters    :
##   -f [REQUIRED] "/path/to/file.ini" (File that holds the RCON password)
##   -c [REQUIRED] "command" (RCON command to be sent to the RCON server)
##   -a [OPTIONAL] 127.0.0.1 (IP address of the RCON server)
##   -p [OPTIONAL] 27015 (Port number that RCON is listening on)
##   -v [OPTIONAL] verbose mode
##   -? [OPTIONAL] show usage
## Examples:
##   php rcon.php -f /etc/rcon-island.ini -c "SaveWorld"
##   php rcon.php -f /etc/rcon.ini -a 127.0.0.1 -p 27015 -c "Broadcast Hello"
## Exit Codes    :
##    0 = Success
##    1 = Missing file parameter
##    2 = Missing command parameter
##    3 = Missing address parameter/config
##    4 = Missing port parameter/config
##    5 = Invalid file or format
##    6 = Connect failed
######################## CHANGE LOG #########################
## DATE       VER WHO WHAT WAS CHANGED
## ---------- --- --- ---------------------------------------
## 2012-07-01 1.0 LTH Created program.
#############################################################
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
//error_reporting(E_ALL);

function f_display_help($progname) {
  echo "\nCommand Syntax:\n";
  echo "  ",$progname," <-f \"/path/to/file.ini\"> <-c \"command\"> [-a IPNumber] [-p Port] [-v] [-?]\n";
  echo "\n";
  echo "Examples:\n";
  echo "  ",$progname," -f /etc/rcon.ini -c \"ServerChat Hello\"\n";
  echo "  ",$progname," -f /etc/rcon-island.ini -a 127.0.0.1 -p 27162 -c \"Broadcast Hello\"\n";
  echo "  ",$progname," -f /etc/rcon-ragnarok.ini -c \"SaveWorld\"\n";
  echo "  ",$progname," -f /etc/rcon.ini -c \"ListPlayers\" -p 27160\n";
  echo "\n";
} ## f_display_help() ##

## Display help if no parameters were given. ##
if ($argc == 1) {
  f_display_help($argv[0]);
  exit(1);
}
if ($argv[1] == "-?") {
  f_display_help($argv[0]);
  exit(1);
}
## Initialize variables ##
$rconfile    = "";
$rconip      = "";
$rconport    = "";
$rconcmd     = "";
$rcontimeout = 3;
$verbose     = 0;
## Grab parameters. ##
for ($i=0; $i < $argc; $i++) {
  if ($argv[$i] == "-f") {
    $rconfile = $argv[$i+1];
  }
  if ($argv[$i] == "-c") {
    $rconcmd = $argv[$i+1];
  }
  if ($argv[$i] == "-a") {
    $rconip = $argv[$i+1];
  }
  if ($argv[$i] == "-p") {
    $rconport = $argv[$i+1];
  }
  if ($argv[$i] == "-v") {
    $verbose = 1;
  }
}
## Required parameter validation. ##
if ($rconfile == "") {
  f_display_help($argv[0]);
  echo "[ERROR] Required file parameter is missing.\n";
  exit(1);
} else {
  if (!file_exists($rconfile)) {
    echo "[ERROR] File does not exist: ",$rconfile,"\n";
    exit(5);
  }
}
if ($rconcmd == "") {
  f_display_help($argv[0]);
  echo "[ERROR] Required command parameter is missing.\n";
  exit(2);
}
## Read RCON file settings. ##
$ini      = parse_ini_file($rconfile);
$rconpwd  = $ini['password'];
$iniip    = $ini['ipaddress'];
$iniport  = $ini['port'];
if ($rconip == "") {
  ## IP was not specified on command-line. ##
  $rconip = $iniip;
}
if ($rconport == "") {
  ## Port was not specified on command-line. ##
  $rconport = $iniport;
}
## Validate IP. ##
if (!filter_var($rconip,FILTER_VALIDATE_IP)) {
  f_display_help($argv[0]);
  echo "[ERROR] IP address is not valid: ",$rconip,"\n";
  exit(3);
}
## Validate Port. ##
if (!is_numeric($rconport)) {
  f_display_help($argv[0]);
  echo "[ERROR] RCON port is not valid: ",$rconport,"\n";
  exit(4);
}
## Output parameters.
if ($verbose == 1) {
  echo "\n[INFO] rconfile:",$rconfile,", rconip:",$rconip,", rconport:",$rconport,", rconcmd:",$rconcmd,"\n";
}
## Source: https://github.com/thedudeguy/PHP-Minecraft-Rcon
require_once('rcon-lib.php');
use Thedudeguy\Rcon;
$rcon = new Rcon($rconip, $rconport, $rconpwd, $rcontimeout);
if ($rcon->connect())
{
  $rcon->sendCommand($rconcmd);
  echo $rcon->getResponse(),"\n";
  $rcon->disconnect();
} else {
  echo "\n[ERROR] Failed to connect to ",$rconip,"\n";
  echo $rcon->getResponse(),"\n";
  exit(6);
}
exit(0);
?>
