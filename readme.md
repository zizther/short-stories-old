# Short stories
---

# Frontend
To compile and optimse the assets in the site you will need to have Gulp and other packages installed. To do this you will need Node with NPM installed and Gulp

## Install Node
[Download node JS](http://nodejs.org/download/)
This will also install NPM


## Install Gulp
Once you have Node and NPM installed, run this command

	npm install -g gulp

Anywhere you see -g this means to install the package globally


## Install the project node modules
Go to the project root and run

	npm install

## Update project node modules
Run this command at the project root

	npm update

## Using Gulp
There are 3 tasks available:

	npm run dev

This will perform the watch function to compile the CSS and run other functions

	npm run build

This will compile and optimise the CSS, optimise images and handle any JS stuff.
This should not be run manually. It is designed to only be used when merging with specific branches

## gulp notifications
Automatic desktop notifications for Gulp errors and warnings using Growl for OS X or Windows, Mountain Lion and Mavericks Notification Center, and Notify-Send.

#### Mac

#####  OS X Notification System

*Support Included.*

If you are using OS X 10.8 Mountain Lion or newer a notification system is built in, but Apple does not provide a
notification API that Node can access. Only code written in Objective C and signed in XCode can access it.
This is not very friendly for Node users so we are using the tiny signed MIT-licensed native application
[Terminal Notifier](https://github.com/alloy/terminal-notifier) from [Eloy Durán](https://github.com/alloy).

##### Growl for OS X

*Requires growlnotify for OS X.*

Install **growlnotify** from the [Growl Downloads Page](http://growl.info/downloads). This will install in `/usr/local/bin/growlnotify`.

#### Windows

##### Snarl

*Included with Snarl.*

If you have downloaded and installed Snarl from [Snarl's web site](http://snarl.fullphat.net/) you'll have the commandline tool heysnarl as well.

##### Growl for Windows

*Requires growlnotify for Windows.*

Install **growlnotify** from the [growlnotify Page](http://www.growlforwindows.com/gfw/help/growlnotify.aspx).

##### Windows 8.1 Notifications

*Nothing to install.*

Create a pull request!

#### Linux

##### Notify-Send

*Nothing to install.*

I created an Ubuntu virtual machine and it had `notify-send` in the path.

I don't use Linux frequently so I don't know if this utility is available for other distros.

[notify-send man page](http://manpages.ubuntu.com/manpages/gutsy/man1/notify-send.1.html).

`notify-send` has an addition `duration` option which takes a number seconds. The default is 3 seconds.

Duration doesn't work natively on some versions of Ubuntu.

Here is a fix: http://askubuntu.com/questions/128474/how-to-customize-on-screen-notifications

#### Chrome

*Not supported yet.*

Chrome has a notification system but I'm not sure if it's possible to use from a command-line Node app. Somebody could
probably create a Chrome Plugin helper for this.
