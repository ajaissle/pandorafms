<?php

// Pandora - the Free monitoring system
// ====================================
// Copyright (c) 2004-2006 Sancho Lerena, slerena@gmail.com
// Copyright (c) 2005-2006 Artica Soluciones Tecnológicas S.L, info@artica.es
// Copyright (c) 2004-2006 Raul Mateos Martin, raulofpandora@gmail.com
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.


?>
<html>
<head>
<title>Pandora - The Free Monitoring System Help - VIII. Database maintenance</title>
<link rel="stylesheet" href="../../include/styles/pandora.css" type="text/css">
<style>
div.logo {float:left;}
div.toc {padding-left: 200px;}
div.rayah {clear:both; border-top: 1px solid #708090; width: 100%;}
</style>
</head>

<body>
<div class='logo'>
<img src="../../images/logo_menu.gif" alt='logo'><h1>Pandora Help v1.2</h1>
</div>
<div class="toc">
<h1><a href="chap7.php">7. Pandora Servers</a> « <a href="toc.php">Table of Contents</a> » <a href="chap9.php">9. Pandora Configuration</a></h1>

</div>
<div class="rayah">
<p align='right'>Pandora is a GPL Software Project. &copy; Sancho Lerena 2003-2006, David villanueva 2004-2005, Alex Arnal 2005, Ra&uacute;l Mateos 2004-2006.</p>
</div>

<a name="8"><h1>8. Database Maintenance</h1></a>

<p>The core of Pandora's system is its Database. All the data collected by the monitored
machines is stored in this data base, from the administrator's data, to the
events, incidents and audit data generated in the system at any time.</p>

<p>It is obvious that the efficiency and reliability of this module is vital for the correct functioning of Pandora. A regular data base maintainance is needed. To do so the data base managers can
use standard MySQL commands. Maintaining Pandora database in good condition is critital for Pandora to work properly.</p>

<p>As the database size will increase linearly, the data will be compacted to reduce the amount of stored data without loosing important information, specially the different graphs that are generated with
the processed data.</p>

<p>Going to "DB Maintenance" from the Administration menu we will find the Database configuration defined in the "Pandora Setup" option of the Administration menu to compact and delete data.</p>

<p class="center"><img src="images/image054.png"></p>

<h2><a name="81">8.1. DB Information</a></h2>

<p>The DB statistics are generated by Agent, on the "DB Maintenance"&gt;"DB Information" in the Administration menu, and are represented in two kinds of graphs:</p>

<ul>
<li>Number of modules configured for each of the agents.</li>
<li>Number of packages sent by each agent. A package is the group of data linked to the module the agent sends in each interval of time.</li>
</ul>

<p class="center">
<img src="images/image055.png"><br>
<img src="images/image056.png"><br>
<img src="images/image057.png"><br>
</p>

These graphs can be also viewed from "View Agents"&gt;"Statistics" in the Operation menu.</p>

<h2><a name="82">8.2. Manual purge of the Datadase</a></h2>

<p>Pandora counts with powerful tools for the
Administrator to manually purge the majority of data stored in the Database.
This includes data generated by both the agents and the own server.</p>

<h2><a name="83">8.3. Agent's data purge</a></h2>

<h3><a name="831">8.3.1. Debuging selected data from a module</a></h3>

<p>The option of purging selected data from a module is used to eliminate those out of range entries, whatever the reason – agent failure, out of range values, testing, DB errors, etc. Eliminating erroneous, incorrect or unnecessary data makes the graphical representation more acuarate and shows the data without peaks or unreal scales.</p>

<p>From "DB Maintenance"&gt;"Database Debug" in the Administration menu any of the out of range data received from a agent's module can be deleted.</p>

<p class="center"><img src="images/image058.png"></p>

<p>The purge settings are: agent, module, minimum and maximum data range. Any parameter out of this minimum/maximum range will be deleted.</p>

<p>For example, in a module registering the number of processes, if we are only interested in values between 0 and 100, any values above that number will be usually produced by errors, noise or abnormal
circumstances. If we set to range between 0 and 100 all those values below and above - such as -1, 100 or 100000 - will be permanently deleted from the database.</p>

<h3><a name="832">8.3.2. Purging all the agent's data</a></h3>

<p>All the out of range data received by an agent can be deleted from the "DB Maintainance"&gt;"Database Purge" option in the Administration menu.</p>

<p>The data is deleted by the following parameters from the "Delete data" screen:</p>

<ul>
<li>Purge all data</li>
<li>Purge data over 90 days</li>
<li>Purge data over 30 days</li>
<li>Purge data over 14 days</li>
<li>Purge data over 7 days</li>
<li>Purge data over 3 days</li>
<li>Purge data over 1 day</li>
</ul>

<p class="center"><img src="images/image059.png"></p>

<h2><a name="84">8.4.Purging system data</a></h2>

<h3><a name="841">8.4.1. Audit data purge</a></h3>

<p>All the audit data generated by the system can be deleted from "DB Maintenance"&gt;"Database Audit", in the Administration menu</p>

<p>The data to be deleted is selected by setting the following parameters in the "Delete Data" screen</p>

<ul>
<li>Purge audit data over 90 days</li>
<li>Purge audit data over 30 days</li>
<li>Purge audit data over 14 days</li>
<li>Purge audit data over 7 days</li>
<li>Purge audit data over 3 days</li>
<li>Purge audit data over 1 day</li>
<li>Purge all audit data</li>
</ul>

<p class="center"><img src="images/image060.png"></p>

<h3><a name="842">8.4.2. Event data purge</a></h3>

<p>All the event data generated by the system can be deleted from "DB Maintenance"&gt;"Database Event", in the Administration menu</p>

<p>The data to be deleted is selected by setting the following parameters in the "Delete Data" screen</p>

<ul>
<li>Purge event data over 90 days</li>
<li>Purge event data over 30 days</li>
<li>Purge event data over 14 days</li>
<li>Purge event data over 7 days</li>
<li>Purge event data over 3 days</li>
<li>Purge event data over 1 day</li>
<li>Purge all event data</li>
</ul>

<p class="center"><img src="images/image061.png"></p>

</body>
</html>